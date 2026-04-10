import React from 'react';
import {
    useReactTable,
    getCoreRowModel,
    flexRender,
} from '@tanstack/react-table';
import { Link } from '@inertiajs/react';

export default function DashboardTable({ 
    data, 
    columns, 
    isLoading = false,
    emptyMessage = "No records found.",
    pagination = null,
    className = "",
    onSort = null,
    sortColumn = null,
    sortDirection = 'desc'
}) {
    const table = useReactTable({
        data,
        columns,
        getCoreRowModel: getCoreRowModel(),
    });

    return (
        <div className={`dashboard-table-wrapper ${className}`}>
            <div className="card shadow-sm border-0 rounded-2xl overflow-hidden bg-white">
                <div className="table-responsive custom-scrollbar">
                    <table className="table table-hover align-middle mb-0 border-0">
                        <thead>
                            {table.getHeaderGroups().map(headerGroup => (
                                <tr key={headerGroup.id} className="bg-primary-gradient border-0">
                                    {headerGroup.headers.map(header => {
                                        const isSortable = header.column.columnDef.enableSorting;
                                        return (
                                            <th 
                                                key={header.id} 
                                                className={`px-4 py-4 text-white text-uppercase small fw-bold tracking-wider border-0 ${isSortable ? 'cursor-pointer' : ''}`}
                                                style={{ minWidth: header.column.columnDef.minWidth || 'auto' }}
                                                onClick={isSortable && onSort ? () => onSort(header.column.id) : undefined}
                                            >
                                                <div className="d-flex align-items-center gap-2">
                                                    {header.isPlaceholder
                                                        ? null
                                                        : flexRender(
                                                            header.column.columnDef.header,
                                                            header.getContext()
                                                        )}
                                                    
                                                    {isSortable && onSort && (
                                                        <span className="sort-icon">
                                                            {sortColumn === header.column.id ? (
                                                                sortDirection === 'asc' ? <i className="fas fa-sort-up"></i> : <i className="fas fa-sort-down"></i>
                                                            ) : (
                                                                <i className="fas fa-sort text-white-50"></i>
                                                            )}
                                                        </span>
                                                    )}
                                                </div>
                                            </th>
                                        );
                                    })}
                                </tr>
                            ))}
                        </thead>
                        <tbody className="border-0">
                            {isLoading ? (
                                <tr>
                                    <td colSpan={columns.length} className="text-center py-5">
                                        <div className="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                                        <span className="text-muted font-medium">Loading data...</span>
                                    </td>
                                </tr>
                            ) : table.getRowModel().rows.length > 0 ? (
                                table.getRowModel().rows.map(row => (
                                    <tr key={row.id} className="border-bottom border-light hover-bg-light transition-all cursor-default">
                                        {row.getVisibleCells().map(cell => (
                                            <td key={cell.id} className="px-4 py-3.5 border-0">
                                                {flexRender(cell.column.columnDef.cell, cell.getContext())}
                                            </td>
                                        ))}
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan={columns.length} className="text-center py-5 text-muted border-0">
                                        <div className="d-flex flex-column align-items-center opacity-50">
                                            <i className="fas fa-folder-open mb-3 fs-1 text-gray-300"></i>
                                            <p className="mb-0 fw-medium">{emptyMessage}</p>
                                        </div>
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Optional Pagination Footer */}
                {pagination && pagination.links && pagination.links.length > 3 && (
                    <div className="card-footer bg-white border-0 px-4 py-3 d-flex justify-content-between align-items-center border-top border-light">
                        <div className="text-muted small">
                            Showing <span className="fw-bold text-gray-800">{pagination.from || 0}</span> to <span className="fw-bold text-gray-800">{pagination.to || 0}</span> of <span className="fw-bold text-gray-800">{pagination.total}</span> entries
                        </div>
                        <nav aria-label="Table navigation">
                            <ul className="pagination pagination-sm mb-0 gap-1">
                                {pagination.links.map((link, i) => (
                                    <li key={i} className={`page-item ${link.active ? 'active' : ''} ${!link.url ? 'disabled' : ''}`}>
                                        <Link
                                            href={link.url || '#'}
                                            className="page-link rounded-circle border-0 shadow-sm d-flex align-items-center justify-content-center"
                                            style={{ width: '32px', height: '32px' }}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                            preserveScroll
                                        />
                                    </li>
                                ))}
                            </ul>
                        </nav>
                    </div>
                )}
            </div>

            <style>{`
                .bg-primary-gradient {
                    background: linear-gradient(135deg, #e91e63 0%, #d81b60 100%) !important;
                }
                .hover-bg-light:hover {
                    background-color: #fffafb !important;
                }
                .custom-scrollbar::-webkit-scrollbar {
                    height: 8px;
                    width: 8px;
                }
                .custom-scrollbar::-webkit-scrollbar-track {
                    background: #f1f1f1;
                    border-radius: 10px;
                }
                .custom-scrollbar::-webkit-scrollbar-thumb {
                    background: #e91e6333;
                    border-radius: 10px;
                }
                .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                    background: #e91e6355;
                }
                .pagination .page-item.active .page-link {
                    background-color: #e91e63 !important;
                    border-color: #e91e63 !important;
                    color: white !important;
                }
                .pagination .page-link {
                    color: #e91e63;
                    background-color: #fff;
                    font-weight: 600;
                }
                .pagination .page-item.disabled .page-link {
                    color: #adb5bd;
                    background-color: #f8f9fa;
                }
                .table > :not(caption) > * > * {
                    padding: 1rem 0.75rem;
                }
                
                /* CRITICAL: Fix for dropdowns being hidden */
                .table-responsive {
                    overflow: visible !important;
                }
                .dashboard-table-wrapper .card {
                    overflow: visible !important;
                }
                .cursor-pointer {
                    cursor: pointer;
                }
                .sort-icon {
                    opacity: 0.5;
                    transition: opacity 0.2s;
                }
                th:hover .sort-icon {
                    opacity: 1;
                }
            `}</style>
        </div>
    );
}
