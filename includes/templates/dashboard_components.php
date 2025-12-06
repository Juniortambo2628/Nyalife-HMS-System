<?php
/**
 * Nyalife HMS - Dashboard Components
 * 
 * This file provides reusable dashboard components.
 */

/**
 * Generate a statistics card
 * 
 * @param string $title Card title
 * @param string $value Card value
 * @param string $icon Card icon class
 * @param string $color Card color (primary, success, info, warning, danger)
 * @param string $footerText Optional footer text
 * @param string $footerIcon Optional footer icon
 * @return string HTML for the statistics card
 */
function generateStatsCard($title, $value, $icon, $color = 'primary', $footerText = '', $footerIcon = '') {
    $html = <<<HTML
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-$color shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-$color text-uppercase mb-1">
                            $title
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">$value</div>
                    </div>
                    <div class="col-auto">
                        <i class="$icon fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
    HTML;

    if ($footerText) {
        $html .= <<<HTML
            <div class="card-footer">
                <div class="small text-muted">
                    <i class="$footerIcon me-1"></i> $footerText
                </div>
            </div>
        HTML;
    }

    $html .= '</div></div>';
    
    return $html;
}

/**
 * Print a statistics card
 * 
 * @param string $title Card title
 * @param string $value Card value
 * @param string $icon Card icon class
 * @param string $color Card color (primary, success, info, warning, danger)
 * @param string $footerText Optional footer text
 * @param string $footerIcon Optional footer icon
 */
function printStatsCard($title, $value, $icon, $color = 'primary', $footerText = '', $footerIcon = '') {
    echo generateStatsCard($title, $value, $icon, $color, $footerText, $footerIcon);
}

/**
 * Generate statistics cards row
 * 
 * @param array $cards Array of card definitions
 * @return string HTML for the statistics cards row
 */
