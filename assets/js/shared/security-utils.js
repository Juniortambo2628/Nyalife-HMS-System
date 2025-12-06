import CryptoJS from 'crypto-js';

const SECRET_KEY = process.env.APP_KEY || 'nyalife-hms-secret-key';

/**
 * Encrypt data
 * @param {any} data - Data to encrypt
 * @returns {string} Encrypted string
 */
export function encryptData(data) {
    try {
        const jsonString = JSON.stringify(data);
        return CryptoJS.AES.encrypt(jsonString, SECRET_KEY).toString();
    } catch (error) {
        console.error('Encryption error:', error);
        return null;
    }
}

/**
 * Decrypt data
 * @param {string} encryptedData - Encrypted string
 * @returns {any} Decrypted data
 */
export function decryptData(encryptedData) {
    try {
        const bytes = CryptoJS.AES.decrypt(encryptedData, SECRET_KEY);
        const decryptedString = bytes.toString(CryptoJS.enc.Utf8);
        return JSON.parse(decryptedString);
    } catch (error) {
        console.error('Decryption error:', error);
        return null;
    }
}
