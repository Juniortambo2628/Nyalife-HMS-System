<?php
/**
 * Nyalife HMS - Reusable Table Component
 * 
 * This file provides functions to generate standardized data tables.
 */

/**
 * Generate a data table
 * 
 * @param string $id Table ID
 * @param array $columns Table columns with 'title' and optional 'width', 'class', 'sortable' properties
 * @param array $data Table data rows
 * @param array $options Additional table options
 * @return string HTML for the table
 */
function generateTable($id, $columns, $data = [], $options = []) {
    // Default options
    $defaultOptions = [
        'class' => 'table table-striped table-hover',
        'responsive' => true,
        'datatable' => true,
        'datatable_options' => [
            'pageLength' => 10,
            'lengthMenu' => [5, 10, 25, 50, 100],
            'order' => [[0, 'asc']]
        ],
        'empty_message' => 'No data available',
        'bordered' => true,
        'small' => false,
        'footer' => false,
        'card' => true,
        'card_header' => null,
        'card_class' => 'shadow-sm border-0',
        'actions' => []
    ];
    
    // Merge with user options
    $options = array_merge($defaultOptions, $options);
    
    // Build table classes
    $tableClass = $options['class'];
    if ($options['bordered']) {
        $tableClass .= ' table-bordered';
    }
    if ($options['small']) {
        $tableClass .= ' table-sm';
    }
    if ($options['responsive']) {
        $tableClass .= ' w-100';
    }
    
    // Start output
    $html = '';
    
    // Card wrapper if enabled
    if ($options['card']) {
        $html .= '<div class="card ' . $options['card_class'] . '">';
        
        if ($options['card_header']) {
            $html .= '<div class="card-header bg-white">';
            $html .= '<div class="d-flex justify-content-between align-items-center">';
            $html .= '<h5 class="mb-0">' . $options['card_header'] . '</h5>';
            
            // Action buttons
            if (!empty($options['actions'])) {
                $html .= '<div class="btn-group">';
                foreach ($options['actions'] as $action) {
                    $btnId = isset($action['id']) ? ' id="' . $action['id'] . '"' : '';
                    $btnClass = isset($action['class']) ? ' ' . $action['class'] : ' btn-primary btn-sm';
                    $btnIcon = isset($action['icon']) ? '<i class="' . $action['icon'] . ' me-1"></i>' : '';
                    $btnAttributes = isset($action['attributes']) ? ' ' . $action['attributes'] : '';
                    
                    $html .= '<button type="button" class="btn' . $btnClass . '"' . $btnId . $btnAttributes . '>';
                    $html .= $btnIcon . $action['text'];
                    $html .= '</button>';
                }
                $html .= '</div>';
            }
            
            $html .= '</div>';
            $html .= '</div>';
        }
        
        $html .= '<div class="card-body p-0">';
    }
    
    // Responsive wrapper if enabled
    if ($options['responsive']) {
        $html .= '<div class="table-responsive">';
    }
    
    // Start table
    $html .= '<table id="' . $id . '" class="' . $tableClass . '">';
    
    // Table header
    $html .= '<thead>';
    $html .= '<tr>';
    foreach ($columns as $column) {
        $width = isset($column['width']) ? ' width="' . $column['width'] . '"' : '';
        $class = isset($column['class']) ? ' class="' . $column['class'] . '"' : '';
        $sortable = isset($column['sortable']) && $column['sortable'] === false ? ' data-orderable="false"' : '';
        
        $html .= '<th' . $width . $class . $sortable . '>' . $column['title'] . '</th>';
    }
    $html .= '</tr>';
    $html .= '</thead>';
    
    // Table footer if enabled
    if ($options['footer']) {
        $html .= '<tfoot>';
        $html .= '<tr>';
        foreach ($columns as $column) {
            $width = isset($column['width']) ? ' width="' . $column['width'] . '"' : '';
            $class = isset($column['class']) ? ' class="' . $column['class'] . '"' : '';
            
            $html .= '<th' . $width . $class . '>' . $column['title'] . '</th>';
        }
        $html .= '</tr>';
        $html .= '</tfoot>';
    }
    
    // Table body
    $html .= '<tbody>';
    
    if (empty($data)) {
        $html .= '<tr><td colspan="' . count($columns) . '" class="text-center">' . $options['empty_message'] . '</td></tr>';
    } else {
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($columns as $column) {
                $key = $column['key'] ?? '';
                $formatter = $column['formatter'] ?? null;
                $tdClass = isset($column['cell_class']) ? ' class="' . $column['cell_class'] . '"' : '';
                
                $value = isset($row[$key]) ? $row[$key] : '';
                
                // Apply formatter if provided
                if (is_callable($formatter)) {
                    $value = call_user_func($formatter, $value, $row);
                }
                
                $html .= '<td' . $tdClass . '>' . $value . '</td>';
            }
            $html .= '</tr>';
        }
    }
    
    $html .= '</tbody>';
    $html .= '</table>';
    
    // Close responsive wrapper
    if ($options['responsive']) {
        $html .= '</div>';
    }
    
    // Close card wrapper
    if ($options['card']) {
        $html .= '</div>';
        $html .= '</div>';
    }
    
    // DataTable initialization script
    if ($options['datatable']) {
        $dtOptions = json_encode($options['datatable_options']);
        
        $html .= '<script>';
        $html .= 'document.addEventListener("DOMContentLoaded", function() {';
        $html .= '  if ($.fn.DataTable) {';
        $html .= '    $("#' . $id . '").DataTable(' . $dtOptions . ');';
        $html .= '  }';
        $html .= '});';
        $html .= '</script>';
    }
    
    return $html;
}

