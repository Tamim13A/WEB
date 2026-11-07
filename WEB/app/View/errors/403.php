<?php
/**
 * 403 Error Page
 * CIS324 Real Estate Portal
 */
$this->layout = 'layout/main';
http_response_code(403);
?>
<div class="error-page">
    <div class="error-container">
        <div class="error-content">
            <div class="error-code">403</div>
            <h1>Access Denied</h1>
            <p>You don't have permission to access this page.</p>
            <a href="/" class="btn btn-primary">Go Home</a>
            <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
        </div>
    </div>
</div>