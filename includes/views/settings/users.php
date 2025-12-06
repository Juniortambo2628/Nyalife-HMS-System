<?php
/**
 * Nyalife HMS - User Management Settings View
 *
 * User management settings page.
 */
$pageTitle = 'User Management Settings - Nyalife HMS';
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-users text-primary me-2"></i>
                    User Management Settings
                </h1>
                <a href="<?= $baseUrl ?>/settings" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Settings
                </a>
            </div>
            
            <!-- User Management Overview -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>User Management Overview</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <div class="text-center">
                                        <div class="h4 text-primary mb-1">Active Users</div>
                                        <div class="text-muted">Manage active user accounts</div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="text-center">
                                        <div class="h4 text-success mb-1">Role Management</div>
                                        <div class="text-muted">Configure user roles and permissions</div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="text-center">
                                        <div class="h4 text-info mb-1">Access Control</div>
                                        <div class="text-muted">Set access policies and restrictions</div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="text-center">
                                        <div class="h4 text-warning mb-1">Security</div>
                                        <div class="text-muted">Password policies and authentication</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Settings Categories -->
            <div class="row">
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-primary">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-user-shield fa-3x text-primary"></i>
                            </div>
                            <h5 class="card-title">Role Management</h5>
                            <p class="card-text">Configure user roles, permissions, and access levels.</p>
                            <div class="d-grid gap-2">
                                <a href="<?= $baseUrl ?>/users" class="btn btn-primary">
                                    <i class="fas fa-users me-2"></i>Manage Roles
                                </a>
                                <button class="btn btn-outline-primary" disabled>
                                    <i class="fas fa-cog me-2"></i>Permissions
                                </button>
                            </div>
                        </div>
                        <div class="card-footer text-muted">
                            <small>Roles: Admin, Doctor, Nurse, Staff</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-success">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-lock fa-3x text-success"></i>
                            </div>
                            <h5 class="card-title">Access Control</h5>
                            <p class="card-text">Set access policies, restrictions, and security measures.</p>
                            <div class="d-grid gap-2">
                                <button class="btn btn-success" disabled>
                                    <i class="fas fa-lock me-2"></i>Access Policies
                                </button>
                                <button class="btn btn-outline-success" disabled>
                                    <i class="fas fa-shield-alt me-2"></i>Security Rules
                                </button>
                            </div>
                        </div>
                        <div class="card-footer text-muted">
                            <small>Security Level: High</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-info">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-key fa-3x text-info"></i>
                            </div>
                            <h5 class="card-title">Authentication</h5>
                            <p class="card-text">Configure password policies and authentication methods.</p>
                            <div class="d-grid gap-2">
                                <button class="btn btn-info" disabled>
                                    <i class="fas fa-key me-2"></i>Password Policy
                                </button>
                                <button class="btn btn-outline-info" disabled>
                                    <i class="fas fa-fingerprint me-2"></i>2FA Settings
                                </button>
                            </div>
                        </div>
                        <div class="card-footer text-muted">
                            <small>2FA: Coming Soon</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-warning">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-user-clock fa-3x text-warning"></i>
                            </div>
                            <h5 class="card-title">Session Management</h5>
                            <p class="card-text">Manage user sessions, timeouts, and login history.</p>
                            <div class="d-grid gap-2">
                                <button class="btn btn-warning" disabled>
                                    <i class="fas fa-clock me-2"></i>Session Timeout
                                </button>
                                <button class="btn btn-outline-warning" disabled>
                                    <i class="fas fa-history me-2"></i>Login History
                                </button>
                            </div>
                        </div>
                        <div class="card-footer text-muted">
                            <small>Timeout: 30 minutes</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-secondary">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-user-plus fa-3x text-secondary"></i>
                            </div>
                            <h5 class="card-title">User Registration</h5>
                            <p class="card-text">Configure user registration and approval workflows.</p>
                            <div class="d-grid gap-2">
                                <button class="btn btn-secondary" disabled>
                                    <i class="fas fa-user-plus me-2"></i>Registration
                                </button>
                                <button class="btn btn-outline-secondary" disabled>
                                    <i class="fas fa-check-circle me-2"></i>Approval Workflow
                                </button>
                            </div>
                        </div>
                        <div class="card-footer text-muted">
                            <small>Auto-approval: Disabled</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-dark">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-archive fa-3x text-dark"></i>
                            </div>
                            <h5 class="card-title">User Archive</h5>
                            <p class="card-text">Manage inactive users and archival policies.</p>
                            <div class="d-grid gap-2">
                                <button class="btn btn-dark" disabled>
                                    <i class="fas fa-archive me-2"></i>Archive Users
                                </button>
                                <button class="btn btn-outline-dark" disabled>
                                    <i class="fas fa-trash me-2"></i>Cleanup Policy
                                </button>
                            </div>
                        </div>
                        <div class="card-footer text-muted">
                            <small>Archive after: 90 days</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <a href="<?= $baseUrl ?>/users" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-user-plus me-2"></i>Add New User
                                    </a>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <button class="btn btn-outline-success w-100" disabled>
                                        <i class="fas fa-users me-2"></i>Bulk Import
                                    </button>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <button class="btn btn-outline-warning w-100" disabled>
                                        <i class="fas fa-download me-2"></i>Export Users
                                    </button>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <button class="btn btn-outline-info w-100" disabled>
                                        <i class="fas fa-chart-bar me-2"></i>User Analytics
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
