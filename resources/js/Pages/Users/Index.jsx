import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

import { useState, useEffect, useMemo } from 'react';
import DashboardSearch from '@/Components/DashboardSearch';
import DashboardTable from '@/Components/DashboardTable';
import ViewToggle from '@/Components/ViewToggle';
import UserAvatar from '@/Components/UserAvatar';
import DashboardSelect from '@/Components/DashboardSelect';
import TableActions from '@/Components/TableActions';
import UnifiedToolbar from '@/Components/UnifiedToolbar';

export default function Index({ users, filters, roles, auth }) {
    const [viewMode, setViewMode] = useState('list');
    const [search, setSearch] = useState(filters?.search || '');
    const [roleFilter, setRoleFilter] = useState(filters?.role || '');
    const [sortBy, setSortBy] = useState(typeof filters?.sort === 'string' ? filters.sort : 'created_at');
    const [direction, setDirection] = useState(filters?.direction || 'desc');
    const [selectedIds, setSelectedIds] = useState([]);

    useEffect(() => {
        const handleClear = () => setSelectedIds([]);
        window.addEventListener('toolbar-clear-selection', handleClear);
        return () => window.removeEventListener('toolbar-clear-selection', handleClear);
    }, []);

    const handleSearch = (searchValue, quickFilterValue = filters?.quick_filter) => {
        const query = { search: searchValue };
        if (roleFilter) query.role = roleFilter;
        if (sortBy) query.sort = sortBy;
        if (direction) query.direction = direction;
        if (quickFilterValue) query.quick_filter = quickFilterValue;

        router.get(route('users.index'), query, {
            preserveState: true,
            replace: true,
        });
    };

    const handleQuickFilterChange = (val) => {
        handleSearch(search, val);
    };

    // Update filters when roles or sorts change
    useEffect(() => {
        if (search === (filters?.search || '') && 
            roleFilter === (filters?.role || '') && 
            sortBy === (filters?.sort || 'created_at') && 
            direction === (filters?.direction || 'desc')) return;

        handleSearch(search);
    }, [roleFilter, sortBy, direction]);

    const columns = useMemo(() => [
        {
            header: 'User',
            accessorKey: 'first_name', // Sort key
            cell: info => (
                <div className="d-flex align-items-center">
                    <UserAvatar user={info.row.original} size="sm" className="me-3" showStatus={true} />
                    <div>
                        <div className="fw-bold text-gray-900">{info.row.original.first_name} {info.row.original.last_name}</div>
                        <div className="text-muted extra-small font-bold opacity-75">@{info.row.original.username}</div>
                    </div>
                </div>
            ),
            enableSorting: true
        },
        {
            header: 'Role',
            accessorKey: 'role',
            cell: info => (
                <span className="badge bg-soft-primary text-primary rounded-pill px-3 py-2 text-capitalize border border-primary-subtle fw-bold extra-small">
                    {info.row.original.role?.replace('_', ' ') || info.row.original.role_relation?.role_name}
                </span>
            ),
            enableSorting: false
        },
        {
            header: 'Email',
            accessorKey: 'email',
            cell: info => <span className="text-muted small fw-medium">{info.getValue()}</span>,
            enableSorting: true
        },
        {
            header: 'Status',
            accessorKey: 'is_active',
            cell: info => (
                <span className={`badge rounded-pill px-3 py-2 fw-bold border extra-small ${info.getValue() ? 'bg-success-subtle text-success border-success-subtle' : 'bg-secondary-subtle text-secondary border-secondary-subtle'}`}>
                    <i className={`fas fa-${info.getValue() ? 'check-circle' : 'times-circle'} me-1`}></i>
                    {info.getValue() ? 'Active' : 'Inactive'}
                </span>
            ),
            enableSorting: true
        },
        {
            header: 'Actions',
            id: 'actions',
            headerClassName: 'pe-5 text-end',
            cell: info => (
                <div className="pe-4">
                    <TableActions actions={[
                        { icon: 'fa-eye', label: 'View Profile', href: route('users.show', info.row.original.user_id) },
                        { icon: 'fa-edit', label: 'Edit Permissions', href: route('users.edit', info.row.original.user_id) },
                    ]} />
                </div>
            )
        }
    ], [sortBy, direction]);

    return (
        <AuthenticatedLayout 
            headerTitle="Staff & Access"
            breadcrumbs={[{ label: 'Users Registry', active: true }]}
        >
            <Head title="Users Registry" />

            <UnifiedToolbar 
                viewOptions={[
                    { label: 'LIST VIEW', icon: 'fa-list-ul', onClick: () => setViewMode('list'), color: viewMode === 'list' ? 'pink-500' : 'gray-400' },
                    { label: 'GRID VIEW', icon: 'fa-th-large', onClick: () => setViewMode('grid'), color: viewMode === 'grid' ? 'pink-500' : 'gray-400' }
                ]}
                filters={
                    <DashboardSelect 
                        options={roles.map(r => ({ label: r.role_name.replace('_', ' ').toUpperCase(), value: r.role_name }))}
                        value={roleFilter}
                        onChange={val => setRoleFilter(val || '')}
                        placeholder="Role..."
                        theme="dark"
                        dropup={true}
                    />
                }
                actions={[
                    { label: 'CREATE USER', icon: 'fa-user-plus', href: route('users.create') }
                ]}
                bulkActions={[
                    { label: 'ACTIVATE', icon: 'fa-check-circle', onClick: () => console.log('Activate', selectedIds) },
                    { label: 'DEACTIVATE', icon: 'fa-user-slash', onClick: () => console.log('Deactivate', selectedIds), color: 'warning' },
                    { label: 'DELETE', icon: 'fa-trash-alt', onClick: () => console.log('Delete', selectedIds), color: 'danger' }
                ]}
                selectionCount={selectedIds.length}
            />

            <div className="py-0">
                <DashboardSearch 
                    placeholder="Search by name, email, or username..." 
                    value={search}
                    onChange={setSearch}
                    onSubmit={handleSearch}
                    onFilterChange={handleQuickFilterChange}
                    filters={[
                        { label: 'Active', value: 'active' },
                        { label: 'Inactive', value: 'inactive' },
                        { label: 'Doctors', value: 'doctor' },
                        { label: 'Nurses', value: 'nurse' },
                        { label: 'Admins', value: 'admin' },
                    ]}
                />

                {viewMode === 'list' ? (
                    <DashboardTable 
                        data={users.data}
                        columns={columns}
                        pagination={users}
                        onSort={(columnId) => {
                            if (columnId === 'first_name' || columnId === 'email' || columnId === 'created_at' || columnId === 'last_name' || columnId === 'is_active') {
                                if (sortBy === columnId) {
                                    setDirection(direction === 'asc' ? 'desc' : 'asc');
                                } else {
                                    setSortBy(columnId);
                                    setDirection('asc');
                                }
                            }
                        }}
                        sortColumn={sortBy}
                        sortDirection={direction}
                        emptyMessage="No users found."
                        selectable={true}
                        selectedIds={selectedIds}
                        onSelectionChange={setSelectedIds}
                        idField="user_id"
                    />
                ) : (
                    <div className="row g-4">
                        {users.data.length > 0 ? (
                            <>
                                {users.data.map((user) => (
                                    <div key={user.user_id} className="col-md-4 col-lg-3">
                                        <div className="card shadow-sm border-0 rounded-2xl h-100 text-center p-4 hover-lift transition-all bg-white shadow-hover">
                                            <div className="mx-auto mb-4">
                                                <UserAvatar user={user} size="xl" showStatus={true} />
                                            </div>
                                            <h5 className="fw-bold text-gray-900 mb-1">{user.first_name} {user.last_name}</h5>
                                            <p className="text-muted extra-small font-bold uppercase tracking-widest opacity-50 mb-3">@{user.username}</p>
                                            <div className="mb-3">
                                                <span className="badge bg-soft-primary text-primary rounded-pill px-3 py-2 text-capitalize border border-primary-subtle fw-bold extra-small">
                                                    {user.role?.replace('_', ' ') || user.role_relation?.role_name}
                                                </span>
                                            </div>
                                            <div className="small text-muted mb-4 text-truncate px-2">{user.email}</div>
                                            <div className="mt-auto pt-2">
                                                <Link href={route('users.show', user.user_id)} className="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm w-100">View Profile</Link>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                                
                                {/* Pagination for Grid View */}
                                <div className="col-12 mt-4">
                                    <DashboardTable 
                                        data={[]} 
                                        columns={[]} 
                                        pagination={users}
                                        className="bg-transparent shadow-none"
                                    />
                                </div>
                            </>
                        ) : (
                            <div className="col-12 py-16 text-center bg-white rounded-3xl border border-dashed">
                                <i className="fas fa-users-slash text-gray-200 text-5xl mb-4"></i>
                                <h4 className="text-gray-400 fw-bold">No users found</h4>
                            </div>
                        )}
                    </div>
                )}
            </div>

        </AuthenticatedLayout>
    );
}
