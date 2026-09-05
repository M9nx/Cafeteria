<?php

declare(strict_types=1);

/**
 * Shared catalogue query builder for Available / Curated sections.
 *
 * @param int|null $selectedCategoryId
 * @param int $availablePage
 * @param int $curatedPage
 * @return callable(array<string, mixed>): string
 */
$catalogQuery = static function (array $overrides = []) use (
    $selectedCategoryId,
    $availablePage,
    $curatedPage,
): string {
    $category = array_key_exists('category', $overrides)
        ? $overrides['category']
        : $selectedCategoryId;
    $page = array_key_exists('page', $overrides)
        ? $overrides['page']
        : $availablePage;
    $cpage = array_key_exists('cpage', $overrides)
        ? $overrides['cpage']
        : $curatedPage;

    $params = [];

    if ($category !== null && (int) $category > 0) {
        $params['category'] = (int) $category;
    }

    if ((int) $page > 1) {
        $params['page'] = (int) $page;
    }

    if ((int) $cpage > 1) {
        $params['cpage'] = (int) $cpage;
    }

    return $params === [] ? '/' : ('/?' . http_build_query($params));
};
