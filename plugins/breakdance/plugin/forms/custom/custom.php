<?php

namespace Breakdance\Forms;

use Breakdance\Forms\Actions\Action;
use Breakdance\Forms\Actions\ActionProvider;
use function Breakdance\AJAX\get_nonce_key_for_ajax_requests;
use function Breakdance\Forms\Render\getFieldAttributes;
use const Breakdance\Filesystem\PHP_FILE_UPLOAD_ERROR_MESSAGES;

/**
 * @param int $postId
 * @param int $formId
 * @param FormUserSubmittedContents $fields
 * @return FormSuccess|FormError|FormError[]
 */
function handleSubmission($postId, $formId, $fields)
{
    $settings = getFormSettings($postId, $formId);

    if (!$settings) {
        return [
            'type' => 'error',
            'message' => __('An unexpected error occurred.', 'breakdance')
        ];
    }

    $actions = getFormActions($settings);
    $form    = getFormData($settings['form']['fields'], $fields);
    $successMessage = $settings['form']['success_message'];
    $errorMessage = $settings['form']['error_message'];

    // Metadata
    $ip        = (string) ($_SERVER['REMOTE_ADDR'] ?? null);
    $referer   = wp_get_referer();
    $userAgent = (string) ($_SERVER['HTTP_USER_AGENT'] ?? null);
    $userId = get_current_user_id();

    // User access
    $breakdancePermissions = \Breakdance\Permissions\getUserPermission();
    $hasFullAccess = $breakdancePermissions && $breakdancePermissions['slug'] === 'full';

    // Validate honeypot field
    $csrfEnabled = $settings['advanced']['csrf_enabled'] ?? false;

    if ($csrfEnabled) {
        if (!array_key_exists('csrfToken', $fields) || !is_string($fields['csrfToken']) || !wp_verify_nonce($fields['csrfToken'], get_nonce_key_for_ajax_requests())) {
            return [
                'type' => 'error',
                'message' => $hasFullAccess ? __('Invalid CSRF token', 'breakdance') : $errorMessage
            ];
        }
        unset($fields['csrfToken']);
    }

    // reCAPTCHA v3 challenge
    $recaptchaEnabled = $settings['advanced']['recaptcha']['enabled'] ?? false;
    if ($recaptchaEnabled) {
        $token   = (string) ($_POST['recaptcha_token'] ?? null);
        if (!$token) {
            return [
                'type' => 'error',
                'message' => $hasFullAccess ? __('[reCAPTCHA] Error retrieving token.', 'breakdance') : $errorMessage
            ];
        }
        $verified = \Breakdance\Forms\Recaptcha\verify($token, $ip, 'breakdance_submit', $settings['advanced']['recaptcha']['api_key_input']['apiKey']);

        if (!$verified) {
            return [
                'type' => 'error',
                'message' => $hasFullAccess ? __('[reCAPTCHA] Invalid challenge. Please reload and try again.', 'breakdance') : $errorMessage
            ];
        }
    }

    // Validate honeypot field
    $honeypotEnabled = $settings['advanced']['honeypot_enabled'] ?? false;
    if ($honeypotEnabled) {
        if (array_key_exists('hpname', $fields) && !empty($fields['hpname'])) {
            // failed the honeypot test. Ignore this
            // submission but return a success response
            return [
                'type' => 'success',
                'message' => $successMessage,
            ];
        }
    }

    $uploads = [];
    if (array_key_exists('fields', $_FILES)) {
        /** @psalm-suppress MixedArgument */
        $uploads = getFilesNormalized($_FILES['fields']);
    }

    // Validate form. Validators available: Required, email, and files.
    $validation = validateFormData($form, $uploads);

    if (is_wp_error($validation)) {
        /** @psalm-suppress PossiblyInvalidMethodCall */
        return [
            'type' => 'error',
            'message' => $validation->get_error_message()
        ];
    }

    // Upload files if any is present. Runs after all validations
    // We don't want to allow spam files in our server.
    $files = handleUploadedFiles($formId, $uploads, $settings);
    $storeUploadedFiles = $settings['actions']['store_submission']['store_files'] ?? false;

    if (!empty($files) && $storeUploadedFiles) {
        // If we have files, assign the URLs to
        // the value for both $form and $extra
        foreach ($form as &$field) {
            $fieldId = getIdFromField($field);
            if ($field['type'] === 'file' && array_key_exists($fieldId, $files)) {
                $commaSeparatedFileUrls = implode(', ', array_map(static function($file) use ($postId, $formId, $fieldId) {
                    return getSecureFileUrl($postId, $formId, $fieldId, $file['url'], false);
                }, $files[$fieldId]));
                $fields[$fieldId] =  $commaSeparatedFileUrls;
                $field['value'] =  $commaSeparatedFileUrls;
            }
        }
        unset($field);
    }

    // Run all actions set for this form
    /** @var FormExtra $extra */
    $extra = [
        'formId'  => $formId,
        'postId'  => $postId,
        'fields'   => $fields,
        'uploads'    => $uploads,
        'files'    => $files,
        'ip'      => $ip,
        'referer' => $referer,
        'userAgent' => $userAgent,
        'userId' => $userId,
    ];

    $response = executeActions($actions, [$form, $settings, $extra]);
    /** @var int|null $submissionId */
    $submissionId = $response['store_submission']['id'] ?? null;

    if ($submissionId) {
        unset($response['store_submission']); // We don't care for the 'store submission' action.
        \Breakdance\Forms\Submission\saveActionsLog($submissionId, $response);

        // We delete the files outside the `store_submission` action,
        // because we want to keep the files for all actions to use.
        if (!$storeUploadedFiles) {
            cleanUpFiles($files);
        }
    } else {
        // If there is no stored submission, delete any uploaded files
        cleanUpFiles($files);
    }

    return handleSubmissionResponse($response, $successMessage, $errorMessage, $hasFullAccess);
}

