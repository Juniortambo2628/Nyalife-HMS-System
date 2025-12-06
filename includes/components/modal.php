<?php
/**
 * Nyalife HMS - Reusable Modal Component
 *
 * This file provides a function to generate modals with consistent structure
 * while allowing custom content.
 */

$pageTitle = 'Modal - Nyalife HMS';

/**
 * Generate a modal with consistent structure
 *
 * @param string $id Modal ID
 * @param string $title Modal title
 * @param string $content Modal body content
 * @param array $options Additional options (size, footer buttons, etc)
 * @return string HTML for the modal
 */
function generateModal(string $id, string $title, string $content, $options = []): string
{
    // Default options
    $defaultOptions = [
        'size' => 'default', // Options: default, sm, lg, xl
        'header_class' => 'bg-primary text-white',
        'buttons' => [
            [
                'text' => 'Cancel',
                'class' => 'btn btn-secondary',
                'attributes' => 'data-bs-dismiss="modal"'
            ],
            [
                'text' => 'Save',
                'class' => 'btn btn-primary',
                'id' => 'submit' . ucfirst(str_replace('-', '', $id))
            ]
        ],
        'form_id' => null,
        'extra_footer' => '',
        'centered' => false,
        'dismissable' => true,
        'scrollable' => false
    ];

    // Merge with user options
    $options = array_merge($defaultOptions, $options);

    // Determine modal size class
    $sizeClass = '';
    switch ($options['size']) {
        case 'sm':
            $sizeClass = 'modal-sm';
            break;
        case 'lg':
            $sizeClass = 'modal-lg';
            break;
        case 'xl':
            $sizeClass = 'modal-xl';
            break;
    }

    // Generate modal HTML
    $modalHtml = '';
    $modalHtml .= '<div class="modal fade" id="' . $id . '" tabindex="-1" aria-labelledby="' . $id . 'Label" aria-hidden="true">';
    $modalHtml .= '<div class="modal-dialog ' . $sizeClass . ($options['centered'] ? ' modal-dialog-centered' : '') . ($options['scrollable'] ? ' modal-dialog-scrollable' : '') . '">';
    $modalHtml .= '<div class="modal-content">';

    // Modal header
    $modalHtml .= '<div class="modal-header ' . $options['header_class'] . '">';
    $modalHtml .= '<h5 class="modal-title" id="' . $id . 'Label">' . $title . '</h5>';
    if ($options['dismissable']) {
        $modalHtml .= '<button type="button" class="btn-close ' . (str_contains((string) $options['header_class'], 'text-white') ? 'btn-close-white' : '') . '" data-bs-dismiss="modal" aria-label="Close"></button>';
    }
    $modalHtml .= '</div>';

    // Modal body
    $modalHtml .= '<div class="modal-body">';
    if ($options['form_id']) {
        $modalHtml .= '<form id="' . $options['form_id'] . '">';
    }
    $modalHtml .= $content;
    if ($options['form_id']) {
        $modalHtml .= '</form>';
    }
    $modalHtml .= '</div>';

    // Modal footer with buttons
    if (!empty($options['buttons']) || !empty($options['extra_footer'])) {
        $modalHtml .= '<div class="modal-footer">';

        // Custom footer content
        if (!empty($options['extra_footer'])) {
            $modalHtml .= $options['extra_footer'];
        }

        // Buttons
        if (!empty($options['buttons'])) {
            foreach ($options['buttons'] as $button) {
                $btnId = isset($button['id']) ? ' id="' . $button['id'] . '"' : '';
                $attributes = isset($button['attributes']) ? ' ' . $button['attributes'] : '';
                $modalHtml .= '<button type="button" class="' . $button['class'] . '"' . $btnId . $attributes . '>' . $button['text'] . '</button>';
            }
        }

        $modalHtml .= '</div>';
    }

    $modalHtml .= '</div>'; // Close modal-content
    $modalHtml .= '</div>'; // Close modal-dialog
    $modalHtml .= '</div>'; // Close modal

    return $modalHtml;
}

/**
 * Generate a confirmation modal
 *
 * @param string $id Modal ID
 * @param string $title Modal title
 * @param string $message Confirmation message
 * @param string $confirmButtonText Text for confirm button
 * @param string $confirmButtonClass Class for confirm button
 * @return string HTML for the confirmation modal
 */
function generateConfirmationModal($id, $title, string $message, $confirmButtonText = 'Confirm', $confirmButtonClass = 'btn btn-danger')
{
    $options = [
        'size' => 'sm',
        'centered' => true,
        'buttons' => [
            [
                'text' => 'Cancel',
                'class' => 'btn btn-secondary',
                'attributes' => 'data-bs-dismiss="modal"'
            ],
            [
                'text' => $confirmButtonText,
                'class' => $confirmButtonClass,
                'id' => 'confirm' . ucfirst(str_replace('-', '', $id))
            ]
        ]
    ];

    $content = '<p class="mb-0">' . $message . '</p>';

    return generateModal($id, $title, $content, $options);
}

/**
 * Print a modal to the page
 *
 * @param string $id Modal ID
 * @param string $title Modal title
 * @param string $content Modal body content
 * @param array $options Additional options
 */
function printModal($id, $title, $content, $options = []): void
{
    echo generateModal($id, $title, $content, $options);
}

/**
 * Print a confirmation modal to the page
 *
 * @param string $id Modal ID
 * @param string $title Modal title
 * @param string $message Confirmation message
 * @param string $confirmButtonText Text for confirm button
 * @param string $confirmButtonClass Class for confirm button
 */
function printConfirmationModal($id, $title, $message, $confirmButtonText = 'Confirm', $confirmButtonClass = 'btn btn-danger'): void
{
    echo generateConfirmationModal($id, $title, $message, $confirmButtonText, $confirmButtonClass);
}

/**
 * Show a modal programmatically
 *
 * @param string $modalId The ID of the modal to show
 */
function showModal(string $modalId): void
{
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            var modal = new bootstrap.Modal(document.getElementById('" . $modalId . "'));
            modal.show();
        });
    </script>";
}

/**
 * Hide a modal programmatically
 *
 * @param string $modalId The ID of the modal to hide
 */
function hideModal(string $modalId): void
{
    echo "<script>
        var modal = bootstrap.Modal.getInstance(document.getElementById('" . $modalId . "'));
        if (modal) {
            modal.hide();
        }
    </script>";
}
