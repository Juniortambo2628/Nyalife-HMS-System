<?php
/**
 * Nyalife HMS - Reusable Pagination Component
 * 
 * This file provides functions to generate standardized pagination controls.
 */

/**
 * Generate a pagination control
 * 
 * @param int $currentPage Current page number
 * @param int $totalPages Total number of pages
 * @param string $baseUrl Base URL for pagination links
 * @param array $options Additional pagination options
 * @return string HTML for the pagination control
 */
function generatePagination($currentPage, $totalPages, $baseUrl, $options = []) {
    // Default options
    $defaultOptions = [
        'size' => 'default', // default, sm, lg
        'alignment' => 'center', // start, center, end
        'query_param' => 'page',
        'show_first_last' => true,
        'show_prev_next' => true,
        'max_visible_pages' => 5,
        'first_text' => '&laquo;',
        'last_text' => '&raquo;',
        'prev_text' => '&lsaquo;',
        'next_text' => '&rsaquo;',
        'page_class' => '',
        'current_page_class' => 'active',
        'disabled_class' => 'disabled'
    ];
    
    // Merge with user options
    $options = array_merge($defaultOptions, $options);
    
    // Sanitize inputs
    $currentPage = max(1, (int)$currentPage);
    $totalPages = max(1, (int)$totalPages);
    
    // Build the pagination HTML
    $html = '';
    
    // If only one page, don't show pagination
    if ($totalPages <= 1) {
        return $html;
    }
    
    // Determine size class
    $sizeClass = '';
    switch ($options['size']) {
        case 'sm':
            $sizeClass = ' pagination-sm';
            break;
        case 'lg':
            $sizeClass = ' pagination-lg';
            break;
    }
    
    // Determine alignment class
    $alignmentClass = '';
    switch ($options['alignment']) {
        case 'start':
            $alignmentClass = ' justify-content-start';
            break;
        case 'center':
            $alignmentClass = ' justify-content-center';
            break;
        case 'end':
            $alignmentClass = ' justify-content-end';
            break;
    }
    
    // Start pagination
    $html .= '<nav aria-label="Page navigation">';
    $html .= '<ul class="pagination' . $sizeClass . $alignmentClass . '">';
    
    // First and Previous buttons
    if ($options['show_first_last'] && $currentPage > 1) {
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . addQueryParamToUrl($baseUrl, $options['query_param'], '1') . '" aria-label="First">';
        $html .= '<span aria-hidden="true">' . $options['first_text'] . '</span>';
        $html .= '</a>';
        $html .= '</li>';
    } elseif ($options['show_first_last']) {
        $html .= '<li class="page-item ' . $options['disabled_class'] . '">';
        $html .= '<span class="page-link">' . $options['first_text'] . '</span>';
        $html .= '</li>';
    }
    
    if ($options['show_prev_next'] && $currentPage > 1) {
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . addQueryParamToUrl($baseUrl, $options['query_param'], ($currentPage - 1)) . '" aria-label="Previous">';
        $html .= '<span aria-hidden="true">' . $options['prev_text'] . '</span>';
        $html .= '</a>';
        $html .= '</li>';
    } elseif ($options['show_prev_next']) {
        $html .= '<li class="page-item ' . $options['disabled_class'] . '">';
        $html .= '<span class="page-link">' . $options['prev_text'] . '</span>';
        $html .= '</li>';
    }
    
    // Calculate range of pages to show
    $maxVisiblePages = $options['max_visible_pages'];
    $halfVisiblePages = floor($maxVisiblePages / 2);
    
    $startPage = max(1, $currentPage - $halfVisiblePages);
    $endPage = min($totalPages, $startPage + $maxVisiblePages - 1);
    
    // Adjust start page if we're near the end
    if ($endPage - $startPage + 1 < $maxVisiblePages) {
        $startPage = max(1, $endPage - $maxVisiblePages + 1);
    }
    
    // Page numbers
    for ($i = $startPage; $i <= $endPage; $i++) {
        if ($i == $currentPage) {
            $html .= '<li class="page-item ' . $options['page_class'] . ' ' . $options['current_page_class'] . '">';
            $html .= '<span class="page-link">' . $i . '</span>';
            $html .= '</li>';
        } else {
            $html .= '<li class="page-item ' . $options['page_class'] . '">';
            $html .= '<a class="page-link" href="' . addQueryParamToUrl($baseUrl, $options['query_param'], $i) . '">' . $i . '</a>';
            $html .= '</li>';
        }
    }
    
    // Next and Last buttons
    if ($options['show_prev_next'] && $currentPage < $totalPages) {
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . addQueryParamToUrl($baseUrl, $options['query_param'], ($currentPage + 1)) . '" aria-label="Next">';
        $html .= '<span aria-hidden="true">' . $options['next_text'] . '</span>';
        $html .= '</a>';
        $html .= '</li>';
    } elseif ($options['show_prev_next']) {
        $html .= '<li class="page-item ' . $options['disabled_class'] . '">';
        $html .= '<span class="page-link">' . $options['next_text'] . '</span>';
        $html .= '</li>';
    }
    
    if ($options['show_first_last'] && $currentPage < $totalPages) {
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . addQueryParamToUrl($baseUrl, $options['query_param'], $totalPages) . '" aria-label="Last">';
        $html .= '<span aria-hidden="true">' . $options['last_text'] . '</span>';
        $html .= '</a>';
        $html .= '</li>';
    } elseif ($options['show_first_last']) {
        $html .= '<li class="page-item ' . $options['disabled_class'] . '">';
        $html .= '<span class="page-link">' . $options['last_text'] . '</span>';
        $html .= '</li>';
    }
    
    $html .= '</ul>';
    $html .= '</nav>';
    
    return $html;
}