/**
 * @param array<string, ActionSuccess|ActionError> $response
 * @param string|null $successMessage
 * @param string|null $errorMessage
 * @param bool $hasFullAccess
 * @return FormSuccess|FormError|FormError[]
 */
function handleSubmissionResponse($response, $successMessage, $errorMessage, $hasFullAccess)
{
    // Remove context data from the request response
    // we don't want to return this data to the end-user
    foreach ($response as &$responseItem) {
        unset($responseItem['context']);
    }
    unset($responseItem);

    // Show errors if any is present and user is admin
    $actionsErrors = getActionsErrors($response);
    $userErrors = array_filter($actionsErrors, fn($action) => $action['type'] === 'user-error');

    if (!empty($actionsErrors)) {
        if (count($userErrors) > 0) {
            return $userErrors;
        }

        if ($hasFullAccess) {
            // Show action errors if user is admin
            return $actionsErrors;
        }

        return [
            'type' => 'error',
            'message' => [$errorMessage]
        ];
    }

    // Otherwise return a success message.
    return [
        'type' => 'success',
        'message' => $successMessage,
    ];
}

/**
 * @param Action $action
 * @param FormData $form
 * @param FormSettings $settings
 * @param FormExtra $extra
 * @return boolean|\WP_Error
 */
function shouldActionRun($action, $form, $settings, $extra)
{
    /** @var ActionData $actionData */
    $actionData = $settings['actions'][$action::slug()] ?? false;
    $runConditionally = $actionData['run_action_conditionally'] ?? false;
    $conditions = $actionData['conditions'] ?? [];
    $allFields = array_combine(
        array_map(fn($field) => getIdFromField($field), $form),
        array_column($form, 'value')
    );

    $canExecute = true;

    if ($runConditionally && !empty($conditions)) {
        foreach ($conditions as $row) {
            $condition = $row['condition'] ?? [];

            if (isset($condition['field'], $condition['operand'])) {
                $fieldId = $condition['field'];
                $operand = $condition['operand'];
                $fieldValue = $allFields[$fieldId] ?? null;
                $conditionValue = $condition['value'] ?? null;

                $isFieldValid = compareFieldValue($fieldValue, $operand, $conditionValue);

                if (!$isFieldValid) {
                    $canExecute = false;
                    break;
                }
            }
        }
    }

    /**
     * @psalm-suppress TooManyArguments
     * @var bool|\WP_Error $canExecute
     */
    $canExecute = apply_filters('breakdance_form_run_action_' . $action::slug(), $canExecute, $action, $extra, $form, $settings);
    return $canExecute;
}

