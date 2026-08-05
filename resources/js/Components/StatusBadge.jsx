import React from 'react';

const StatusBadge = ({ status, className = '' }) => {
    if (!status) return null;

    const s = status.toLowerCase();

    const config = {
        // Appointments & Consultations
        completed: { bg: 'bg-success-subtle', text: 'text-success', icon: 'fa-check-circle', label: 'Completed' },
        confirmed: { bg: 'bg-info-subtle', text: 'text-info', icon: 'fa-calendar-check', label: 'Confirmed' },
        arrived: { bg: 'bg-success-subtle', text: 'text-success', icon: 'fa-door-open', label: 'Arrived' },
        vitals_recorded: {
            bg: 'bg-success-subtle',
            text: 'text-success',
            icon: 'fa-heartbeat',
            label: 'Vitals Recorded',
        },
        scheduled: { bg: 'bg-primary-subtle', text: 'text-primary', icon: 'fa-calendar-alt', label: 'Scheduled' },
        in_progress: {
            bg: 'bg-primary-subtle',
            text: 'text-primary',
            icon: 'fa-spinner fa-spin',
            label: 'In Progress',
        },
        pending: { bg: 'bg-warning-subtle', text: 'text-warning-emphasis', icon: 'fa-clock', label: 'Pending' },
        cancelled: { bg: 'bg-danger-subtle', text: 'text-danger', icon: 'fa-times-circle', label: 'Cancelled' },
        no_show: { bg: 'bg-secondary-subtle', text: 'text-secondary', icon: 'fa-user-slash', label: 'No Show' },

        // Billing
        paid: { bg: 'bg-success-subtle', text: 'text-success', icon: 'fa-money-bill-wave', label: 'Paid' },
        unpaid: { bg: 'bg-danger-subtle', text: 'text-danger', icon: 'fa-exclamation-circle', label: 'Unpaid' },
        overdue: { bg: 'bg-danger', text: 'text-white', icon: 'fa-history', label: 'Overdue' },
        partially_paid: { bg: 'bg-info-subtle', text: 'text-info', icon: 'fa-file-invoice-dollar', label: 'Partial' },
        failed: { bg: 'bg-danger-subtle', text: 'text-danger', icon: 'fa-times-circle', label: 'Failed' },
        refunded: { bg: 'bg-secondary-subtle', text: 'text-secondary', icon: 'fa-undo', label: 'Refunded' },

        // Labs & priorities
        processing: { bg: 'bg-info-subtle', text: 'text-info', icon: 'fa-flask fa-spin', label: 'Processing' },
        awaiting_sample: {
            bg: 'bg-warning-subtle',
            text: 'text-warning-emphasis',
            icon: 'fa-vial',
            label: 'Awaiting Sample',
        },
        dispensed: {
            bg: 'bg-success-subtle',
            text: 'text-success',
            icon: 'fa-prescription-bottle',
            label: 'Dispensed',
        },
        pending_verification: {
            bg: 'bg-info-subtle',
            text: 'text-info',
            icon: 'fa-history',
            label: 'Pending Verification',
        },
        verified: { bg: 'bg-success-subtle', text: 'text-success', icon: 'fa-check-double', label: 'Verified' },

        // Priority levels
        urgent: { bg: 'bg-danger-subtle', text: 'text-danger', icon: 'fa-bolt', label: 'Urgent' },
        emergency: { bg: 'bg-danger', text: 'text-white', icon: 'fa-bolt', label: 'Emergency' },
        stat: { bg: 'bg-dark', text: 'text-white', icon: 'fa-bolt', label: 'Stat' },
        high: { bg: 'bg-warning-subtle', text: 'text-warning-emphasis', icon: 'fa-bolt', label: 'High' },
        routine: { bg: 'bg-info-subtle', text: 'text-info', icon: 'fa-bolt', label: 'Routine' },
        normal: { bg: 'bg-info-subtle', text: 'text-info', icon: 'fa-bolt', label: 'Normal' },

        // Generic
        active: { bg: 'bg-success-subtle', text: 'text-success', icon: 'fa-toggle-on', label: 'Active' },
        inactive: { bg: 'bg-secondary-subtle', text: 'text-secondary', icon: 'fa-toggle-off', label: 'Inactive' },
        in_stock: { bg: 'bg-success-subtle', text: 'text-success', icon: 'fa-box', label: 'In Stock' },
        low_stock: { bg: 'bg-danger-subtle', text: 'text-danger', icon: 'fa-exclamation-triangle', label: 'Low Stock' },

        // Contact Messages
        replied: { bg: 'bg-success-subtle', text: 'text-success', icon: 'fa-reply', label: 'Replied' },
        read: { bg: 'bg-light text-muted border', text: 'text-muted', icon: 'fa-envelope-open', label: 'Read' },

        // Purchase orders & blog
        ordered: { bg: 'bg-info-subtle', text: 'text-info', icon: 'fa-truck', label: 'Ordered' },
        received: { bg: 'bg-success-subtle', text: 'text-success', icon: 'fa-box-open', label: 'Received' },
        draft: { bg: 'bg-secondary-subtle', text: 'text-secondary', icon: 'fa-clock', label: 'Draft' },
        published: { bg: 'bg-success-subtle', text: 'text-success', icon: 'fa-check-circle', label: 'Published' },
    };

    const { bg, text, icon, label } = config[s] || {
        bg: 'bg-light',
        text: 'text-dark',
        icon: 'fa-info-circle',
        label: status.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()),
    };

    return (
        <span className={`badge nyl-status-badge ${bg} ${text} ${className}`.trim()}>
            <i className={`fas ${icon} nyl-status-badge-icon`}></i>
            <span>{label}</span>
        </span>
    );
};

export default StatusBadge;
