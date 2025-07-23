<?php

namespace Breakdance\Forms\Actions;

use function Breakdance\Elements\control;
use function Breakdance\Elements\controlSection;
use function Breakdance\Elements\inlineRepeaterControl;
use function Breakdance\Elements\repeaterControl;

class ActiveCampaign extends ApiAction
{

    /** @var ?string */
    protected $baseUrl = null;

    /** @var ?string */
    protected $apiKey = null;

    /**
     * @param ?string $apiKey
     * @param ?string $apiUrl
     */
    public function __construct($apiKey = null, $apiUrl = null)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = $apiUrl;
    }

    /**
     * @return string
     */
    public static function name()
    {
        return 'ActiveCampaign';
    }

    /**
     * @return string
     */
    public static function slug()
    {
        return 'activecampaign';
    }

    /**
     * @return void
     */
    public static function registerAjaxHandlers()
    {

        \Breakdance\AJAX\register_handler(
            'breakdance_fetch_activecampaign_lists',
            /**
             * @return array
             */
            function () {
                /** @var FormRequestContext $requestdata */
                $requestdata = $_POST['requestData'] ?? [];
                return self::getLists($requestdata);
            },
            'edit'
        );

        \Breakdance\AJAX\register_handler(
            'breakdance_fetch_activecampaign_accounts',
            /**
             * @return array
             */
            function () {
                /** @var FormRequestContext $requestdata */
                $requestdata = $_POST['requestData'] ?? [];
                return self::getAccounts($requestdata);
            },
            'edit'
        );

        \Breakdance\AJAX\register_handler(
            'breakdance_fetch_activecampaign_tags',
            /**
             * @return array
             */
            function () {
                /** @var FormRequestContext $requestdata */
                $requestdata = $_POST['requestData'] ?? [];
                return self::getTags($requestdata);
            },
            'edit'
        );

        \Breakdance\AJAX\register_handler(
            'breakdance_fetch_activecampaign_fields',
            /**
             * @return array
             */
            function () {
                /** @var FormRequestContext $requestdata */
                $requestdata = $_POST['requestData'] ?? [];
                return self::getCustomFields($requestdata);
            },
            'edit'
        );
    }

    /**
     * @return array
     */
    public function controls()
    {
        return [
            control(
                'api_key_input',
                'Use ActiveCampaign API Key',
                [
                    'type' => 'api_key_input',
                    'layout' => 'vertical',
                    'apiKeyOptions' => [
                        'apiKeyName' => BREAKDANCE_ACTIVECAMPAIGN_API_KEY_NAME,
                        'urlKeyName' => BREAKDANCE_ACTIVECAMPAIGN_URL_NAME
                    ]
                ]),

            control('account', 'Account', [
                'type' => 'dropdown',
                'layout' => 'vertical',
                'placeholder' => 'No account selected',
                'dropdownOptions' => [
                    'populate' => [
                        'fetchDataAction' => 'breakdance_fetch_activecampaign_accounts',
                        'fetchContextPath' => 'content.actions.activecampaign',
                        'refetchPaths' => ['content.actions.activecampaign.api_key_input'],
                    ],
                ],
            ]),

            control('list', 'List', [
                'type' => 'dropdown',
                'layout' => 'vertical',
                'placeholder' => 'No list selected',
                'dropdownOptions' => [
                    'populate' => [
                        'fetchDataAction' => 'breakdance_fetch_activecampaign_lists',
                        'fetchContextPath' => 'content.actions.activecampaign',
                        'refetchPaths' => ['content.actions.activecampaign.api_key_input'],
                    ],
                ],
            ]),

            control('tags', 'Tags', [
                'type' => 'multiselect',
                'layout' => 'vertical',
                'placeholder' => 'No tags selected',
                'multiselectOptions' => [
                    'populate' => [
                        'fetchDataAction' => 'breakdance_fetch_activecampaign_tags',
                        'fetchContextPath' => 'content.actions.activecampaign',
                        'refetchPaths' => ['content.actions.activecampaign.api_key_input'],
                    ],
                ],
            ]),

            controlSection('field_mapping', 'Field Mapping', [
                repeaterControl('fields', 'Custom Fields', [
                    control('activecampaign_field', 'Field', [
                        'type' => 'dropdown',
                        'layout' => 'vertical',
                        'placeholder' => 'No field selected',
                        'dropdownOptions' => [
                            'populate' => [
                                'fetchDataAction' => 'breakdance_fetch_activecampaign_fields',
                                'fetchContextPath' => 'content.actions.activecampaign',
                                'refetchPaths' => ['content.actions.activecampaign.form', 'content.actions.activecampaign.api_key_input'],
                            ],
                        ]
                    ]),

                    control('formField', 'Form Field', [
                        'type' => 'dropdown',
                        'layout' => 'vertical',
                        'placeholder' => '',
                        'dropdownOptions' => [
                            'populate' => [
                                'path' => 'content.form.fields',
                                'text' => 'label',
                                'value' => 'advanced.id',
                            ]
                        ],
                    ]),
                ])
            ]),
        ];
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl ? $this->baseUrl . '/api/3' : '';
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return [
            'Api-Token' => $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * @param FormData $form
     * @param FormSettings $settings
     * @param FormExtra $extra
     * @return ActionSuccess|ActionError|array<array-key, ActionSuccess|ActionError>
     */
    public function run($form, $settings, $extra)
    {
        $this->apiKey = self::getApiKeyFromApiKeyInput($settings['actions']['activecampaign']['api_key_input'], BREAKDANCE_ACTIVECAMPAIGN_API_KEY_NAME);
        $this->baseUrl = self::getApiKeyFromApiKeyInput($settings['actions']['activecampaign']['api_key_input'], BREAKDANCE_ACTIVECAMPAIGN_URL_NAME, 'apiUrl');

        $isApiKeySetAndValid = self::isApiKeySet(['apiKey' => $this->apiKey, 'apiUrl' => $this->baseUrl]);
        $account = $settings['actions']['activecampaign']['account'] ?? null;
        $list = $settings['actions']['activecampaign']['list'] ?? null;
        $tags = $settings['actions']['activecampaign']['tags'] ?? [];

        if ($isApiKeySetAndValid !== true) {
            return $isApiKeySetAndValid;
        }

        $mergeFields = \Breakdance\Forms\getMappedFieldValuesFromFormData(
            $settings['actions']['activecampaign']['field_mapping']['fields'],
            $form,
            'activecampaign_field'
        );

        $contactInfo = [];

        // Add all default fields to the subscriber info and unset them from mergeFields
        foreach ($this->fields as $field){
            if (isset($mergeFields[$field['value']])){
                /** @var string */
                $contactInfo[$field['value']] = $mergeFields[$field['value']];

                unset($mergeFields[$field['value']]);
            }
        }

        $fieldValues = array_map(
            fn($id, $value) => ['field' => $id, 'value' => $value],
            array_keys($mergeFields),
            $mergeFields
        );

        $contactInfo = array_merge(
            $contactInfo,
            [
                'fieldValues' => $fieldValues,
            ]
        );


        if (!isset($contactInfo['email'])) {
            $this->addContext('Contact Information', $contactInfo);
            return [
                'type' => 'error',
                'message' => 'Email address is required.'
            ];
        }

        $response = $this->request("/contact/sync", 'POST', json_encode(['contact' => $contactInfo]));

        if (array_key_exists('error', $response)) {
            /** @var string $error */
            $error = $response['error'];

            return [
                'type' => 'error',
                'message' => $error,
                'response' => $response,
            ];
        }

        /** @var array{contact: array{id: string, email: string, organization: string|null}} $response */
        $contact = $response['contact'];

        if ($account) {
            $accountResponse = $this->request("/accountContacts", 'POST', json_encode(
                [
                    'accountContact' => [
                        'account' => $account,
                        'contact' => $contact['id']
                    ]
                ]
            ));
        }

        if ($list) {
            $listResponse = $this->request("/contactLists", 'POST', json_encode(
                [
                    'contactList' => [
                        'list' => $list,
                        'contact' => $contact['id'],
                        'status' => 1
                    ]
                ]
            ));

            if (array_key_exists('error', $listResponse)) {
                /** @var string $error */
                $error = $listResponse['error'];

                return [
                    'type' => 'error',
                    'message' => "{$error} (List ID: {$list})",
                    'response' => $listResponse
                ];
            }
        }

        if ($tags) {
            foreach ($tags as $tag) {
                $tagsResponse = $this->request("/contactTags", 'POST', json_encode(
                    [
                        'contactTag' => [
                            'contact' => $contact['id'],
                            'tag' => $tag
                        ]
                    ]
                ));

                if (array_key_exists('error', $tagsResponse)) {
                    /** @var string $error */
                    $error = $tagsResponse['error'];

                    return [
                        'type' => 'error',
                        'message' => $error,
                        'response' => $tagsResponse,
                    ];
                }
            }
        }

        return [
            'type' => 'success',
            'response' => $response
        ];
    }

    /**
     * Show a meaningful message if the response contains an error
     * @param array $response
     * @return string
     * @psalm-suppress MixedReturnStatement
     */
    protected function handleErrorMessage($response) {
        if (array_key_exists('errors', $response) && is_array($response['errors'])) {
            /** @var array{title: string} $error */
            $error = reset($response['errors']);
            return $error['title'] ?? 'Error accessing resource';
        }

        return parent::handleErrorMessage($response);
    }

    /**
     * @param array{apiKey: string|null, apiUrl: string|null} $apiKeyAndUrl
     * @return ActionSuccess|ActionError
     */
    public static function validateApiKey($apiKeyAndUrl)
    {
        $isApiKeySetAndValid = self::isApiKeySet($apiKeyAndUrl);

        if ($isApiKeySetAndValid !== true) {
            return $isApiKeySetAndValid;
        }

        return self::getSuccessOrErrorFromApiKeyValidationResponse(
            self::fetchLists($apiKeyAndUrl['apiKey'] ?? null, $apiKeyAndUrl['apiUrl'] ?? null)
        );
    }

    /**
     * @psalm-suppress ImplementedParamTypeMismatch
     * @param array{apiKey: string|null, apiUrl: string|null}  $apiKeyAndUrl
     * @return true|ActionError
     */
    public static function isApiKeySet($apiKeyAndUrl)
    {
        if (!isset($apiKeyAndUrl['apiKey']) || !$apiKeyAndUrl['apiKey'] || empty($apiKeyAndUrl['apiKey'])) {
            return [
                'type' => 'error',
                'message' => 'API key is not set.'
            ];
        }

        if (!isset($apiKeyAndUrl['apiUrl']) || !$apiKeyAndUrl['apiUrl'] || empty($apiKeyAndUrl['apiUrl'])) {
            return [
                'type' => 'error',
                'message' => 'API URL is not set.'
            ];
        }

        $isApiUrlValid = filter_var($apiKeyAndUrl['apiUrl'], FILTER_VALIDATE_URL);

        if (!$isApiUrlValid){
            return [
                'type' => 'error',
                'message' => 'Invalid API URL. You can find the correct one in your ActiveCampaign settings.'
            ];
        }

        return true;
    }

    /**
     * @param ?string $apiKey
     * @param ?string $apiUrl
     * @return array{lists: array{name: string, id: string}[]} $response
     */
    public static function fetchLists($apiKey, $apiUrl)
    {
        /** @var array{lists: array{name: string, id: string}[]} $lists */
        $lists = (new self($apiKey, $apiUrl))->request( '/lists?limit=100');

        return $lists;
    }

    /**
     * @param ?string $apiKey
     * @param ?string $apiUrl
     * @return array{accounts: array{name: string, id: string}[]} $response
     */
    public static function fetchAccounts($apiKey, $apiUrl)
    {
        /** @var array{accounts: array{name: string, id: string}[]} $accounts */
        $accounts = (new self($apiKey, $apiUrl))->request( '/accounts');

        return $accounts;
    }

    /**
     * @param ?string $apiKey
     * @param ?string $apiUrl
     * @return array{tags: array{tag: string, id: string}[]} $response
     */
    public static function fetchTags($apiKey, $apiUrl)
    {
        /** @var array{tags: array{tag: string, id: string}[]} $tags */
        $tags = (new self($apiKey, $apiUrl))->request( '/tags');

        return $tags;
    }

    /**
     * @param FormRequestContext $requestData
     * @return DropdownData[]
     */
    public static function getLists($requestData)
    {
        $apiKey = self::getApiKeyFromApiKeyInput($requestData['context']['api_key_input'] ?? null, BREAKDANCE_ACTIVECAMPAIGN_API_KEY_NAME);
        $apiUrl = self::getApiKeyFromApiKeyInput($requestData['context']['api_key_input'] ?? null, BREAKDANCE_ACTIVECAMPAIGN_URL_NAME, 'apiUrl');

        if (!$apiKey || !$apiUrl) {
            return [];
        }

        $response = self::fetchLists($apiKey, $apiUrl);

        if (array_key_exists('error', $response)) {
            return [];
        }

        return array_map(
            fn($account) => [
                'text' => $account['name'],
                'value' => $account['id']
            ],
            $response['lists'] ?? []
        );
    }

    /**
     * @param FormRequestContext $requestData
     * @return DropdownData[]
     */
    public static function getAccounts($requestData)
    {
        $apiKey = self::getApiKeyFromApiKeyInput($requestData['context']['api_key_input'] ?? null, BREAKDANCE_ACTIVECAMPAIGN_API_KEY_NAME);
        $apiUrl = self::getApiKeyFromApiKeyInput($requestData['context']['api_key_input'] ?? null, BREAKDANCE_ACTIVECAMPAIGN_URL_NAME, 'apiUrl');

        if (!$apiKey || !$apiUrl) {
            return [];
        }

        $response = self::fetchAccounts($apiKey, $apiUrl);

        if (array_key_exists('error', $response)) {
            return [];
        }

        return array_map(
            fn($account) => [
                'text' => $account['name'],
                'value' => $account['id']
            ],
            $response['accounts'] ?? []
        );
    }

    /**
     * @param FormRequestContext $requestData
     * @return DropdownData[]
     */
    public static function getTags($requestData)
    {
        $apiKey = self::getApiKeyFromApiKeyInput($requestData['context']['api_key_input'] ?? null, BREAKDANCE_ACTIVECAMPAIGN_API_KEY_NAME);
        $apiUrl = self::getApiKeyFromApiKeyInput($requestData['context']['api_key_input'] ?? null, BREAKDANCE_ACTIVECAMPAIGN_URL_NAME, 'apiUrl');

        if (!$apiKey || !$apiUrl) {
            return [];
        }

        $response = self::fetchTags($apiKey, $apiUrl);

        if (array_key_exists('error', $response)) {
            return [];
        }

        return array_map(
            fn($tag) => [
                'text' => $tag['tag'],
                'value' => $tag['id']
            ],
            $response['tags'] ?? []
        );
    }

    /** @var array{text: string, value: string}[] */
    public $fields = [
        [
            'text' => 'Email',
            'value' => 'email'
        ],
        [
            'text' => 'First Name',
            'value' => 'firstName'
        ],
        [
            'text' => 'Last Name',
            'value' => 'lastName'
        ],
        [
            'text' => 'Phone',
            'value' => 'phone'
        ],
    ];


    /**
     * @param FormRequestContext $requestData
     * @return DropdownData[]
     */
    public static function getCustomFields($requestData)
    {
        $apiKey = self::getApiKeyFromApiKeyInput($requestData['context']['api_key_input'] ?? null, BREAKDANCE_ACTIVECAMPAIGN_API_KEY_NAME);
        $apiUrl = self::getApiKeyFromApiKeyInput($requestData['context']['api_key_input'] ?? null, BREAKDANCE_ACTIVECAMPAIGN_URL_NAME, 'apiUrl');

        if (!$apiKey || !$apiUrl) {
            return [];
        }

        $self = new self($apiKey, $apiUrl);
        /**
         * @var array{fields: array{title: string, id: string}[]} $response
         */
        $response = $self->request('/fields?limit=999');

        if (array_key_exists('error', $response)) {
            return [];
        }

        return array_merge(
            $self->fields,
            array_map(
                static function ($field) {
                    return [
                        'text' => $field['title'],
                        'value' => $field['id']
                    ];
                },
                $response['fields']
            )
        );
    }
}
