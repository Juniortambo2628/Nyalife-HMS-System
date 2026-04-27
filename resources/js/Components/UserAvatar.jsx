import React from 'react';

export default function UserAvatar({ 
    user, 
    size = 'md', 
    className = "", 
    style = {},
    showStatus = false
}) {
    if (!user) return null;

    const sizeClassMap = {
        'xs': 'avatar-xs',
        'sm': 'avatar-sm',
        'md': 'avatar-md',
        'lg': 'avatar-lg',
        'xl': 'avatar-xl',
        '2xl': 'avatar-xxl'
    };

    const sizeClass = sizeClassMap[size] || 'avatar-md';
    const initial = (user.first_name || user.name || user.username || 'U').charAt(0).toUpperCase();
    
    // Generate a deterministic color based on the user's name
    const colors = [
        'bg-gradient-pink',
        'bg-gradient-blue',
        'bg-gradient-green',
        'bg-gradient-amber',
        'bg-gradient-indigo',
        'bg-gradient-purple',
        'bg-gradient-rose',
    ];
    
    const colorIndex = (user.user_id || initial.charCodeAt(0)) % colors.length;
    // Fallback if gradients aren't defined yet, but I'll add them to nyalife-components.css
    const backgroundClass = colors[colorIndex];

    return (
        <div 
            className={`user-avatar-container position-relative d-inline-block ${sizeClass} ${className}`}
            style={style}
        >
            <div 
                className={`user-avatar-circle d-flex align-items-center justify-content-center text-white fw-extrabold shadow-sm rounded-circle w-100 h-100 ${backgroundClass}`}
                style={{
                    userSelect: 'none',
                    fontSize: 'inherit' // Inherit from parent size class if possible, or defined in CSS
                }}
            >
                {initial}
            </div>
            
            {showStatus && (
                <div 
                    className="avatar-status-indicator position-absolute bottom-0 end-0 rounded-circle border-2 border-white shadow-sm"
                    style={{
                        backgroundColor: user.is_active || user.status === 'active' ? '#10b981' : '#9ca3af'
                    }}
                ></div>
            )}
        </div>
    );
}