/**
 * Print a pagination control
 * 
 * @param int $currentPage Current page number
 * @param int $totalPages Total number of pages
 * @param string $baseUrl Base URL for pagination links
 * @param array $options Additional pagination options
 */
function printPagination($currentPage, $totalPages, $baseUrl, $options = []) {
    echo generatePagination($currentPage, $totalPages, $baseUrl, $options);
}

/**
 * Calculate pagination values based on total items, limit, and current page
 * 
 * @param int $totalItems Total number of items
 * @param int $itemsPerPage Number of items per page
 * @param int $currentPage Current page number
 * @return array Pagination values (current_page, total_pages, offset, has_previous, has_next)
 */
function calculatePagination($totalItems, $itemsPerPage, $currentPage = 1) {
    // Sanitize inputs
    $totalItems = max(0, (int)$totalItems);
    $itemsPerPage = max(1, (int)$itemsPerPage);
    $currentPage = max(1, (int)$currentPage);
    
    // Calculate total pages
    $totalPages = ceil($totalItems / $itemsPerPage);
    
    // Ensure current page is in valid range
    $currentPage = min($currentPage, max(1, $totalPages));
    
    // Calculate offset for database queries
    $offset = ($currentPage - 1) * $itemsPerPage;
    
    return [
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'items_per_page' => $itemsPerPage,
        'total_items' => $totalItems,
        'offset' => $offset,
        'has_previous' => ($currentPage > 1),
        'has_next' => ($currentPage < $totalPages),
        'next_page' => min($totalPages, $currentPage + 1),
        'previous_page' => max(1, $currentPage - 1),
        'first_item' => $offset + 1,
        'last_item' => min($totalItems, $offset + $itemsPerPage)
    ];
}

/**
 * Generate a simple pagination info text
 * 
 * @param int $totalItems Total number of items
 * @param int $currentPage Current page number
 * @param int $itemsPerPage Number of items per page
 * @return string Pagination info text
 */
function generatePaginationInfo($totalItems, $currentPage, $itemsPerPage) {
    $pagination = calculatePagination($totalItems, $itemsPerPage, $currentPage);
    
    if ($totalItems === 0) {
        return 'No items found';
    }
    
    return sprintf(
        'Showing %d to %d of %d items',
        $pagination['first_item'],
        $pagination['last_item'],
        $pagination['total_items']
    );
}

/**
 * Helper function to add or update a query parameter in a URL
 * 
 * @param string $url Base URL
 * @param string $param Parameter name
 * @param string $value Parameter value
 * @return string URL with added/updated parameter
 */
function addQueryParamToUrl($url, $param, $value) {
    $parsedUrl = parse_url($url);
    
    // Start with the base URL
    $newUrl = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
    $newUrl .= isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
    $newUrl .= isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
    $newUrl .= isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
    
    // Parse existing query string
    $queryParams = [];
    if (isset($parsedUrl['query'])) {
        parse_str($parsedUrl['query'], $queryParams);
    }
    
    // Add/update the parameter
    $queryParams[$param] = $value;
    
    // Build the new query string
    $newUrl .= '?' . http_build_query($queryParams);
    
    // Add fragment if exists
    $newUrl .= isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '';
    
    return $newUrl;
}
?> 