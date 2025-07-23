<?php

namespace Breakdance\Icons;

use ZipArchive;
use function Breakdance\Data\delete_global_option;
use function Breakdance\Data\get_global_option;

define('BREAKDANCE_BUILTIN_ICON_SETS', [
    'FontAwesome 6 Free - Brands',
    'FontAwesome 6 Free - Regular',
    'FontAwesome 6 Free - Solid', 'IcoMoon Free'
]);

/**
 * @return \wpdb
 * @psalm-suppress MixedInferredReturnType
 */
function wpdb()
{
    global $wpdb;

    /**
     * @psalm-suppress MixedReturnStatement
     */
    return $wpdb;
}

function handle_icons_logic_on_plugin_install_aka_soft_reset()
{
    // Soft Reset: "Reset your ... Icon Sets to factory defaults."
    clear_icon_sets_table();
    clear_icons_table();
    install_stock_icons_to_db();
    set_full_text_for_icons_table();
    normalize_icon_names();
}

function normalize_icon_names()
{
    $wpdb = wpdb();
    $table_name = ((string) $wpdb->prefix) . 'breakdance_icons';

    $wpdb->query(
        <<<SQL
        UPDATE $table_name
        SET name = REPLACE(REPLACE(REPLACE(name, 'icon-', ''), '-', ' '), '.', '')
        WHERE name LIKE 'icon-%';
        SQL
    );
}

function set_full_text_for_icons_table()
{
    $wpdb = wpdb();
    $table_name = ((string) $wpdb->prefix) . 'breakdance_icons';

    $wpdb->query(
        <<<SQL
        ALTER TABLE $table_name
        ADD FULLTEXT(name);
        SQL
    );
}

/**
 * @return int
 */
function get_full_text_min_word_length()
{
    /**
     * @psalm-suppress UndefinedConstant
     * @psalm-suppress MixedArgument
     * @var array{Variable_name: string, Value: string} $row
     */

    $row = wpdb()->get_row("SHOW VARIABLES WHERE Variable_name = 'ft_min_word_len'", ARRAY_A);
    return isset($row['Value']) ? (int) $row['Value'] : 4;
}

/**
 * @param array $options
 * @psalm-param array{search_term: string|null, icon_set_slug: string|null, offset: int|null, suggestions: string[]|null} $options
 * @param int $limit
 * @param boolean $nomap
 * @return array
 */
function find_icons($options, $limit = 100, $nomap = false)
{
    $offset = isset($options['offset']) ? (int)$options['offset'] : 0;
    $min_search_length = get_full_text_min_word_length();

    $conditions = [];

    if (isset($options['icon_set_slug'])) {
        $icon_set_slug = trim($options['icon_set_slug']);
        if (strlen($icon_set_slug) > 0) {
            $conditions[] = wpdb()->prepare('`icon_set_slug` = %s', $icon_set_slug);
        }
    }

    if (isset($options['search_term'])) {
        $search_term = str_replace('-', ' ', trim($options['search_term']));
        $search_conditions = [];

        if (strlen($search_term) > $min_search_length) {
            $ft_search_term = explode(' ', $search_term);
            $ft_search_term = join(' ', array_map(fn($term) => "+$term*", $ft_search_term));

            $search_conditions[] = wpdb()->prepare(
                "MATCH(name) AGAINST(%s IN BOOLEAN MODE) OR name = %s OR name LIKE '%%%s%%'",
                $ft_search_term,
                $search_term,
                wpdb()->esc_like($search_term)
            );
        } else if (strlen($search_term) > 0) {
            $search_conditions[] = wpdb()->prepare("name LIKE %s", "%{$search_term}%");
        }

        if (!empty($search_conditions)) {
            $conditions[] = '(' . implode(' OR ', $search_conditions) . ')';
        }
    }

    /**
     * @psalm-suppress RedundantConditionGivenDocblockType
     */
    if (isset($options['suggestions']) && is_array($options['suggestions'])) {
        $suggestion_conditions = [];
        foreach ($options['suggestions'] as $suggestion_term) {
            $suggestion_term = trim($suggestion_term);
            if (strlen($suggestion_term) > $min_search_length) {
                $suggestion_conditions[] = wpdb()->prepare(
                    "MATCH(name) AGAINST(%s IN BOOLEAN MODE) OR name = %s OR name LIKE '%%%s%%'",
                    $suggestion_term,
                    $suggestion_term,
                    wpdb()->esc_like($suggestion_term)
                );
            } else if (strlen($suggestion_term) > 0) {
                $suggestion_conditions[] = wpdb()->prepare('`slug` LIKE %s', "%{$suggestion_term}%");
            }
        }

        if (!empty($suggestion_conditions)) {
            $conditions[] = '(' . implode(' OR ', $suggestion_conditions) . ')';
        }
    }

    $conditions_sql = empty($conditions) ? '' : sprintf(' WHERE %s ', join(' AND ', $conditions));

    $raw_sql = <<<SQL
    SELECT * FROM `%s` %s LIMIT %u OFFSET %u;
    SQL;

    $sql = sprintf($raw_sql, get_icons_table_full_name(), $conditions_sql, $limit, $offset);
    /**
     * @psalm-suppress UndefinedConstant
     * @psalm-suppress MixedArgument
     */
    $results = wpdb()->get_results($sql, ARRAY_A);

    if ($results === null) {
        $results = [];
    }

    // used when exporting the icons to stock-icons.csv
    if ($nomap) {
        /** @var array */
        $results = $results;
        return $results;
    }

    /**
     * @psalm-suppress PossiblyInvalidArgument
     */
    return array_map('\Breakdance\Icons\map_db_icon_to_webapp_icon', $results);
}

