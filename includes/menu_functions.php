<?php

define('MENU_DATA_FILE', __DIR__ . '/../data/menu.json');

/**
 * Load menu items from the JSON data file.
 *
 * @return array<int, array<string, mixed>>
 */
function load_menu_items(): array
{
    if (!file_exists(MENU_DATA_FILE)) {
        return [];
    }

    $raw = file_get_contents(MENU_DATA_FILE);
    if ($raw === false || $raw === '') {
        return [];
    }

    $items = json_decode($raw, true);

    return is_array($items) ? $items : [];
}

/**
 * Persist menu items to the JSON data file.
 *
 * @param array<int, array<string, mixed>> $items
 */
function save_menu_items(array $items): void
{
    if (!is_dir(dirname(MENU_DATA_FILE))) {
        mkdir(dirname(MENU_DATA_FILE), 0777, true);
    }

    file_put_contents(
        MENU_DATA_FILE,
        json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

/**
 * Group menu items by category for easier display on the frontend.
 *
 * @param array<int, array<string, mixed>> $items
 *
 * @return array<string, array<int, array<string, mixed>>>
 */
function group_menu_items_by_category(array $items): array
{
    $grouped = [];

    foreach ($items as $item) {
        $category = trim($item['category'] ?? 'Uncategorized');
        if ($category === '') {
            $category = 'Uncategorized';
        }

        if (!array_key_exists($category, $grouped)) {
            $grouped[$category] = [];
        }

        $grouped[$category][] = $item;
    }

    ksort($grouped, SORT_NATURAL | SORT_FLAG_CASE);

    return $grouped;
}