/**
 * Run a give list of actions
 * @param Action[] $actions
 * @param array $args
 * @return array<string, ActionSuccess|ActionError>
 */
function executeActions($actions, $args)
{
    [$form, $settings, $extra] = $args;

    $responses = [];
    foreach ($actions as $action) {
        $output = [];
        /** @psalm-suppress MixedArgument */
        $canExecute = shouldActionRun($action, $form, $settings, $extra);

        if (!$canExecute) {
            $output['type'] = 'success';
            $output['message'] = '';

            /** @var ActionConditions $conditions */
            $conditions = $settings['actions'][$action::slug()]['conditions'] ?? [];
            $output['conditions'] = $conditions;
        } else if (is_wp_error($canExecute)) {
            /** @var \WP_Error $canExecute */
            $canExecute = $canExecute;

            $output['type'] = 'user-error';
            $output['message'] = $canExecute->get_error_message();
        } else {
            /** @var ActionSuccess|ActionError $output */
            $output = call_user_func_array([$action, 'run'], $args);
        }

        if ($canExecute) {
            $output['executed_at'] = current_time( \DateTimeInterface::ATOM );
        }

        $output['context'] = $action->getContext();

        /** @psalm-suppress MixedAssignment */
        $responses[$action::slug()] = $output;
    }

    return $responses;
}

/**
 * Get actions errors
 * @param array<array-key, ActionSuccess|ActionError> $responses
 * @return ActionError[]
 */
function getActionsErrors($responses)
{
    /** @var ActionError[] $errors */
    $errors = array_values(array_filter(array_map(static function($response, $slug) {
        if ($response['type'] !== 'error' && $response['type'] !== 'user-error') {
            return false;
        }

        $action = ActionProvider::getInstance()->getActionBySlug((string) $slug);
        $actionName     = $action ? $action->name() : $slug;
        $response['message'] = "[$actionName]: " . $response['message'];
        return $response;
    }, $responses, array_keys($responses))));

    return $errors;
}

/**
 * @param int $postId
 * @param int $formId
 * @return FormSettings|null
 */
function getFormSettings($postId, $formId)
{
    $node = \Breakdance\Render\getNodeById($postId, $formId);

    if (!$node) return null;

    /** @var array{content: FormSettings, design: FormDesign, settings: array} $props */
    $props = $node['data']['properties'];

    /** @var FormSettings|null */
    return $props['content'];
}

/**
 * Get available actions for the given settings
 * @param FormSettings $settings
 * @return Action[]
 */
function getFormActions($settings)
{
    $enabledActions = $settings['actions']['actions'] ?? false;

    if (!$enabledActions) return [];

    $allActions = ActionProvider::getInstance()->getActions();

    return array_values(
        array_filter(
            $allActions,
            function (Action $action) use ($enabledActions) {
                return in_array($action::slug(), $enabledActions);
            }
        )
    );
}

/**
 * @param mixed $value
 * @param array $sanitizers
 * @return mixed|null
 */
