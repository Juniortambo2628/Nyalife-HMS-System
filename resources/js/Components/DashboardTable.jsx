import React from 'react';
import {
    useReactTable,
    getCoreRowModel,
    flexRender,
} from '@tanstack/react-table';
import Pagination from '@/Components/Pagination';

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
                                                className={`px-4 py-4 text-white text-uppercase small fw-bold tracking-wider border-0 text-start ${isSortable ? 'cursor-pointer' : ''}`}
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
                                        <span className="text-muted fw-medium">Loading data...</span>
                                    </td>
                                </tr>
                            ) : table.getRowModel().rows.length > 0 ? (
                                table.getRowModel().rows.map(row => (
                                    <tr key={row.id} className="border-bottom border-light hover-bg-light transition-all cursor-default">
                                        {row.getVisibleCells().map(cell => (
                                            <td key={cell.id} className="px-4 py-3 border-0 text-start">
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

                {/* Unified Pagination Footer */}
                {pagination && pagination.links && pagination.links.length > 3 && (
                    <div className="card-footer bg-white border-0 px-4 py-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 border-top border-light">
                        <div className="text-muted small fw-medium">
                            Showing <span className="fw-bold text-gray-900">{pagination.from || 0}</span> to <span className="fw-bold text-gray-900">{pagination.to || 0}</span> of <span className="fw-bold text-gray-900">{pagination.total}</span> entries
                        </div>
                        <Pagination links={pagination.links} />
                    </div>
                )}
            </div>
        </div>
    );
}
