import React from 'react';
import { formatReferenceType, getReferenceIcon } from '@/Components/EntityReferencePicker';

/**
 * ReferenceTag — A single attached-reference chip.
 *
 * Used both as an input-area chip (the "+ Add reference" picker) and as a
 * row inside a message body. Variants:
 *   - 'input'   : pink filled chip with an X to remove (used in the composer)
 *   - 'message' : subtle, theme-aware chip used inside sent message bodies
 *
 * @param {object} props
 * @param {{ id, label, type }} props.reference
 * @param {'input' | 'message'} [props.variant='input']
 * @param {(reference) => void} [props.onRemove]
 * @param {boolean} [props.isOwnMessage=false]  (message variant only)
 */
export default function ReferenceTag({ reference, variant = 'input', onRemove, isOwnMessage = false }) {
    if (!reference) return null;

    const icon = getReferenceIcon(reference.type);

    if (variant === 'message') {
        return (
            <span
                className={`nyl-ref-tag nyl-ref-tag--message ${isOwnMessage ? 'is-own' : 'is-peer'}`}
                title={formatReferenceType(reference.type)}
            >
                <i className={`fas ${icon}`} aria-hidden="true"></i>
                <span className="truncate">{reference.label}</span>
            </span>
        );
    }

    return (
        <span className="nyl-ref-tag nyl-ref-tag--input" title={formatReferenceType(reference.type)}>
            <i className={`fas ${icon} opacity-75`} aria-hidden="true"></i>
            <span className="truncate">{reference.label}</span>
            {onRemove && (
                <button
                    type="button"
                    onClick={() => onRemove(reference)}
                    aria-label={`Remove ${reference.label} reference`}
                    className="nyl-ref-tag-remove"
                >
                    <i className="fas fa-times" aria-hidden="true"></i>
                </button>
            )}
        </span>
    );
}
