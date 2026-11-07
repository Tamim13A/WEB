<?php
/**
 * 500 Error Page
 * CIS324 Real Estate Portal
 */
$this->layout = 'layout/main';
http_response_code(500);
?>
<div class="error-page">
    <div class="error-container">
        <div class="error-content">
            <div class="error-code">500</div>
            <h1>Server Error</h1>
            <p>Something went wrong on our end. Please try again later.</p>
            <a href="/" class="btn btn-primary">Go Home</a>
            <button class="btn btn-secondary" onclick="location.reload()">Retry</button>
        </div>
    </div>
</div>