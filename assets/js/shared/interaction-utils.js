import QRCode from 'qrcode';
import ClipboardJS from 'clipboard';
import Fuse from 'fuse.js';

/**
 * Generate QR Code
 * @param {string|HTMLElement} element - Canvas element or selector
 * @param {string} text - Text to encode
 * @param {Object} options - QRCode options
 */
export async function generateQRCode(element, text, options = {}) {
    try {
        const canvas = typeof element === 'string' ? document.querySelector(element) : element;
        if (!canvas) throw new Error('Canvas element not found');
        
        await QRCode.toCanvas(canvas, text, {
            width: 200,
            margin: 2,
            color: {
                dark: '#000000',
                light: '#ffffff'
            },
            ...options
        });
    } catch (error) {
        console.error('QR Code generation error:', error);
    }
}

/**
 * Initialize Clipboard copy functionality
 * @param {string} selector - Button selector (e.g., '.btn-copy')
 */
export function initClipboard(selector = '.btn-copy') {
    const clipboard = new ClipboardJS(selector);
    
    clipboard.on('success', (e) => {
        e.clearSelection();
        // You can import showToast from alert-utils here if needed, 
        // or dispatch a custom event
        const event = new CustomEvent('clipboard:success', { detail: { text: e.text } });
        document.dispatchEvent(event);
    });

    clipboard.on('error', (e) => {
        console.error('Clipboard error:', e);
        const event = new CustomEvent('clipboard:error');
        document.dispatchEvent(event);
    });
    
    return clipboard;
}

/**
 * Create a fuzzy search instance
 * @param {Array} list - List of items to search
 * @param {Array} keys - Keys to search in
 * @returns {Fuse} Fuse instance
 */
export function createFuzzySearch(list, keys = ['name', 'email']) {
    const options = {
        keys: keys,
        threshold: 0.3, // 0.0 = perfect match, 1.0 = match anything
        includeScore: true
    };
    
    return new Fuse(list, options);
}