/**
 * @return array
 */
function get_icon_sets()
{

    /**
     * @psalm-suppress UndefinedConstant
     * @psalm-suppress MixedArgument
     * @psalm-suppress MixedArgument
     */
    $results = wpdb()->get_results(
        sprintf('SELECT * FROM `%s` ORDER BY `name` ASC;', get_icon_sets_table_full_name()),
        ARRAY_A
    );

    if (!is_array($results)) {
        $results = [];
    }

    /**
     * @psalm-suppress MixedArrayAccess
     */
    return array_map(fn($set) => [
        'slug' => $set['slug'],
        'name' => $set['name'],
        'custom' => !in_array($set['slug'], BREAKDANCE_BUILTIN_ICON_SETS)
    ], $results);
}

/**
 * @param string $icon_set_slug
 * @return list<array{id: string, name: string, slug: string, icon_set_slug: string, svg_code: string}>
 */
function get_icons_in_set($icon_set_slug)
{
    $table_name = get_icons_table_full_name();

    // Prepare the SQL query
    $sql = wpdb()->prepare(
        "SELECT * FROM `$table_name` WHERE `icon_set_slug` = %s",
        $icon_set_slug
    );

    /**
     * @var list<array{id: string, name: string, slug: string, icon_set_slug: string, svg_code: string}>|null $results
     * @psalm-suppress UndefinedConstant
     * @psalm-suppress MixedArgument
     */
    $results = wpdb()->get_results($sql, ARRAY_A);

    if (!is_array($results)) {
        $results = [];
    }

    return $results;
}

/**
 * @param array $uploaded_icons
 * @psalm-param list<array{name: string, slug: string, svgCode: string}> $uploaded_icons
 * @param array $icon_set_to_use
 * @psalm-param array{name: string, slug: string} $icon_set_to_use
 * @return void
 */
function upload_icons($uploaded_icons, $icon_set_to_use)
{
    $generator_fn = function () use ($uploaded_icons, $icon_set_to_use): \Generator {
        foreach ($uploaded_icons as $icon) {
            yield map_webapp_icon_to_db_icon($icon, $icon_set_to_use['slug']);
        }
    };

    create_icon_set($icon_set_to_use);
    create_icons_from_iterable($generator_fn());
}

function install_stock_icons_to_db()
{
    $stock_icons = read_icons_from_stock_icons_file();

    /**
     * @var string[] $icon_set_slugs
     */
    $icon_set_slugs = [];

    $generator_fn = function () use ($stock_icons, &$icon_set_slugs): \Generator {
        foreach ($stock_icons as $icon) {
            /**
             * @psalm-suppress MixedArgument
             */
            if (!in_array($icon['icon_set_slug'], $icon_set_slugs)) {
                $icon_set_slugs[] = $icon['icon_set_slug'];
            }

            yield $icon;
        }
    };

    create_icons_from_iterable($generator_fn());

    /**
     * @psalm-suppress MixedAssignment
     */
    foreach ($icon_set_slugs as $icon_set_slug) {
        /**
         * @psalm-suppress ArgumentTypeCoercion
         */
        create_icon_set([
            // For stock icons, slug is also a name
            'name' => $icon_set_slug,
            'slug' => $icon_set_slug,
        ]);
    }
}

