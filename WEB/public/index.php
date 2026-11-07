<?php
/**
 * Front Controller
 * CIS324 Real Estate Portal
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/php-error.log');

// Create logs directory if not exists
if (!is_dir(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}

// Timezone
date_default_timezone_set('Asia/Dhaka');

// Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

// Start session
Session::start();

// Security headers
Response::setSecurityHeaders();

// Initialize router
$router = new Router('');

// ==================== PUBLIC ROUTES ====================

// Home - Property listing
$router->get('/', [PropertyController::class, 'index']);
$router->get('/properties', [PropertyController::class, 'index']);

// Property details
$router->get('/properties/{id:\d+}', [PropertyController::class, 'show']);

// API endpoints
$router->get('/api/properties', [ApiController::class, 'properties']);
$router->get('/api/properties/featured', [ApiController::class, 'featured']);
$router->get('/api/properties/{id:\d+}', [ApiController::class, 'property']);
$router->get('/api/categories', [ApiController::class, 'categories']);
$router->get('/api/cities', [ApiController::class, 'cities']);
$router->get('/api/properties/stats', [ApiController::class, 'stats']);

// ==================== AUTH ROUTES ====================

$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'register']);
$router->get('/logout', [AuthController::class, 'logout']);

// ==================== PROTECTED ROUTES ====================

// Customer routes
$router->group(['prefix' => 'customer', 'middleware' => ['auth', 'role:customer']], function($router) {
    $router->get('/dashboard', [DashboardController::class, 'index']);
    $router->get('/favourites', [FavouriteController::class, 'index']);
    $router->get('/inquiries', [InquiryController::class, 'myInquiries']);
    $router->post('/inquiries', [InquiryController::class, 'store']);
    $router->get('/inquiries/{id:\d+}', [InquiryController::class, 'show']);
});

// Agent routes
$router->group(['prefix' => 'agent', 'middleware' => ['auth', 'role:agent']], function($router) {
    $router->get('/dashboard', [DashboardController::class, 'index']);
    $router->get('/inquiries', [InquiryController::class, 'agentInquiries']);
    $router->post('/inquiries/{id:\d+}/respond', [InquiryController::class, 'respond']);
    $router->post('/inquiries/{id:\d+}/status', [InquiryController::class, 'updateStatus']);
    
    // Property management
    $router->get('/properties/create', [PropertyController::class, 'create']);
    $router->post('/properties', [PropertyController::class, 'store']);
    $router->get('/properties/{id:\d+}/edit', [PropertyController::class, 'edit']);
    $router->post('/properties/{id:\d+}', [PropertyController::class, 'update']);
    $router->post('/properties/{id:\d+}/delete', [PropertyController::class, 'destroy']);
    
    // Image management
    $router->post('/properties/images/delete', [PropertyController::class, 'deleteImage']);
    $router->post('/properties/images/primary', [PropertyController::class, 'setPrimaryImage']);
});

// Admin routes
$router->group(['prefix' => 'admin', 'middleware' => ['auth', 'role:admin']], function($router) {
    $router->get('/dashboard', [DashboardController::class, 'index']);
    
    // User management
    $router->get('/users', [AdminController::class, 'users']);
    $router->get('/users/{id:\d+}', [AdminController::class, 'showUser']);
    $router->post('/users/{id:\d+}/toggle', [AdminController::class, 'toggleUser']);
    $router->post('/users/{id:\d+}/delete', [AdminController::class, 'deleteUser']);
    
    // Property management
    $router->get('/properties', [AdminController::class, 'properties']);
    $router->post('/properties/{id:\d+}/approve', [AdminController::class, 'approveProperty']);
    $router->post('/properties/{id:\d+}/reject', [AdminController::class, 'rejectProperty']);
    $router->post('/properties/{id:\d+}/delete', [AdminController::class, 'deleteProperty']);
    
    // Inquiry management
    $router->get('/inquiries', [AdminController::class, 'inquiries']);
    $router->post('/inquiries/{id:\d+}/status', [AdminController::class, 'updateInquiry']);
    
    // Category management
    $router->get('/categories', [AdminController::class, 'categories']);
    $router->post('/categories', [AdminController::class, 'storeCategory']);
    $router->post('/categories/{id:\d+}', [AdminController::class, 'updateCategory']);
    $router->post('/categories/{id:\d+}/toggle', [AdminController::class, 'toggleCategory']);
    $router->post('/categories/{id:\d+}/delete', [AdminController::class, 'deleteCategory']);
});

// ==================== FAVOURITE API (AJAX) ====================

$router->post('/api/favourites/toggle', [ApiController::class, 'toggleFavourite']);
$router->post('/api/inquiries', [ApiController::class, 'submitInquiry']);
$router->get('/api/favourites/check/{propertyId:\d+}', [FavouriteController::class, 'check']);

// ==================== ERROR HANDLING ====================

// Handle 404 for undefined routes
$router->get('/{any:.*}', function() {
    http_response_code(404);
    include __DIR__ . '/../app/View/errors/404.php';
});

// Dispatch request
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

try {
    $router->dispatch($method, $uri);
} catch (Throwable $e) {
    // Log error
    error_log("[" . date('Y-m-d H:i:s') . "] " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Internal server error',
            'debug' => $_ENV['APP_DEBUG'] ?? false ? $e->getMessage() : null,
        ]);
    } else {
        http_response_code(500);
        include __DIR__ . '/../app/View/errors/500.php';
    }
}