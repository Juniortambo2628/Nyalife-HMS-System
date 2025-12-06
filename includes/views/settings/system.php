<?php
/**
 * Nyalife HMS - System Configuration Settings View
 */
$pageTitle = 'System Configuration - Nyalife HMS';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fas fa-cogs text-success me-2"></i>
                    System Configuration
                </h1>
                <a href="<?= $baseUrl ?>/settings" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Settings
                </a>
            </div>

            <!-- General Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-sliders-h me-2"></i>General Settings</h5>
                </div>
                <div class="card-body">
                    <form id="generalSettingsForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="systemName" class="form-label">System Name</label>
                                <input type="text" class="form-control" id="systemName" value="Nyalife HMS">
                            </div>
                            <div class="col-md-6">
                                <label for="systemEmail" class="form-label">System Email</label>
                                <input type="email" class="form-control" id="systemEmail" value="admin@nyalife-hms.com">
                            </div>
                            <div class="col-md-6">
                                <label for="timezone" class="form-label">Timezone</label>
                                <select class="form-select" id="timezone">
                                    <option value="Africa/Nairobi" selected>Africa/Nairobi (EAT)</option>
                                    <option value="UTC">UTC</option>
                                    <option value="America/New_York">America/New_York (EST)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="dateFormat" class="form-label">Date Format</label>
                                <select class="form-select" id="dateFormat">
                                    <option value="Y-m-d" selected>YYYY-MM-DD</option>
                                    <option value="d/m/Y">DD/MM/YYYY</option>
                                    <option value="m/d/Y">MM/DD/YYYY</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="timeFormat" class="form-label">Time Format</label>
                                <select class="form-select" id="timeFormat">
                                    <option value="24" selected>24 Hour</option>
                                    <option value="12">12 Hour</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="language" class="form-label">Default Language</label>
                                <select class="form-select" id="language">
                                    <option value="en" selected>English</option>
                                    <option value="sw">Swahili</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save General Settings
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Appointment Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Appointment Settings</h5>
                </div>
                <div class="card-body">
                    <form id="appointmentSettingsForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="appointmentDuration" class="form-label">Default Appointment Duration (minutes)</label>
                                <input type="number" class="form-control" id="appointmentDuration" value="30">
                            </div>
                            <div class="col-md-6">
                                <label for="appointmentBuffer" class="form-label">Buffer Time Between Appointments (minutes)</label>
                                <input type="number" class="form-control" id="appointmentBuffer" value="15">
                            </div>
                            <div class="col-md-6">
                                <label for="workingHoursStart" class="form-label">Working Hours Start</label>
                                <input type="time" class="form-control" id="workingHoursStart" value="08:00">
                            </div>
                            <div class="col-md-6">
                                <label for="workingHoursEnd" class="form-label">Working Hours End</label>
                                <input type="time" class="form-control" id="workingHoursEnd" value="17:00">
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="allowOnlineBooking" checked>
                                    <label class="form-check-label" for="allowOnlineBooking">
                                        Allow Online Appointment Booking
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Appointment Settings
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Email Notification Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Email Notification Settings</h5>
                </div>
                <div class="card-body">
                    <form id="emailSettingsForm">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="emailAppointmentConfirmation" checked>
                                    <label class="form-check-label" for="emailAppointmentConfirmation">
                                        Send appointment confirmation emails
                                    </label>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="emailAppointmentReminder" checked>
                                    <label class="form-check-label" for="emailAppointmentReminder">
                                        Send appointment reminder emails
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="reminderTime" class="form-label">Send Reminder (hours before appointment)</label>
                                <input type="number" class="form-control" id="reminderTime" value="24">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Email Settings
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form submission handlers
    document.getElementById('generalSettingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        alert('General settings saved! (Demo - not yet connected to backend)');
    });
    
    document.getElementById('appointmentSettingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        alert('Appointment settings saved! (Demo - not yet connected to backend)');
    });
    
    document.getElementById('emailSettingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        alert('Email settings saved! (Demo - not yet connected to backend)');
    });
});
</script>