/**
 * @return string
 */
function get_icons_table_full_name()
{
    return wpdb()->prefix . 'breakdance_icons';
}

/**
 * @return string
 */
function get_icon_sets_table_full_name()
{
    return wpdb()->prefix . 'breakdance_icon_sets';
}

/**
 * @param array $icon_set_definition
 * @psalm-param array{name: string, slug: string} $icon_set_definition
 *
 * @return void
 */
function create_icon_set($icon_set_definition)
{
    wpdb()->replace(
        get_icon_sets_table_full_name(),
        [
            'name' => $icon_set_definition['name'],
            'slug' => $icon_set_definition['slug']
        ]
    );
}

/**
 * @param string $icon_set_slug
 * @return void
 */
function delete_icon_set($icon_set_slug)
{
    wpdb()->delete(get_icon_sets_table_full_name(), [
        'slug' => $icon_set_slug,
    ]);
    wpdb()->delete(get_icons_table_full_name(), [
        'icon_set_slug' => $icon_set_slug,
    ]);
}

/**
 * @param int $id
 * @return void
 */
function delete_icon($id)
{
    wpdb()->delete(get_icons_table_full_name(), [
        'id' => $id,
    ]);
}

/**
 * @param array $icons
 * @psalm-param list<array{name: string, slug: string, icon_set_slug: string, svg_code: string}> $icons
 * @return void
 */
function insert_icons_batch(array $icons)
{
    $sql_query = sprintf(
        'INSERT INTO `%s` (`name`, `slug`, `icon_set_slug`, `svg_code`)
        VALUES %s
        ON DUPLICATE KEY UPDATE `svg_code` = VALUES(`svg_code`), `name` = VALUES(`name`)',
        get_icons_table_full_name(),
        join(
            ',',
            array_map(function ($icon) {
                return wpdb()->prepare(
                    '(%s,%s,%s,%s)',
                    trim($icon['name']),
                    trim($icon['slug']),
                    trim($icon['icon_set_slug']),
                    trim($icon['svg_code'])
                );
            }, $icons)
        )
    );

    wpdb()->query($sql_query);
}

/**
 * @psalm-param iterable<int, array{name: string, slug: string, icon_set_slug: string, svg_code: string}> $icons
 * @return void
 */
function create_icons_from_iterable(iterable $icons)
{
    /** @psalm-var list<array{name: string, slug: string, icon_set_slug: string, svg_code: string}> $buffer */
    $buffer = [];

    foreach ($icons as $icon) {
        $buffer[] = $icon;

        if (sizeof($buffer) >= 100) {
            insert_icons_batch($buffer);
            $buffer = [];
        }
    }

    if (sizeof($buffer) > 0) {
        insert_icons_batch($buffer);
    }
}

function clear_icons_table()
{
    wpdb()->query(sprintf('DELETE FROM `%s`;', get_icons_table_full_name()));
}

function clear_icon_sets_table()
{
    wpdb()->query(sprintf('DELETE FROM `%s`;', get_icon_sets_table_full_name()));
}

function migrate_icons_from_wp_option_to_db_table()
{
    /**
     * @psalm-suppress MixedAssignment
     */
    $icon_sets_to_migrate = json_decode((string) get_global_option('icons'), true);

    if (!is_array($icon_sets_to_migrate) || sizeof($icon_sets_to_migrate) === 0) {
        return;
    }

    $generator_fn = function () use ($icon_sets_to_migrate): \Generator {
        /**
         * @psalm-suppress MixedAssignment
         */
        foreach ($icon_sets_to_migrate as $icon_set) {
            if (!isset($icon_set['name'], $icon_set['slug'], $icon_set['icons']) || !is_array($icon_set['icons'])) {
                continue;
            }

            /**
             * @psalm-suppress MixedArgument
             */
            create_icon_set($icon_set);

            /**
             * @psalm-suppress MixedArrayAccess
             * @psalm-suppress MixedAssignment
             */
            foreach ($icon_set['icons'] as $icon) {
                if (!isset($icon['name'], $icon['slug'], $icon['svgCode'])) {
                    continue;
                }

                /**
                 * @psalm-suppress MixedArrayAccess
                 */
                yield [
                    'name' => $icon['name'],
                    'slug' => $icon['slug'],
                    'icon_set_slug' => $icon_set['slug'],
                    'svg_code' => $icon['svgCode'],
                ];
            }
        }
    };

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    create_icons_from_iterable($generator_fn());
}

