import Quill from 'quill';
import 'quill/dist/quill.snow.css'; // Import Quill styles

/**
 * Initialize Quill editor
 * @param {string|HTMLElement} element - Editor container
 * @param {Object} options - Quill options
 * @returns {Quill} Quill instance
 */
export function createRichTextEditor(element, options = {}) {
    const defaultOptions = {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'color': [] }, { 'background': [] }],
                ['link', 'clean']
            ]
        },
        placeholder: 'Type something...'
    };

    return new Quill(element, { ...defaultOptions, ...options });
}
