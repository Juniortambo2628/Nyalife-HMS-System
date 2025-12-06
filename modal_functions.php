<?php
/**
 * Nyalife HMS - Modal and Loader Functions
 * 
 * This file contains functions for modal dialogs and page loaders.
 */

/**
 * Show a modal dialog
 * 
 * @param string $id Modal ID
 * @param string $title Modal title
 * @param string $content Modal content
 * @param string $size Modal size (sm, lg, xl)
 * @param bool $showFooter Whether to show footer
 * @return string Modal HTML
 */
if (!function_exists('showModal')) {
    function showModal($id, $title, $content, $size = 'lg', $showFooter = true) {
        $sizeClass = $size ? "modal-$size" : '';
        
        $html = "
        <div class='modal fade' id='$id' tabindex='-1' aria-labelledby='{$id}Label' aria-hidden='true'>
            <div class='modal-dialog $sizeClass'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title' id='{$id}Label'>$title</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                    </div>
                    <div class='modal-body'>
                        $content
                    </div>";
        
        if ($showFooter) {
            $html .= "
                    <div class='modal-footer'>
                        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                    </div>";
        }
        
        $html .= "
                </div>
            </div>
        </div>";
        
        return $html;
    }
}

/**
 * Show a confirmation modal
 * 
 * @param string $id Modal ID
 * @param string $title Modal title
 * @param string $message Confirmation message
 * @param string $confirmText Confirm button text
 * @param string $cancelText Cancel button text
 * @param string $confirmClass Confirm button class
 * @return string Modal HTML
 */
if (!function_exists('showConfirmModal')) {
    function showConfirmModal($id, $title, $message, $confirmText = 'Confirm', $cancelText = 'Cancel', $confirmClass = 'btn-danger') {
        $html = "
        <div class='modal fade' id='$id' tabindex='-1' aria-labelledby='{$id}Label' aria-hidden='true'>
            <div class='modal-dialog'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title' id='{$id}Label'>$title</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                    </div>
                    <div class='modal-body'>
                        <p>$message</p>
                    </div>
                    <div class='modal-footer'>
                        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>$cancelText</button>
                        <button type='button' class='btn $confirmClass' id='{$id}Confirm'>$confirmText</button>
                    </div>
                </div>
            </div>
        </div>";
        
        return $html;
    }
}

/**
 * Show an alert modal
 * 
 * @param string $id Modal ID
 * @param string $title Modal title
 * @param string $message Alert message
 * @param string $type Alert type (success, warning, danger, info)
 * @return string Modal HTML
 */
if (!function_exists('showAlertModal')) {
    function showAlertModal($id, $title, $message, $type = 'info') {
        $alertClass = "alert-$type";
        
        $html = "
        <div class='modal fade' id='$id' tabindex='-1' aria-labelledby='{$id}Label' aria-hidden='true'>
            <div class='modal-dialog'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title' id='{$id}Label'>$title</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                    </div>
                    <div class='modal-body'>
                        <div class='alert $alertClass' role='alert'>
                            $message
                        </div>
                    </div>
                    <div class='modal-footer'>
                        <button type='button' class='btn btn-primary' data-bs-dismiss='modal'>OK</button>
                    </div>
                </div>
            </div>
        </div>";
        
        return $html;
    }
}

/**
 * Show a loading modal
 * 
 * @param string $id Modal ID
 * @param string $message Loading message
 * @return string Modal HTML
 */
if (!function_exists('showLoadingModal')) {
    function showLoadingModal($id, $message = 'Loading...') {
        $html = "
        <div class='modal fade' id='$id' tabindex='-1' aria-hidden='true' data-bs-backdrop='static' data-bs-keyboard='false'>
            <div class='modal-dialog modal-sm'>
                <div class='modal-content'>
                    <div class='modal-body text-center'>
                        <div class='spinner-border text-primary' role='status'>
                            <span class='visually-hidden'>Loading...</span>
                        </div>
                        <p class='mt-2 mb-0'>$message</p>
                    </div>
                </div>
            </div>
        </div>";
        
        return $html;
    }
}

/**
 * Show page loader
 * 
 * @return string Loader HTML
 */