function _sanitize($value, $sanitizers)
{
    if (is_array($value)) {
        return array_map(
            /**
             * @param mixed $itemVal
             * @return mixed
             */
            function ($itemVal) use ($sanitizers) {
                return _sanitize($itemVal, $sanitizers);
            },
            $value
        );
    }

    return array_reduce(
        $sanitizers,
        /**
         * @param mixed $val
         * @param \Closure $sanitizer
         * @return mixed
         */
        function ($val, $sanitizer) {
            return $sanitizer($val);
        },
        $value
    );
}

/**
 * @param mixed $value
 * @param string $type
 * @return mixed
 */
function sanitizeValue($value, $type)
{
    $sanitizers = [
        'email'    => ['sanitize_text_field', 'sanitize_email'],
        'text'     => ['sanitize_text_field'],
        'textarea' => ['sanitize_textarea_field']
    ];

    $default = ['sanitize_text_field'];

    return _sanitize($value, $sanitizers[$type] ?? $default);
}

/**
 * @param array $value
 * @return string
 */
function getCommaDelimitedValue($value)
{
    return implode(', ', $value);
}

/**
 * Merge labels and user submitted data together.
 * @param StoredFormField[] | null $storedFields
 * @param FormUserSubmittedContents $values
 * @return FormData
 */
function getFormData($storedFields, $values)
{
    if (!is_array($storedFields)) {
        return [];
    }

    return array_map(function ($storedField) use ($values) {
        $field = getFieldAttributes($storedField);
        /** @var mixed $original */
        $fieldId = \Breakdance\Forms\getIdFromField($field);
        $original = array_key_exists($fieldId, $values) ? $values[$fieldId] : '';
        /** @var string $value */
        $value = is_array($original) ? getCommaDelimitedValue($original) : $original;

        return array_merge($field, [
            'value' => $value,
            'originalValue' => $original
        ]);
    }, $storedFields);
}

/**
 * @param FormData $form
 * @param NormalizedUploadedFiles $files
 * @return \WP_Error|true
 */
function validateFormData($form, $files)
{
    $bag = new \WP_Error();
    $fileFieldIds = [];

    foreach ($form as $field) {
        $required = $field['advanced']['required'] ?? false;
        $label = $field['label'] ?? $field['advanced']['id'];

        if ($required && array_key_exists('conditional', $field['advanced']) && $field['advanced']['conditional'] === true) {
            $required = isConditionalFieldRequired($field, $form);
        }

        // Required fields
        if ($required && $field['type'] !== 'file' && empty($field['value'])) {
            $bag->add('required', sprintf(__('%s is required.', 'breakdance'), $label));
            continue; // Bail out from other validations if the value is undefined
        }

        // Email Addresses
        if ($required && $field['type'] === 'email' && !is_email($field['value'])) {
            $bag->add('invalid_email', __('Please enter a valid email address.', 'breakdance'));
        }

        if ($field['type'] === 'file') {
            $fieldId = \Breakdance\Forms\getIdFromField($field);
            // File Upload
            $fileFieldIds[] = $fieldId;
            /** @var _FILE[] $fieldFiles */
            $fieldFiles = $files[$fieldId] ?? [];

            if ($required && !$fieldFiles) {
                $bag->add('required_file', sprintf(__('%s is required.', 'breakdance'), $label));
            }

            if ($fieldFiles) {
                $maxNumOfFiles = $field['max_number_of_files'] ?? 1;
                $maxFileSizeInMB = $field['max_file_size'] ?? 10;
                $maxFileSizeInBytes = $maxFileSizeInMB * pow(1024, 2);
                $allowedFileTypes = $field['allowed_file_types'] ?? [];

                if (count($fieldFiles) > $maxNumOfFiles) {
                    $bag->add('file_max', sprintf(__('%s may not have more than %s file(s).', 'breakdance'), $label, $maxNumOfFiles));
                }

                foreach ($fieldFiles as $file) {
                    if ($file['error'] === UPLOAD_ERR_NO_FILE && !$required) {
                        continue;
                    }
                    if ($file['error'] !== UPLOAD_ERR_OK) {
                        $bag->add('file_error', PHP_FILE_UPLOAD_ERROR_MESSAGES[$file['error']]);
                    }

                    $size = filesize($file['tmp_name']);
                    $allowedMimeTypes = array_filter(get_allowed_mime_types(), static function($mimeType) use ($allowedFileTypes) {
                        return in_array($mimeType, $allowedFileTypes);
                    });
                    /** @var array{ext: string|false, type: string|false} $isTypeValid */
                    $isTypeValid = wp_check_filetype_and_ext($file['tmp_name'], $file['name'], $allowedMimeTypes);

                    if ($size > $maxFileSizeInBytes) {
                        $bag->add('file_size', sprintf(__('%s must be less than %s megabytes.', 'breakdance'), $label, $maxFileSizeInMB));
                    }

                    if ($isTypeValid['ext'] === false || $isTypeValid['type'] === false) {
                        $parseFileTypes = implode(', ', preg_replace('(.*/)', '', $allowedFileTypes));
                        $bag->add('file_type', sprintf(__('%s must be a file of type: %s', 'breakdance'), $label, $parseFileTypes));
                    }
                }
            }
        }
    }

    $unexpectedFiles = array_diff(array_keys($files), $fileFieldIds);
    if (!empty($unexpectedFiles)) {
         foreach ($unexpectedFiles as $fieldId) {
             $bag->add('unexpected_file', sprintf(__('Unexpected file with id: %s', 'breakdance'), $fieldId));
         }
    }

    if ($bag->has_errors()) {
        return $bag;
    }

    return true;
}

