import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

import { useState, useEffect, useMemo } from 'react';
import DashboardSearch from '@/Components/DashboardSearch';
import DashboardTable from '@/Components/DashboardTable';
import RegistryTablePanel from '@/Components/RegistryTablePanel';
import ViewToggle from '@/Components/ViewToggle';
import UserAvatar from '@/Components/UserAvatar';
import TableActions from '@/Components/TableActions';
import StatusBadge from '@/Components/StatusBadge';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import { TableCellPrimary, TableCellStack } from '@/Components/TableCells';
import StatCardGrid from '@/Components/StatCardGrid';

export default function Index({ users, filters, roles, auth, stats }) {
    const [viewMode, setViewMode] = useState('list');
    const [search, setSearch] = useState(filters?.search || '');
    const [roleFilter, setRoleFilter] = useState(filters?.role || '');
    const [sortBy, setSortBy] = useState(typeof filters?.sort === 'string' ? filters.sort : 'created_at');
    const [direction, setDirection] = useState(filters?.direction || 'desc');
    const [selectedIds, setSelectedIds] = useState([]);

    const statItems = useMemo(() => [
        {
            label: 'Total Users',
            value: stats?.total ?? 0,
            icon: 'fa-users',
            color: 'primary',
        },
        {
            label: 'Active Staff',
            value: stats?.active ?? 0,
            icon: 'fa-user-check',
            color: 'success',
        },
        {
            label: 'Inactive Staff',
            value: stats?.inactive ?? 0,
            icon: 'fa-user-slash',
            color: 'danger',
        },
    ], [stats]);

    useEffect(() => {
        const handleClear = () => setSelectedIds([]);
        window.addEventListener('toolbar-clear-selection', handleClear);
        return () => window.removeEventListener('toolbar-clear-selection', handleClear);
    }, []);

    const handleBulkAction = (action) => {
        router.post(route('users.bulk-action'), {
            action: action,
            ids: selectedIds
        }, {
            onSuccess: () => setSelectedIds([]),
        });
    };

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
                <TableCellStack
                    primary={`${info.row.original.first_name} ${info.row.original.last_name}`}
                    secondary={`@${info.row.original.username}`}
                />
            ),
            enableSorting: true
        },
        {
            header: 'Role',
            accessorKey: 'role',
            cell: info => (
                <TableCellPrimary className="text-uppercase">
                    {info.row.original.role?.replace('_', ' ') || info.row.original.role_relation?.role_name}
                </TableCellPrimary>
            ),
            enableSorting: false
        },
        {
            header: 'Email',
            accessorKey: 'email',
            cell: info => <TableCellPrimary className="text-muted">{info.getValue()}</TableCellPrimary>,
            enableSorting: true
        },
        {
            header: 'Status',
            accessorKey: 'is_active',
            cell: info => <StatusBadge status={info.getValue() ? 'active' : 'inactive'} />,
            enableSorting: true
        },
        {
            header: 'Actions',
            id: 'actions',
            headerClassName: 'pe-5 text-end',
            cell: info => (
                <TableActions actions={[
                    { icon: 'fa-eye', label: 'View profile', href: route('users.show', info.row.original.user_id) },
                    { icon: 'fa-edit', label: 'Edit permissions', href: route('users.edit', info.row.original.user_id) },
                ]} />
            )
        }
    ], [sortBy, direction]);

    return (
        <AuthenticatedLayout 
            headerTitle="Staff & Access"
            breadcrumbs={[{ label: 'Users Registry', active: true }]}
        >
            <Head title="Users Registry" />

            <StatCardGrid items={statItems} cols={3} />

            <UnifiedToolbar 
                viewMode={viewMode}
                onViewModeChange={setViewMode}
                filterGroups={[
                    {
                        id: 'role',
                        label: 'Role',
                        emptyLabel: 'All roles',
                        value: roleFilter,
                        onChange: (val) => setRoleFilter(val || ''),
                        options: roles.map((r) => ({
                            label: r.role_name.replace('_', ' ').toUpperCase(),
                            value: r.role_name,
                        })),
                    },
                ]}
                actions={[
                    { label: 'CREATE USER', icon: 'fa-user-plus', href: route('users.create') }
                ]}
                bulkActions={[
                    { label: 'ACTIVATE SELECTED', icon: 'fa-check-circle', onClick: () => handleBulkAction('activate') },
                    { label: 'DEACTIVATE SELECTED', icon: 'fa-user-slash', onClick: () => handleBulkAction('deactivate'), color: 'warning' },
                    { label: 'DELETE SELECTED', icon: 'fa-trash-alt', onClick: () => handleBulkAction('delete'), color: 'danger' }
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
                    <RegistryTablePanel
                        title="Staff registry"
                        icon="fa-user-md"
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
