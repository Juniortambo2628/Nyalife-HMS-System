import Swal from 'sweetalert2';

/**
 * Show a success alert
 * @param {string} title - Alert title
 * @param {string} text - Alert text
 */
export function showSuccessAlert(title, text) {
    return Swal.fire({
        icon: 'success',
        title: title,
        text: text,
        confirmButtonColor: '#20c997'
    });
}

/**
 * Show an error alert
 * @param {string} title - Alert title
 * @param {string} text - Alert text
 */
export function showErrorAlert(title, text) {
    return Swal.fire({
        icon: 'error',
        title: title,
        text: text,
        confirmButtonColor: '#dc3545'
    });
}

/**
 * Show a confirmation dialog
 * @param {string} title - Dialog title
 * @param {string} text - Dialog text
 * @param {string} confirmButtonText - Confirm button text
 * @returns {Promise<boolean>} True if confirmed
 */
export function showConfirmDialog(title, text, confirmButtonText = 'Yes, proceed') {
    return Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#20c997',
        cancelButtonColor: '#d33',
        confirmButtonText: confirmButtonText
    }).then((result) => {
        return result.isConfirmed;
    });
}

/**
 * Show a toast notification
 * @param {string} icon - Toast icon (success, error, warning, info)
 * @param {string} title - Toast title
 */
export function showToast(icon, title) {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    Toast.fire({
        icon: icon,
        title: title
    });
}
