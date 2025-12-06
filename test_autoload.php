<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Router;         // assuming you have a class like this
use App\Models\PatientModel; // assuming you have a model

// Test helper from functions.php
if (function_exists('dd')) {
    dd("Functions loaded successfully ✅");
} else {
    echo "❌ functions.php not loaded.\n";
}

// Test a core class
$router = new Router();
echo "Loaded class: " . get_class($router) . PHP_EOL;

// Test a model
$patient = new PatientModel();
echo "Model loaded: " . get_class($patient) . PHP_EOL;
