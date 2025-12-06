<?php
/**
 * Nyalife HMS - Registration Page
 */

$pageTitle = 'Register - Nyalife HMS';
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header">
                    <h4 class="mb-0 text-white">Register for Nyalife HMS</h4>
                </div>
                <div class="card-body p-4">
                    <?php
                    $errors = SessionManager::get('registration_errors', []);
$formData = SessionManager::get('registration_data', []);

// Clear the session data
SessionManager::remove('registration_errors');
SessionManager::remove('registration_data');

if (!empty($errors) && isset($errors['general'])):
    ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $errors['general'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Step indicator and aria-live region for accessibility -->
                    <div class="registration-header d-flex flex-column align-items-stretch mb-3">
                        <div class="step-dots d-flex justify-content-center mb-2" aria-hidden="true"></div>
                        <div id="authValidationStatus" class="visually-hidden" role="status" aria-live="polite"></div>
                        <div class="register-progress-wrapper" aria-hidden="true">
                            <div class="register-progress-bar" style="width:0%"></div>
                        </div>
                    </div>

                    <form id="registrationForm" action="<?= $baseUrl ?>/register" method="post" data-nyalife-form="true" data-validate-blur="true" class="multi-step-form">
                        <input type="hidden" name="role" value="patient">

                        <div class="registration-steps">
                            <!-- Step 1: Personal Information -->
                            <div class="form-step active" data-step="1">
                                <h5 class="mb-3">Personal Information</h5>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>" 
                                               id="first_name" name="first_name" value="<?= $formData['first_name'] ?? '' ?>" required>
                                        <?php if (isset($errors['first_name'])): ?>
                                        <div class="invalid-feedback"><?= $errors['first_name'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>" 
                                               id="last_name" name="last_name" value="<?= $formData['last_name'] ?? '' ?>" required>
                                        <?php if (isset($errors['last_name'])): ?>
                                        <div class="invalid-feedback"><?= $errors['last_name'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                                               id="email" name="email" value="<?= $formData['email'] ?? '' ?>" required>
                                        <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback"><?= $errors['email'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Phone</label>
                                        <input type="text" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                                               id="phone" name="phone" value="<?= $formData['phone'] ?? '' ?>" required>
                                        <?php if (isset($errors['phone'])): ?>
                                        <div class="invalid-feedback"><?= $errors['phone'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                                        <input type="date" class="form-control <?= isset($errors['date_of_birth']) ? 'is-invalid' : '' ?>" 
                                               id="date_of_birth" name="date_of_birth" value="<?= $formData['date_of_birth'] ?? '' ?>" required>
                                        <?php if (isset($errors['date_of_birth'])): ?>
                                        <div class="invalid-feedback"><?= $errors['date_of_birth'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="gender" class="form-label">Gender</label>
                                        <select class="form-select <?= isset($errors['gender']) ? 'is-invalid' : '' ?>" 
                                                id="gender" name="gender" required>
                                            <option value="">Select Gender</option>
                                            <option value="female" <?= (isset($formData['gender']) && $formData['gender'] === 'female') ? 'selected' : '' ?>>Female</option>
                                            <option value="male" <?= (isset($formData['gender']) && $formData['gender'] === 'male') ? 'selected' : '' ?>>Male</option>
                                            <option value="other" <?= (isset($formData['gender']) && $formData['gender'] === 'other') ? 'selected' : '' ?>>Other</option>
                                        </select>
                                        <?php if (isset($errors['gender'])): ?>
                                        <div class="invalid-feedback"><?= $errors['gender'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="blood_group" class="form-label">Blood Group</label>
                                        <select class="form-select <?= isset($errors['blood_group']) ? 'is-invalid' : '' ?>" 
                                                id="blood_group" name="blood_group">
                                            <option value="">Select Blood Group</option>
                                            <option value="A+" <?= (isset($formData['blood_group']) && $formData['blood_group'] === 'A+') ? 'selected' : '' ?>>A+</option>
                                            <option value="A-" <?= (isset($formData['blood_group']) && $formData['blood_group'] === 'A-') ? 'selected' : '' ?>>A-</option>
                                            <option value="B+" <?= (isset($formData['blood_group']) && $formData['blood_group'] === 'B+') ? 'selected' : '' ?>>B+</option>
                                            <option value="B-" <?= (isset($formData['blood_group']) && $formData['blood_group'] === 'B-') ? 'selected' : '' ?>>B-</option>
                                            <option value="AB+" <?= (isset($formData['blood_group']) && $formData['blood_group'] === 'AB+') ? 'selected' : '' ?>>AB+</option>
                                            <option value="AB-" <?= (isset($formData['blood_group']) && $formData['blood_group'] === 'AB-') ? 'selected' : '' ?>>AB-</option>
                                            <option value="O+" <?= (isset($formData['blood_group']) && $formData['blood_group'] === 'O+') ? 'selected' : '' ?>>O+</option>
                                            <option value="O-" <?= (isset($formData['blood_group']) && $formData['blood_group'] === 'O-') ? 'selected' : '' ?>>O-</option>
                                        </select>
                                        <?php if (isset($errors['blood_group'])): ?>
                                        <div class="invalid-feedback"><?= $errors['blood_group'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="height" class="form-label">Height (cm)</label>
                                        <input type="number" step="0.01" class="form-control <?= isset($errors['height']) ? 'is-invalid' : '' ?>" 
                                               id="height" name="height" value="<?= $formData['height'] ?? '' ?>">
                                        <?php if (isset($errors['height'])): ?>
                                        <div class="invalid-feedback"><?= $errors['height'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="weight" class="form-label">Weight (kg)</label>
                                        <input type="number" step="0.01" class="form-control <?= isset($errors['weight']) ? 'is-invalid' : '' ?>" 
                                               id="weight" name="weight" value="<?= $formData['weight'] ?? '' ?>">
                                        <?php if (isset($errors['weight'])): ?>
                                        <div class="invalid-feedback"><?= $errors['weight'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>" 
                                              id="address" name="address" rows="2"><?= $formData['address'] ?? '' ?></textarea>
                                    <?php if (isset($errors['address'])): ?>
                                    <div class="invalid-feedback"><?= $errors['address'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-outline-secondary me-2 btn-step-next">Next</button>
                                </div>
                            </div>

                            <!-- Step 2: Account Information -->
                            <div class="form-step" data-step="2">
                                <h5 class="mt-3 mb-3">Account Information</h5>
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                                           id="username" name="username" value="<?= $formData['username'] ?? '' ?>" required>
                                    <?php if (isset($errors['username'])): ?>
                                    <div class="invalid-feedback"><?= $errors['username'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" autocomplete="new-password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                               id="password" name="password" required>
                                        <div class="form-text">Password must be at least 8 characters and include uppercase, lowercase, and numbers.</div>
                                        <?php if (isset($errors['password'])): ?>
                                        <div class="invalid-feedback"><?= $errors['password'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="confirm_password" class="form-label">Confirm Password</label>
                                        <input type="password" autocomplete="new-password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                                               id="confirm_password" name="confirm_password" required>
                                        <?php if (isset($errors['confirm_password'])): ?>
                                        <div class="invalid-feedback"><?= $errors['confirm_password'] ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input <?= isset($errors['terms']) ? 'is-invalid' : '' ?>" 
                                           id="terms" name="terms">
                                    <label class="form-check-label" for="terms">I agree to the terms and conditions</label>
                                    <?php if (isset($errors['terms'])): ?>
                                    <div class="invalid-feedback"><?= $errors['terms'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-outline-secondary btn-step-prev">Previous</button>
                                    <button type="submit" class="btn btn-primary">Register</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <div class="mt-4 text-center">
                        <p>Already have an account? <a href="<?= $baseUrl ?>/login">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <!-- Bundled Assets -->
    <link rel="stylesheet" href="<?= AssetHelper::getCss('shared') ?>">
    <script src="<?= AssetHelper::getJs('runtime') ?>"></script>
    <script src="<?= AssetHelper::getJs('vendors') ?>"></script>
    <script src="<?= AssetHelper::getJs('shared') ?>"></script>
    <script src="<?= AssetHelper::getJs('auth-register') ?>"></script>

