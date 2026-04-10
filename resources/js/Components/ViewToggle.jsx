import React from 'react';

export default function ViewToggle({ view, setView }) {
    return (
        <div className="btn-group bg-white rounded-pill shadow-sm p-1">
            <button 
                type="button"
                onClick={() => setView('grid')}
                className={`btn btn-sm rounded-pill px-3 ${view === 'grid' ? 'btn-primary' : 'btn-light border-0'}`}
                title="Grid View"
            >
                <i className="fas fa-th-large"></i>
            </button>
            <button 
                type="button"
                onClick={() => setView('list')}
                className={`btn btn-sm rounded-pill px-3 ${view === 'list' ? 'btn-primary' : 'btn-light border-0'}`}
                title="List View"
            >
                <i className="fas fa-list"></i>
            </button>
        </div>
    );
}
