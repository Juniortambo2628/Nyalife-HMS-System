import React from 'react';

const StatusBadge = ({ status, className = "" }) => {
    if (!status) return null;

    const s = status.toLowerCase();
    
    const config = {
        // Appointments & Consultations
        completed: { bg: 'bg-success-subtle', text: 'text-success', icon: 'fa-check-circle', label: 'Completed' },
        confirmed: { bg: 'bg-info-subtle', text: 'text-info', icon: 'fa-calendar-check', label: 'Confirmed' },
        scheduled: { bg: 'bg-primary-subtle', text: 'text-primary', icon: 'fa-calendar-alt', label: 'Scheduled' },
        in_progress: { bg: 'bg-primary-subtle', text: 'text-primary', icon: 'fa-spinner fa-spin', label: 'In Progress' },
        pending: { bg: 'bg-warning-subtle', text: 'text-warning-emphasis', icon: 'fa-clock', label: 'Pending' },
        cancelled: { bg: 'bg-danger-subtle', text: 'text-danger', icon: 'fa-times-circle', label: 'Cancelled' },
        no_show: { bg: 'bg-secondary-subtle', text: 'text-secondary', icon: 'fa-user-slash', label: 'No Show' },
        
        // Billing
        paid: { bg: 'bg-success-subtle', text: 'text-success', icon: 'fa-money-bill-wave', label: 'Paid' },
        unpaid: { bg: 'bg-danger-subtle', text: 'text-danger', icon: 'fa-exclamation-circle', label: 'Unpaid' },
        overdue: { bg: 'bg-danger', text: 'text-white', icon: 'fa-history', label: 'Overdue' },
        partially_paid: { bg: 'bg-info-subtle', text: 'text-info', icon: 'fa-file-invoice-dollar', label: 'Partial' },
        
        // Labs
        processing: { bg: 'bg-info-subtle', text: 'text-info', icon: 'fa-flask fa-spin', label: 'Processing' },
        awaiting_sample: { bg: 'bg-warning-subtle', text: 'text-warning-emphasis', icon: 'fa-vial', label: 'Awaiting Sample' },
        dispensed: { bg: 'bg-success-subtle', text: 'text-success', icon: 'fa-prescription-bottle', label: 'Dispensed' },
        
        // Generic
        active: { bg: 'bg-success-subtle', text: 'text-success', icon: 'fa-toggle-on', label: 'Active' },
        inactive: { bg: 'bg-secondary-subtle', text: 'text-secondary', icon: 'fa-toggle-off', label: 'Inactive' },
    };

    const { bg, text, icon, label } = config[s] || { 
        bg: 'bg-light', 
        text: 'text-dark', 
        icon: 'fa-info-circle', 
        label: status.replace('_', ' ').toUpperCase() 
    };

    return (
        <span className={`badge ${bg} ${text} rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-1 border-0 animate-in fade-in duration-300 ${className}`}>
            <i className={`fas ${icon} text-xs`}></i>
            <span style={{ fontSize: '0.75rem' }}>{label.toUpperCase()}</span>
        </span>
    );
};

export default StatusBadge;
