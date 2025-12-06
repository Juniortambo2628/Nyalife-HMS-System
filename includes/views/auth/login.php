<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header">
                    <h4 class="mb-0 text-white">Login to Nyalife HMS</h4>
                </div>
                <div class="card-body p-4">
                    
                    <div id="loginAlert" style="display: none;"></div>
                    
                    <?php if (isset($message) && !empty($message)): ?>
                    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
                        <?= $message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    
                    <form id="loginForm" action="<?= rtrim($baseUrl, '/') ?>/login" method="post">
                        <div class="mb-4 mt-5">
                            <label for="username" class="form-label">Username or Email</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary position-relative">
                                <span>Login</span>
                                <span id="loginSpinner" class="spinner-border spinner-border-sm position-absolute top-50 end-0 translate-middle-y me-2 d-none" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </span>
                            </button>
                        </div>
                    </form>
                    
                    <div class="mt-4 text-center">
                        <p>Don't have an account? <a href="<?= $baseUrl ?>/register">Register here</a></p>
                        <p><a href="<?= $baseUrl ?>/forgot-password">Forgot your password?</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