function generateStatsCardsRow($cards) {
    $html = '<div class="row">';
    
    foreach ($cards as $card) {
        $html .= generateStatsCard(
            $card['title'],
            $card['value'],
            $card['icon'],
            $card['color'] ?? 'primary',
            $card['footer_text'] ?? '',
            $card['footer_icon'] ?? ''
        );
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Print statistics cards row
 * 
 * @param array $cards Array of card definitions
 */
function printStatsCardsRow($cards) {
    echo generateStatsCardsRow($cards);
}

/**
 * Generate a content card
 * 
 * @param string $title Card title
 * @param string $content Card content
 * @param array $options Additional options
 * @return string HTML for the content card
 */
function generateContentCard($title, $content, $options = []) {
    // Default options
    $defaultOptions = [
        'footer' => '',
        'header_buttons' => [],
        'card_class' => 'shadow mb-4',
        'body_class' => '',
        'header_class' => 'py-3 d-flex flex-row align-items-center justify-content-between'
    ];
    
    // Merge with user options
    $options = array_merge($defaultOptions, $options);
    
    $html = '<div class="card ' . $options['card_class'] . '">';
    
    // Card header
    $html .= '<div class="card-header ' . $options['header_class'] . '">';
    $html .= '<h6 class="m-0 font-weight-bold text-primary">' . $title . '</h6>';
    
    // Header buttons
    if (!empty($options['header_buttons'])) {
        $html .= '<div class="btn-group">';
        foreach ($options['header_buttons'] as $button) {
            $btnLink = $button['url'] ?? '#';
            $btnClass = $button['class'] ?? 'btn-primary btn-sm';
            $btnIcon = isset($button['icon']) ? '<i class="' . $button['icon'] . ' me-1"></i>' : '';
            $btnAttr = $button['attributes'] ?? '';
            
            $html .= '<a href="' . $btnLink . '" class="btn ' . $btnClass . '" ' . $btnAttr . '>';
            $html .= $btnIcon . $button['text'];
            $html .= '</a>';
        }
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    // Card body
    $html .= '<div class="card-body ' . $options['body_class'] . '">';
    $html .= $content;
    $html .= '</div>';
    
    // Card footer
    if ($options['footer']) {
        $html .= '<div class="card-footer">';
        $html .= $options['footer'];
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Print a content card
 * 
 * @param string $title Card title
 * @param string $content Card content
 * @param array $options Additional options
 */
function printContentCard($title, $content, $options = []) {
    echo generateContentCard($title, $content, $options);
}

/**
 * Generate a chart container
 * 
 * @param string $chartId Chart ID
 * @param string $title Chart title
 * @param array $options Additional options
 * @return string HTML for the chart container
 */
function generateChartContainer($chartId, $title, $options = []) {
    // Default options
    $defaultOptions = [
        'height' => 400,
        'footer' => '',
        'card_class' => 'shadow mb-4'
    ];
    
    // Merge with user options
    $options = array_merge($defaultOptions, $options);
    
    $html = '<div class="card ' . $options['card_class'] . '">';
    $html .= '<div class="card-header py-3">';
    $html .= '<h6 class="m-0 font-weight-bold text-primary">' . $title . '</h6>';
    $html .= '</div>';
    $html .= '<div class="card-body">';
    $html .= '<div class="chart-container" style="position: relative; height:' . $options['height'] . 'px;">';
    $html .= '<canvas id="' . $chartId . '"></canvas>';
    $html .= '</div>';
    
    if ($options['footer']) {
        $html .= '<div class="mt-3 small text-muted">' . $options['footer'] . '</div>';
    }
    
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}

/**
 * Print a chart container
 * 
 * @param string $chartId Chart ID
 * @param string $title Chart title
 * @param array $options Additional options
 */
function printChartContainer($chartId, $title, $options = []) {
    echo generateChartContainer($chartId, $title, $options);
}

/**
 * Generate the JavaScript for a Chart.js chart
 * 
 * @param string $chartId Chart element ID
 * @param string $type Chart type (line, bar, pie, doughnut, etc.)
 * @param array $labels Chart labels
 * @param array $datasets Chart datasets
 * @param array $options Chart options
 * @return string JavaScript code for the chart
 */
function generateChartJs($chartId, $type, $labels, $datasets, $options = []) {
    // Convert PHP arrays to JSON
    $labelsJson = json_encode($labels);
    $datasetsJson = json_encode($datasets);
    $optionsJson = !empty($options) ? json_encode($options) : '{}';
    
    $js = <<<JS
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var ctx = document.getElementById('$chartId').getContext('2d');
        var myChart = new Chart(ctx, {
            type: '$type',
            data: {
                labels: $labelsJson,
                datasets: $datasetsJson
            },
            options: $optionsJson
        });
    });
    </script>
    JS;
    
    return $js;
}

/**
 * Print the JavaScript for a Chart.js chart
 * 
 * @param string $chartId Chart element ID
 * @param string $type Chart type (line, bar, pie, doughnut, etc.)
 * @param array $labels Chart labels
 * @param array $datasets Chart datasets
 * @param array $options Chart options
 */
function printChartJs($chartId, $type, $labels, $datasets, $options = []) {
    echo generateChartJs($chartId, $type, $labels, $datasets, $options);
}

/**
 * Generate a simple info alert
 * 
 * @param string $message Alert message
 * @param string $type Alert type (primary, secondary, success, danger, warning, info)
 * @param bool $dismissible Whether the alert can be dismissed
 * @return string HTML for the alert
 */
if (!function_exists('generateAlert')) {
    function generateAlert($message, $type = 'info', $dismissible = false) {
        $dismissClass = $dismissible ? 'alert-dismissible fade show' : '';
        $dismissButton = $dismissible ? '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' : '';
        
        $html = '<div class="alert alert-' . $type . ' ' . $dismissClass . '" role="alert">';
        $html .= $message;
        $html .= $dismissButton;
        $html .= '</div>';
        
        return $html;
    }
}

/**
 * Print a simple info alert
 * 
 * @param string $message Alert message
 * @param string $type Alert type (primary, secondary, success, danger, warning, info)
 * @param bool $dismissible Whether the alert can be dismissed
 */
function printAlert($message, $type = 'info', $dismissible = false) {
    echo generateAlert($message, $type, $dismissible);
}

/**
 * Generate empty state placeholder for dashboards
 * 
 * @param string $message Message to display
 * @param string $icon Icon class
 * @param string $buttonText Optional button text
 * @param string $buttonUrl Optional button URL
 * @return string HTML for the empty state
 */
function generateEmptyState($message, $icon = 'fas fa-info-circle', $buttonText = '', $buttonUrl = '#') {
    $html = '<div class="text-center p-5 my-4">';
    $html .= '<i class="' . $icon . ' fa-4x text-muted mb-4"></i>';
    $html .= '<p class="lead">' . $message . '</p>';
    
    if ($buttonText) {
        $html .= '<a href="' . $buttonUrl . '" class="btn btn-primary mt-3">';
        $html .= $buttonText;
        $html .= '</a>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Print empty state placeholder for dashboards
 * 
 * @param string $message Message to display
 * @param string $icon Icon class
 * @param string $buttonText Optional button text
 * @param string $buttonUrl Optional button URL
 */
function printEmptyState($message, $icon = 'fas fa-info-circle', $buttonText = '', $buttonUrl = '#') {
    echo generateEmptyState($message, $icon, $buttonText, $buttonUrl);
} 