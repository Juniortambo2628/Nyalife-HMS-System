import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import PageHeader from '@/Components/PageHeader';
import { useState, useEffect, useRef } from 'react';
import TextInput from '@/Components/TextInput';
import DashboardSearch from '@/Components/DashboardSearch';

import DashboardTable from '@/Components/DashboardTable';


export default function Index({ users, filters, roles }) {
    const [viewMode, setViewMode] = useState('list');
    const [search, setSearch] = useState(filters?.search || '');
    const [roleFilter, setRoleFilter] = useState(filters?.role || '');
    const [sortBy, setSortBy] = useState(typeof filters?.sort === 'string' ? filters.sort : 'created_at');
    const [direction, setDirection] = useState(filters?.direction || 'desc');

    const handleSearch = (searchValue) => {
        const query = { search: searchValue };
        if (roleFilter) query.role = roleFilter;
        if (sortBy) query.sort = sortBy;
        if (direction) query.direction = direction;

        router.get(route('users.index'), query, {
            preserveState: true,
            replace: true,
        });
    };

    // Update filters when roles or sorts change
    useEffect(() => {
        if (search === filters?.search && 
            roleFilter === filters?.role && 
            sortBy === (filters?.sort || 'created_at') && 
            direction === (filters?.direction || 'desc')) return;

        handleSearch(search);
    }, [roleFilter, sortBy, direction]);

    return (
        <AuthenticatedLayout
            header="Users Management"
        >
            <Head title="Users" />

            <PageHeader 
                title="Staff & Users"
                breadcrumbs={[{ label: 'Users', active: true }]}
                actions={
                    <div className="d-flex flex-wrap gap-2 align-items-center">
                        <select 
                            value={roleFilter}
                            onChange={(e) => setRoleFilter(e.target.value)}
                            className="form-select bg-white border shadow-sm text-sm rounded-pill"
                            style={{width: 'auto'}}
                        >
                            <option value="">All Roles</option>
                            {roles.map(r => (
                                <option key={r.role_id} value={r.role_name}>{r.role_name.replace('_', ' ').charAt(0).toUpperCase() + r.role_name.slice(1)}</option>
                            ))}
                        </select>
                        
                        <div className="btn-group rounded-pill overflow-hidden shadow-sm ms-2">
                            <button 
                                onClick={() => setViewMode('list')}
                                className={`btn btn-sm ${viewMode === 'list' ? 'btn-primary' : 'btn-white'}`}
                            >
                                <i className="fas fa-list"></i>
                            </button>
                            <button 
                                onClick={() => setViewMode('grid')}
                                className={`btn btn-sm ${viewMode === 'grid' ? 'btn-primary' : 'btn-white'}`}
                            >
                                <i className="fas fa-th-large"></i>
                            </button>
                        </div>
                        <Link href={route('users.create')} className="btn btn-primary rounded-pill px-4 font-bold shadow-sm ms-2">
                            <i className="fas fa-user-plus me-2"></i>Create New User
                        </Link>
                    </div>
                }
            />

            <div className="py-0">
                <DashboardSearch 
                    placeholder="Search by name, email, or username..." 
                    value={search}
                    onChange={setSearch}
                    onSubmit={handleSearch}
                />

                {viewMode === 'list' ? (
                    <DashboardTable 
                        data={users.data}
                        columns={[
                            {
                                header: 'User',
                                accessorKey: 'first_name', // Sort key
                                cell: info => (
                                    <div className="d-flex align-items-center">
                                        <div className="avatar-circle-sm bg-pink-100 text-pink-600 rounded-circle d-flex align-items-center justify-content-center me-3" style={{ width: '40px', height: '40px' }}>
                                            {info.row.original.first_name?.charAt(0)}
                                        </div>
                                        <div>
                                            <div className="fw-bold text-gray-900">{info.row.original.first_name} {info.row.original.last_name}</div>
                                            <div className="text-muted small">@{info.row.original.username}</div>
                                        </div>
                                    </div>
                                ),
                                enableSorting: true
                            },
                            {
                                header: 'Role',
                                accessorKey: 'role',
                                cell: info => (
                                    <span className="badge bg-soft-primary text-primary rounded-pill px-3 text-capitalize">
                                        {info.row.original.role?.replace('_', ' ') || info.row.original.role_relation?.role_name}
                                    </span>
                                ),
                                enableSorting: false
                            },
                            {
                                header: 'Email',
                                accessorKey: 'email',
                                cell: info => <span className="text-muted small">{info.getValue()}</span>,
                                enableSorting: true
                            },
                            {
                                header: 'Status',
                                accessorKey: 'is_active',
                                cell: info => (
                                    <span className={`badge rounded-pill px-2 py-1 ${info.getValue() ? 'bg-success' : 'bg-secondary'}`}>
                                        {info.getValue() ? 'Active' : 'Inactive'}
                                    </span>
                                ),
                                enableSorting: true
                            },
                            {
                                header: 'Actions',
                                id: 'actions',
                                cell: info => (
                                    <div className="text-end">
                                        <Link href={route('users.show', info.row.original.user_id)} className="btn btn-sm btn-outline-secondary rounded-circle p-2" style={{ width: '32px', height: '32px' }}>
                                            <i className="fas fa-eye"></i>
                                        </Link>
                                    </div>
                                )
                            }
                        ]}
                        pagination={users}
                        onSort={(columnId) => {
                            if (columnId === 'first_name' || columnId === 'email' || columnId === 'created_at' || columnId === 'last_name') {
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
                    />
                ) : (
                    <div className="row g-4">
                        {users.data.map((user) => (
                            <div key={user.user_id} className="col-md-4 col-lg-3">
                                <div className="card shadow-sm border-0 rounded-xl h-100 text-center p-4 hover-lift translate-y-n2">
                                    <div className="mx-auto mb-3">
                                        <div className="avatar-lg bg-pink-100 text-pink-600 rounded-circle d-flex align-items-center justify-content-center" style={{ width: '80px', height: '80px', fontSize: '1.5rem' }}>
                                            {user.first_name?.charAt(0)}
                                        </div>
                                    </div>
                                    <h5 className="fw-bold mb-1">{user.first_name} {user.last_name}</h5>
                                    <p className="text-muted small mb-2">@{user.username}</p>
                                    <div className="mb-3">
                                        <span className="badge bg-soft-primary text-primary rounded-pill px-3 text-capitalize">
                                            {user.role?.replace('_', ' ') || user.role_relation?.role_name}
                                        </span>
                                    </div>
                                    <div className="small text-muted mb-4 truncate">{user.email}</div>
                                    <div className="mt-auto d-flex justify-content-center gap-2">
                                        <Link href={route('users.show', user.user_id)} className="btn btn-outline-primary btn-sm rounded-pill px-4">View Profile</Link>
                                    </div>
                                </div>
                            </div>
                        ))}
                    </div>
                )}

                {/* Pagination */}
                <div className="mt-4 d-flex justify-content-center">
                    {users.links.map((link, i) => (
                        <Link
                            key={i}
                            href={link.url || '#'}
                            className={`btn btn-sm mx-1 rounded-pill px-3 ${link.active ? 'btn-primary' : 'btn-white border'}`}
                            dangerouslySetInnerHTML={{ __html: link.label }}
                        />
                    ))}
                </div>
            </div>

            <style>{`
                .avatar-lg { font-size: 2rem; }
                .hover-lift:hover { transform: translateY(-5px); transition: all 0.3s ease; }
            `}</style>
        </AuthenticatedLayout>
    );
}
