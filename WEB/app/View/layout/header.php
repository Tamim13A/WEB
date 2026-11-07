<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= $meta_description ?? 'Real Estate Portal - Buy, Sell, Rent Properties' ?>">
    <meta name="keywords" content="<?= $meta_keywords ?? 'real estate, property, buy, sell, rent, Bangladesh' ?>">
    <title><?= $title ?? 'Real Estate Portal' ?></title>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/main.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
    
    <!-- CSRF Token for AJAX -->
    <meta name="csrf-token" content="<?= Security::generateCsrfToken() ?>">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" role="navigation" aria-label="Main navigation">
        <div class="container navbar-container">
            <a href="/" class="navbar-brand" aria-label="Real Estate Portal Home">
                <svg class="brand-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                <span>RealEstate</span>
            </a>
            
            <button class="navbar-toggle" aria-expanded="false" aria-controls="navbar-menu" aria-label="Toggle navigation">
                <span class="hamburger"></span>
            </button>
            
            <div class="navbar-menu" id="navbar-menu" role="menubar">
                <ul class="navbar-nav" role="menu">
                    <li role="none"><a href="/properties" class="nav-link" role="menuitem">Properties</a></li>
                    
                    <?php if (Session::isLoggedIn()): ?>
                        <?php if (Session::userRole() === 'customer'): ?>
                            <li role="none"><a href="/customer/dashboard" class="nav-link" role="menuitem">Dashboard</a></li>
                            <li role="none"><a href="/customer/favourites" class="nav-link" role="menuitem">Favourites</a></li>
                            <li role="none"><a href="/customer/inquiries" class="nav-link" role="menuitem">Inquiries</a></li>
                        <?php elseif (Session::userRole() === 'agent'): ?>
                            <li role="none"><a href="/agent/dashboard" class="nav-link" role="menuitem">Dashboard</a></li>
                            <li role="none"><a href="/agent/properties/create" class="nav-link" role="menuitem">Add Property</a></li>
                        <?php elseif (Session::userRole() === 'admin'): ?>
                            <li role="none"><a href="/admin/dashboard" class="nav-link" role="menuitem">Admin Panel</a></li>
                        <?php endif; ?>
                        
                        <li role="none">
                            <div class="nav-user dropdown">
                                <button class="dropdown-toggle" aria-expanded="false" aria-haspopup="true" aria-label="User menu">
                                    <span class="user-avatar"><?= strtoupper(Session::user()['name'][0] ?? 'U') ?></span>
                                    <span class="user-name"><?= Security::escape(Session::user()['name'] ?? 'User') ?></span>
                                    <svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <polyline points="6 9 12 15 18 9"></polyline>
                                    </svg>
                                </button>
                                <div class="dropdown-menu" role="menu">
                                    <a href="/logout" class="dropdown-item" role="menuitem">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                        Logout
                                    </a>
                                </div>
                            </div>
                        </li>
                    <?php else: ?>
                        <li role="none"><a href="/login" class="nav-link btn btn-ghost" role="menuitem">Login</a></li>
                        <li role="none"><a href="/register" class="nav-link btn btn-primary" role="menuitem">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <div class="flash-container" aria-live="polite" aria-atomic="true">
        <?php foreach (Session::getFlash() as $type => $message): ?>
            <div class="flash flash-<?= $type ?>" role="alert">
                <span><?= Security::escape($message) ?></span>
                <button class="flash-close" aria-label="Dismiss">&times;</button>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Main Content -->
    <main id="main-content" role="main">