/**
 * @param StoredFormField $field
 * @param FormData $form
 * @return bool
 */
function isConditionalFieldRequired($field, $form)
{
    $condition = $field['advanced']['condition'] ?? [];
    if (isset($condition['field'], $condition['operand'])) {
        /** @var array<array-key, FormFieldWithValue> $allFieldsByID */
        $allFieldsByID = [];
        foreach ($form as $formField) {
            $allFieldsByID[$formField['advanced']['id']] = $formField;
        }
        /**
         * Have to define this here as psalm
         * doesn't support recursive type definitions
         * @var StoredFormField $conditionField
         * @since 2.0 - Conditional fields references are now stored as strings, but check if it's an array for backwards compatibility.
         */
        $conditionField = is_string($condition['field']) ? $allFieldsByID[$condition['field']] : $condition['field'];
        $conditionFieldId = getIdFromField($conditionField);

        if (!array_key_exists($conditionFieldId, $allFieldsByID)) {
            return false;
        }

        $fieldValue = $allFieldsByID[$conditionFieldId]['value'] ?? null;
        $conditionValue = $condition['value'] ?? null;

        return compareFieldValue($fieldValue, $condition['operand'], $conditionValue);
    }

    return false;
}

/**
 * The below is replicated in JS in forms/shared/js/shared.js
 * so if you change it here, you need to change it there too.
 *
 * @param string|null $aValue
 * @param string $operand
 * @param string|null $bValue
 * @return bool
 */
