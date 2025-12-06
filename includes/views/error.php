<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($errorCode) ? $errorCode . ' Error' : 'Error' ?> - Nyalife HMS</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 40px;
        }
        .card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2e59d9;
        }
        .error-code {
            font-size: 6rem;
            font-weight: 700;
            color: #e74a3b;
        }
        .debug-section {
            background-color: #f1f1f1;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 2rem;
        }
        .route-suggestion {
            background-color: #e8f4fe;
            border-left: 4px solid #4e73df;
            padding: 0.5rem 1rem;
            margin-bottom: 0.5rem;
        }
        code {
            background-color: #272c30;
            color: #f8f9fa;
            padding: 0.2rem 0.4rem;
            border-radius: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="container py-3">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center p-4">
                        <div class="error-code"><?= isset($errorCode) ? $errorCode : '404' ?></div>
                        <h2 class="mb-3"><?= isset($errorMessage) ? htmlspecialchars($errorMessage) : 'Page Not Found' ?></h2>
                        <p class="lead mb-4">The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="<?= isset($baseUrl) ? $baseUrl : '/' ?>" class="btn btn-primary">
                                <i class="fas fa-home me-2"></i> Go to Homepage
                            </a>
                            <a href="javascript:history.back()" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i> Go Back
                            </a>
                        </div>
                    </div>
                </div>
                
                <?php if (defined('DEBUG_MODE') && DEBUG_MODE): ?>
                    <!-- Debug Information Section -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0"><i class="fas fa-bug me-2"></i> Debug Information</h5>
                        </div>
                        <div class="card-body">
                            <h6>Request Details:</h6>
                            <ul class="list-group mb-3">
                                <li class="list-group-item"><strong>Requested URL:</strong> <?= htmlspecialchars($_SERVER['REQUEST_URI']) ?></li>
                                <li class="list-group-item"><strong>Request Method:</strong> <?= htmlspecialchars($_SERVER['REQUEST_METHOD']) ?></li>
                                <li class="list-group-item"><strong>Server Protocol:</strong> <?= htmlspecialchars($_SERVER['SERVER_PROTOCOL']) ?></li>
                            </ul>
                            
                            <?php if (isset($suggestedRoutes) && !empty($suggestedRoutes)): ?>
                                <div class="mt-4">
                                    <h5>Did you mean one of these?</h5>
                                    <ul>
                                        <?php foreach ($suggestedRoutes as $name => $info): ?>
                                        <li><a href="<?= $baseUrl . $info['pattern'] ?>"><?= $name ?> (<?= $info['pattern'] ?>)</a></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($debugInfo) && !empty($debugInfo)): ?>
                                <div class="mt-4">
                                    <h5>Debug Information</h5>
                                    <div class="bg-dark text-light p-3 rounded">
                                        <pre><?= htmlspecialchars($debugInfo) ?></pre>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($availableRoutes)): ?>
                                <h6>All Available Routes:</h6>
                                <div class="debug-section">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Route Name</th>
                                                    <th>Pattern</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($availableRoutes as $name => $pattern): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($name) ?></td>
                                                        <td><code><?= htmlspecialchars($pattern) ?></code></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
