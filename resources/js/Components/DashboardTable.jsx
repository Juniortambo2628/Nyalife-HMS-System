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
    sortDirection = 'desc',
    selectable = false,
    selectedIds = [],
    onSelectionChange = null,
    idField = 'id'
}) {
    const finalColumns = React.useMemo(() => {
        if (!selectable) return columns;

        const selectionColumn = {
            id: 'selection',
            header: () => (
                <div className="form-check ms-1 d-flex justify-content-center align-items-center m-0 p-0">
                    <input 
                        type="checkbox" 
                        className="form-check-input shadow-none cursor-pointer nyl-checkbox m-0" 
                        onChange={(e) => {
                            if (e.target.checked && onSelectionChange) {
                                onSelectionChange(data.map(item => item[idField]));
                            } else if (onSelectionChange) {
                                onSelectionChange([]);
                            }
                        }}
                        checked={data.length > 0 && selectedIds.length === data.length}
                    />
                </div>
            ),
            cell: ({ row }) => (
                <div className="form-check ms-1 d-flex justify-content-center align-items-center m-0 p-0">
                    <input 
                        type="checkbox" 
                        className="form-check-input shadow-none cursor-pointer nyl-checkbox m-0" 
                        checked={selectedIds.includes(row.original[idField])}
                        onChange={() => {
                            if (!onSelectionChange) return;
                            const id = row.original[idField];
                            if (selectedIds.includes(id)) {
                                onSelectionChange(selectedIds.filter(i => i !== id));
                            } else {
                                onSelectionChange([...selectedIds, id]);
                            }
                        }}
                    />
                </div>
            )
        };

        return [selectionColumn, ...columns];
    }, [columns, selectable, selectedIds, data, idField, onSelectionChange]);

    const table = useReactTable({
        data,
        columns: finalColumns,
        getCoreRowModel: getCoreRowModel(),
    });

    return (
        <div className={`dashboard-table-wrapper ${className}`}>
            <div className="card shadow-sm border-0 rounded-2xl overflow-hidden bg-white">
                <div className="table-responsive custom-scrollbar">
                    <table className="table table-hover align-middle mb-0 border-0">
                        <thead>
                            {table.getHeaderGroups().map(headerGroup => (
                                <tr key={headerGroup.id} className="bg-gray-50 border-0 border-bottom">
                                    {headerGroup.headers.map(header => {
                                        const isSortable = header.column.columnDef.enableSorting;
                                        return (
                                            <th 
                                                key={header.id} 
                                                className={`px-4 py-4 text-white text-uppercase extra-small fw-extrabold tracking-widest border-0 ${header.id === 'selection' ? 'text-center' : 'text-start'} ${isSortable ? 'cursor-pointer' : ''}`}
                                                style={{ whiteSpace: 'nowrap' }}
                                                onClick={isSortable && onSort ? () => onSort(header.column.id) : undefined}
                                            >
                                                <div className={`d-flex align-items-center gap-2 ${header.id === 'selection' ? 'justify-content-center' : ''}`}>
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
                <i className={`fas fa-sort text-white opacity-30`}></i>
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
                                            <td key={cell.id} className={`px-4 py-3 border-0 ${cell.column.id === 'selection' ? 'text-center' : 'text-start'} align-middle`}>
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