/**
 * @param array $webapp_icon
 * @param string $icon_set_slug
 * @return array
 * @psalm-param array{name: string, slug: string, svgCode: string} $webapp_icon
 * @psalm-return array{name: string, slug: string, svg_code: string, icon_set_slug: string}
 */
function map_webapp_icon_to_db_icon($webapp_icon, $icon_set_slug)
{
    return [
        'name' => $webapp_icon['name'],
        'slug' => $webapp_icon['slug'],
        'icon_set_slug' => $icon_set_slug,
        'svg_code' => $webapp_icon['svgCode'],
    ];
}

/**
 * @param $icon_from_db
 * @return array
 * @psalm-param array{id: string, name: string, slug: string, svg_code: string, icon_set_slug: string} $icon_from_db
 * @psalm-return array{id: int, name: string, slug: string, svgCode: string, iconSetSlug: string} $webapp_icon
 */
function map_db_icon_to_webapp_icon($icon_from_db)
{
    return [
        'id' => (int) $icon_from_db['id'],
        'slug' => $icon_from_db['slug'],
        'name' => $icon_from_db['name'],
        'svgCode' => $icon_from_db['svg_code'],
        'iconSetSlug' => $icon_from_db['icon_set_slug'],
    ];
}

/**
 * @return string
 */
function get_stock_icons_filepath()
{
    return dirname(__FILE__) . '/stock-icons.csv';
}


/**
 * Uncomment and call this when you need to export current icons to stock icons file
 * @param iterable $icons
 * @return bool
 */
// function export_icons_to_stock_icons_file(iterable $icons)
// {
//     $file_handle = fopen(get_stock_icons_filepath(), 'w');
//     if ($file_handle === false) {
//         return false;
//     }

//     foreach ($icons as $icon) {
//         fputcsv(
//             $file_handle,
//             [
//                 $icon['slug'],
//                 $icon['name'],
//                 $icon['icon_set_slug'],
//                 $icon['svg_code']
//             ]
//         );
//     }

//     fclose($file_handle);

//     return true;
// }

// $icons = find_icons([], 1000000000000, true);
// export_icons_to_stock_icons_file($icons);


/**
 * @psalm-suppress MoreSpecificReturnType
 * @return \Generator<int, array{name: string, slug: string, svg_code: string, icon_set_slug: string}>
 */
function read_icons_from_stock_icons_file(): \Generator
{
    $file_handle = fopen(get_stock_icons_filepath(), 'r');

    if ($file_handle === false) {
        return;
    }

    try {
        while (($data = fgetcsv($file_handle)) !== false) {
            if (!is_array($data)) {
                continue;
            }

            // A blank line in a CSV file will be returned as an array comprising a single null
            // field, and will not be treated as an error.
            if (sizeof($data) === 1 && $data[array_key_first($data)] === null) {
                continue;
            }

            $icon = [];
            [
                $icon['slug'],
                $icon['name'],
                $icon['icon_set_slug'],
                $icon['svg_code']
            ] = $data;

            if (empty($icon['slug']) || empty($icon['name']) || empty($icon['icon_set_slug']) || empty($icon['svg_code'])) {
                continue;
            }

            yield $icon;
        }
    } finally {
        fclose($file_handle);
    }
}

/**
 * @param string $icon_set_slug
 * @return string|\WP_Error
 */
function generate_icon_set_zip($icon_set_slug) {
    if (!function_exists('wp_tempnam')) {
        /**
         * @psalm-suppress UndefinedConstant
         * @psalm-suppress UnresolvableInclude
         */
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }

    if (!class_exists( 'ZipArchive')) {
        return new \WP_Error('missing_zip_package', __('Unable to generate export file. ZipArchive not available.'));
    }

    $icons    = get_icons_in_set($icon_set_slug);
    $tmp_file = wp_tempnam("breakdance-icon-set-zip");
    $zip      = new ZipArchive();

    if ($zip->open($tmp_file,ZipArchive::OVERWRITE) === TRUE)  {
        foreach ($icons as $icon) {
            $clean_slug = rtrim($icon['slug'], '.');
            $zip->addFromString("{$clean_slug}.svg", $icon['svg_code']);
        }

        $zip->close();
    }

    return $tmp_file;
}
