import Dropzone from 'dropzone';

/**
 * Initialize a Dropzone file uploader
 * @param {string|HTMLElement} element - The element to attach Dropzone to
 * @param {string} url - The upload URL
 * @param {Object} options - Dropzone options
 * @returns {Dropzone} The Dropzone instance
 */
export function createDropzone(element, url, options = {}) {
    // Prevent auto-discovery
    if (Dropzone) {
        Dropzone.autoDiscover = false;
    }

    const defaultOptions = {
        url: url,
        maxFilesize: 5, // MB
        acceptedFiles: 'image/*,application/pdf',
        addRemoveLinks: true,
        dictDefaultMessage: '<i class="fas fa-cloud-upload-alt fa-3x mb-3"></i><br>Drop files here or click to upload',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
    };

    return new Dropzone(element, { ...defaultOptions, ...options });
}
