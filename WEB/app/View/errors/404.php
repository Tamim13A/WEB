<?php
/**
 * 404 Error Page
 * CIS324 Real Estate Portal
 */
$this->layout = 'layout/main';
http_response_code(404);
?>
<div class="error-page">
    <div class="error-container">
        <div class="error-content">
            <div class="error-code">404</div>
            <h1>Page Not Found</h1>
            <p>The page you're looking for doesn't exist or has been moved.</p>
            <a href="/" class="btn btn-primary">Go Home</a>
            <a href="/properties" class="btn btn-secondary">Browse Properties</a>
        </div>
    </div>
</div>