/**
 * Print a data table
 * 
 * @param string $id Table ID
 * @param array $columns Table columns with 'title' and optional 'width', 'class', 'sortable' properties
 * @param array $data Table data rows
 * @param array $options Additional table options
 */
function printTable($id, $columns, $data = [], $options = []) {
    echo generateTable($id, $columns, $data, $options);
}

/**
 * Generate a standard action column formatter for data tables
 * 
 * @param array $actions Array of action definitions
 * @return callable Formatter function
 */
function tableActionFormatter($actions) {
    return function($value, $row) use ($actions) {
        $html = '<div class="btn-group btn-group-sm">';
        
        foreach ($actions as $action) {
            // Skip if condition function returns false
            if (isset($action['condition']) && is_callable($action['condition']) && !call_user_func($action['condition'], $row)) {
                continue;
            }
            
            $icon = isset($action['icon']) ? '<i class="' . $action['icon'] . '"></i>' : '';
            $text = isset($action['text']) ? ' ' . $action['text'] : '';
            $tooltip = isset($action['tooltip']) ? ' data-bs-toggle="tooltip" title="' . $action['tooltip'] . '"' : '';
            $class = isset($action['class']) ? $action['class'] : 'btn-primary';
            
            // Replace placeholders in url and attributes
            $url = isset($action['url']) ? $action['url'] : '#';
            $attributes = isset($action['attributes']) ? $action['attributes'] : '';
            
            foreach ($row as $key => $val) {
                $url = str_replace('{{' . $key . '}}', $val, $url);
                $attributes = str_replace('{{' . $key . '}}', $val, $attributes);
            }
            
            if (isset($action['url'])) {
                // Link button
                $html .= '<a href="' . $url . '" class="btn ' . $class . '"' . $tooltip . ' ' . $attributes . '>';
                $html .= $icon . $text;
                $html .= '</a>';
            } else {
                // Regular button
                $html .= '<button type="button" class="btn ' . $class . '"' . $tooltip . ' ' . $attributes . '>';
                $html .= $icon . $text;
                $html .= '</button>';
            }
        }
        
        $html .= '</div>';
        return $html;
    };
}

/**
 * Generate a standard status formatter for data tables
 * 
 * @param array $statusClasses Associative array of status => class mappings
 * @return callable Formatter function
 */
function tableStatusFormatter($statusClasses) {
    return function($value) use ($statusClasses) {
        $class = isset($statusClasses[$value]) ? $statusClasses[$value] : 'badge-secondary';
        return '<span class="badge ' . $class . '">' . ucfirst($value) . '</span>';
    };
}

/**
 * Generate a standard date formatter for data tables
 * 
 * @param string $format Date format
 * @return callable Formatter function
 */
function tableDateFormatter($format = 'M d, Y') {
    return function($value) use ($format) {
        if (empty($value)) return '';
        $date = new DateTime($value);
        return $date->format($format);
    };
}

/**
 * Generate a standard boolean formatter for data tables
 * 
 * @param string $trueText Text for true values
 * @param string $falseText Text for false values
 * @param string $trueClass CSS class for true values
 * @param string $falseClass CSS class for false values
 * @return callable Formatter function
 */
function tableBooleanFormatter($trueText = 'Yes', $falseText = 'No', $trueClass = 'text-success', $falseClass = 'text-danger') {
    return function($value) use ($trueText, $falseText, $trueClass, $falseClass) {
        if ($value) {
            return '<span class="' . $trueClass . '">' . $trueText . '</span>';
        } else {
            return '<span class="' . $falseClass . '">' . $falseText . '</span>';
        }
    };
}

/**
 * Generate a standard currency formatter for data tables
 * 
 * @param string $symbol Currency symbol
 * @param int $decimals Number of decimal places
 * @return callable Formatter function
 */
function tableCurrencyFormatter($symbol = '$', $decimals = 2) {
    return function($value) use ($symbol, $decimals) {
        if (!is_numeric($value)) return $value;
        return $symbol . number_format((float)$value, $decimals);
    };
}
?> 