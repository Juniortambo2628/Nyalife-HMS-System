import React from 'react';

export default function ViewToggle({ view, setView }) {
    return (
        <div className="btn-group rounded-pill p-1 border border-light-subtle" style={{ backgroundColor: 'rgba(0,0,0,0.03)' }}>
            <button 
                type="button"
                onClick={() => setView('grid')}
                className={`btn btn-sm rounded-pill px-3 shadow-none border-0 ${view === 'grid' ? 'btn-primary' : 'btn-light'}`}
                style={view !== 'grid' ? { backgroundColor: 'transparent' } : {}}
                title="Grid View"
            >
                <i className="fas fa-th-large"></i>
            </button>
            <button 
                type="button"
                onClick={() => setView('list')}
                className={`btn btn-sm rounded-pill px-3 shadow-none border-0 ${view === 'list' ? 'btn-primary' : 'btn-light'}`}
                style={view !== 'list' ? { backgroundColor: 'transparent' } : {}}
                title="List View"
            >
                <i className="fas fa-list"></i>
            </button>
        </div>
    );
}
