<?php
/**
 * Nyalife HMS - Security Settings View
 */
$pageTitle = 'Security Settings - Nyalife HMS';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-shield-alt text-warning me-2"></i>
                    Security Settings
                </h1>
                <a href="<?= $baseUrl ?>/settings" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Settings
                </a>
            </div>

            <!-- Password Policy -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-key me-2"></i>Password Policy</h5>
                </div>
                <div class="card-body">
                    <form id="passwordPolicyForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="minPasswordLength" class="form-label">Minimum Password Length</label>
                                <input type="number" class="form-control" id="minPasswordLength" value="8" min="6" max="32">
                            </div>
                            <div class="col-md-6">
                                <label for="passwordExpiry" class="form-label">Password Expiry (days)</label>
                                <input type="number" class="form-control" id="passwordExpiry" value="90">
                                <small class="text-muted">Set to 0 for no expiry</small>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="requireUppercase" checked>
                                    <label class="form-check-label" for="requireUppercase">
                                        Require at least one uppercase letter
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="requireLowercase" checked>
                                    <label class="form-check-label" for="requireLowercase">
                                        Require at least one lowercase letter
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="requireNumber" checked>
                                    <label class="form-check-label" for="requireNumber">
                                        Require at least one number
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="requireSpecialChar" checked>
                                    <label class="form-check-label" for="requireSpecialChar">
                                        Require at least one special character
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Password Policy
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Session Management -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Session Management</h5>
                </div>
                <div class="card-body">
                    <form id="sessionSettingsForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="sessionTimeout" class="form-label">Session Timeout (minutes)</label>
                                <input type="number" class="form-control" id="sessionTimeout" value="30">
                            </div>
                            <div class="col-md-6">
                                <label for="maxConcurrentSessions" class="form-label">Max Concurrent Sessions per User</label>
                                <input type="number" class="form-control" id="maxConcurrentSessions" value="3">
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="rememberMeEnabled" checked>
                                    <label class="form-check-label" for="rememberMeEnabled">
                                        Allow "Remember Me" feature
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="rememberMeDuration" class="form-label">Remember Me Duration (days)</label>
                                <input type="number" class="form-control" id="rememberMeDuration" value="30">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Session Settings
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Login Security -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-lock me-2"></i>Login Security</h5>
                </div>
                <div class="card-body">
                    <form id="loginSecurityForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="maxLoginAttempts" class="form-label">Maximum Login Attempts</label>
                                <input type="number" class="form-control" id="maxLoginAttempts" value="5">
                            </div>
                            <div class="col-md-6">
                                <label for="lockoutDuration" class="form-label">Lockout Duration (minutes)</label>
                                <input type="number" class="form-control" id="lockoutDuration" value="15">
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="require2FA">
                                    <label class="form-check-label" for="require2FA">
                                        Require Two-Factor Authentication (2FA) for all users
                                        <span class="badge bg-info">Coming Soon</span>
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="emailLoginNotification" checked>
                                    <label class="form-check-label" for="emailLoginNotification">
                                        Send email notification on new login
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Login Security Settings
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Audit Log -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Recent Security Events</h5>
                    <button class="btn btn-sm btn-secondary">
                        <i class="fas fa-download me-2"></i>Export Audit Log
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Event Type</th>
                                    <th>User</th>
                                    <th>IP Address</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>2025-12-02  14:30:15</td>
                                    <td><span class="badge bg-success">Login Success</span></td>
                                    <td>admin@nyalife.com</td>
                                    <td>192.168.1.100</td>
                                    <td>Successful login from Chrome browser</td>
                                </tr>
                                <tr>
                                    <td>2025-12-02 13:45:22</td>
                                    <td><span class="badge bg-warning">Password Change</span></td>
                                    <td>doctor1@nyalife.com</td>
                                    <td>192.168.1.105</td>
                                    <td>Password changed successfully</td>
                                </tr>
                                <tr>
                                    <td>2025-12-02 12:20:10</td>
                                    <td><span class="badge bg-danger">Failed Login</span></td>
                                    <td>unknown@test.com</td>
                                    <td>203.0.113.42</td>
                                    <td>Invalid credentials</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form submission handlers
    document.getElementById('passwordPolicyForm').addEventListener('submit', function(e) {
        e.preventDefault();
        alert('Password policy settings saved! (Demo - not yet connected to backend)');
    });
    
    document.getElementById('sessionSettingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        alert('Session settings saved! (Demo - not yet connected to backend)');
    });
    
    document.getElementById('loginSecurityForm').addEventListener('submit', function(e) {
        e.preventDefault();
        alert('Login security settings saved! (Demo - not yet connected to backend)');
    });
});
</script>