if (!function_exists('showPageLoader')) {
    function showPageLoader() {
        return "
        <div id='pageLoader' class='position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center' style='background: rgba(0,0,0,0.5); z-index: 9999;'>
            <div class='text-center'>
                <div class='spinner-border text-light' role='status' style='width: 3rem; height: 3rem;'>
                    <span class='visually-hidden'>Loading...</span>
                </div>
                <p class='text-light mt-2'>Loading...</p>
            </div>
        </div>";
    }
}

/**
 * Hide page loader
 * 
 * @return string JavaScript to hide loader
 */
if (!function_exists('hidePageLoader')) {
    function hidePageLoader() {
        return "
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const loader = document.getElementById('pageLoader');
                if (loader) {
                    loader.style.display = 'none';
                }
            });
        </script>";
    }
}

/**
 * Show a toast notification
 * 
 * @param string $message Toast message
 * @param string $type Toast type (success, warning, danger, info)
 * @param int $delay Delay in milliseconds
 * @return string Toast HTML
 */
if (!function_exists('showToast')) {
    function showToast($message, $type = 'info', $delay = 5000) {
        $toastId = 'toast_' . uniqid();
        $alertClass = "alert-$type";
        
        $html = "
        <div class='toast-container position-fixed top-0 end-0 p-3' style='z-index: 1055;'>
            <div id='$toastId' class='toast' role='alert' aria-live='assertive' aria-atomic='true'>
                <div class='toast-header'>
                    <strong class='me-auto'>Notification</strong>
                    <button type='button' class='btn-close' data-bs-dismiss='toast' aria-label='Close'></button>
                </div>
                <div class='toast-body'>
                    <div class='alert $alertClass mb-0' role='alert'>
                        $message
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const toast = new bootstrap.Toast(document.getElementById('$toastId'), {
                    delay: $delay
                });
                toast.show();
            });
        </script>";
        
        return $html;
    }
}

/**
 * Show a success toast
 * 
 * @param string $message Success message
 * @return string Toast HTML
 */
if (!function_exists('showSuccessToast')) {
    function showSuccessToast($message) {
        return showToast($message, 'success');
    }
}

/**
 * Show an error toast
 * 
 * @param string $message Error message
 * @return string Toast HTML
 */
if (!function_exists('showErrorToast')) {
    function showErrorToast($message) {
        return showToast($message, 'danger');
    }
}

/**
 * Show a warning toast
 * 
 * @param string $message Warning message
 * @return string Toast HTML
 */
if (!function_exists('showWarningToast')) {
    function showWarningToast($message) {
        return showToast($message, 'warning');
    }
}

/**
 * Show an info toast
 * 
 * @param string $message Info message
 * @return string Toast HTML
 */
if (!function_exists('showInfoToast')) {
    function showInfoToast($message) {
        return showToast($message, 'info');
    }
}

/**
 * Initialize modal functionality
 * 
 * @return string JavaScript for modal initialization
 */
if (!function_exists('initModals')) {
    function initModals() {
        return "
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize all modals
                var modals = document.querySelectorAll('.modal');
                modals.forEach(function(modal) {
                    new bootstrap.Modal(modal);
                });
                
                // Auto-hide toasts after delay
                var toasts = document.querySelectorAll('.toast');
                toasts.forEach(function(toast) {
                    var bsToast = new bootstrap.Toast(toast);
                    bsToast.show();
                });
            });
        </script>";
    }
}

/**
 * Show a form modal
 * 
 * @param string $id Modal ID
 * @param string $title Modal title
 * @param string $formContent Form content
 * @param string $submitText Submit button text
 * @param string $cancelText Cancel button text
 * @return string Modal HTML
 */
if (!function_exists('showFormModal')) {
    function showFormModal($id, $title, $formContent, $submitText = 'Submit', $cancelText = 'Cancel') {
        $html = "
        <div class='modal fade' id='$id' tabindex='-1' aria-labelledby='{$id}Label' aria-hidden='true'>
            <div class='modal-dialog modal-lg'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title' id='{$id}Label'>$title</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                    </div>
                    <form id='{$id}Form'>
                        <div class='modal-body'>
                            $formContent
                        </div>
                        <div class='modal-footer'>
                            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>$cancelText</button>
                            <button type='submit' class='btn btn-primary'>$submitText</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>";
        
        return $html;
    }
} 