function compareFieldValue($aValue, $operand, $bValue) {
    if ($operand === "equals") {
        return $aValue == $bValue;
    }

    if ($operand === "not equals") {
        return $aValue != $bValue;
    }

    if ($operand === "is set") {
        return !empty($aValue);
    }

    if ($operand === "is not set") {
        return empty($aValue);
    }

    if ($operand === "is one of") {
        $bValueArray = explode((string) $bValue, ',');
        return count(array_filter($bValueArray, static function($bValue) use ($aValue) { return trim($bValue) == $aValue; })) > 0;
    }

    if ($operand === "is none of") {
        $bValueArray = explode((string) $bValue, ',');
        return count(array_filter($bValueArray, static function($bValue) use ($aValue) { return trim($bValue) == $aValue; })) === 0;
    }

    if ($operand === "contains") {
        if ($aValue && $bValue) {
            return stripos($aValue, (string)$bValue) !== false;
        }
    }

    if ($operand === "does not contain") {
        if ($aValue && $bValue) {
            return stripos($aValue, (string)$bValue) !== false;
        };
    }

    if ($operand === "is greater than") {
        if (is_numeric($aValue) && is_numeric($bValue)) {
            return (int) $aValue > (int) $bValue;
        }
    }

    if ($operand === "is less than") {
        if (is_numeric($aValue) && is_numeric($bValue)) {
            return (int) $aValue < (int) $bValue;
        }
    }

    if ($operand === "is before date") {
        if (!$aValue || !$bValue) {
            return false;
        }

        return strtotime($aValue) < strtotime($bValue);
    }

    if ($operand === "is after date") {
        if (!$aValue || !$bValue) {
            return false;
        }

        return strtotime($aValue) > strtotime($bValue);
    }

    if ($operand === "is before time") {
        if (!$aValue || !$bValue) {
            return false;
        }

        $todaysDate = date('Y-m-d');
        $aDateValue = strtotime("$todaysDate $aValue");
        $bDateValue = strtotime("$todaysDate $bValue");

        $aValueIsValidDate = $aDateValue !== false;
        $bValueIsValidDate = $bDateValue !== false;

        if (!$aValueIsValidDate || !$bValueIsValidDate) {
            return false;
        }

        return $aDateValue < $bDateValue;
    }

    if ($operand === "is after time") {
        if (!$aValue || !$bValue) {
            return false;
        }

        $todaysDate = date('Y-m-d');
        $aDateValue = strtotime("$todaysDate $aValue");
        $bDateValue = strtotime("$todaysDate $bValue");

        $aValueIsValidDate = $aDateValue !== false;
        $bValueIsValidDate = $bDateValue !== false;

        if (!$aValueIsValidDate || !$bValueIsValidDate) {
            return false;
        }

        return $aDateValue > $bDateValue;
    }

    return false;
}

add_filter('posts_join', 'Breakdance\Forms\submission_search_join');
/**
 * @param string $join
 * @return string
 */
function submission_search_join($join)
{
    global $pagenow, $wpdb;

    $postType = (string) ($_GET['post_type'] ?? '');

    if ($pagenow === 'edit.php'  && 'breakdance_form_res' === $postType && !empty($_GET['s']) && is_admin()) {
        /** @psalm-suppress MixedPropertyFetch */
        $join .= 'LEFT JOIN ' . (string) $wpdb->postmeta . ' AS bdmeta ON ' . (string) $wpdb->posts . '.ID = bdmeta.post_id ';
    }

    return $join;
}

add_filter('posts_where', 'Breakdance\Forms\submission_search_where');
/**
 * @param string $where
 * @return string
 */
function submission_search_where($where)
{
    global $pagenow, $wpdb;
    $postType = (string) ($_GET['post_type'] ?? '');

    if ($pagenow === 'edit.php'  && 'breakdance_form_res' === $postType && !empty($_GET['s']) && is_admin()) {
        /** @psalm-suppress MixedPropertyFetch */
        $where = preg_replace(
            "/\(\s*" . (string) $wpdb->posts . ".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
            "(" . (string) $wpdb->posts . ".post_title LIKE $1) OR (bdmeta.meta_value LIKE $1)", $where
        );
    }
    return $where;
}

add_filter('posts_distinct', 'Breakdance\Forms\submission_search_distinct');
/**
 * @param string $where
 * @return string
 */
function submission_search_distinct($where)
{
    global $pagenow;
    $postType = (string) ($_GET['post_type'] ?? '');

    if ($pagenow === 'edit.php'  && 'breakdance_form_res' === $postType && !empty($_GET['s']) && is_admin()) {
        return "DISTINCT";
    }
    return $where;
}
