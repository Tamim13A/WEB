<?php
/**
 * Login View
 * CIS324 Real Estate Portal
 */
$this->layout = 'layout/main';
?>
<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Welcome Back</h1>
                <p>Sign in to your account</p>
            </div>
            
            <?php if (isset($errors) && !empty($errors)): ?>
                <div class="alert alert-error" role="alert">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?= Security::escape($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form" novalidate>
                <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" 
                           value="<?= Security::escape($old['email'] ?? '') ?>" 
                           required autocomplete="email" autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                
                <div class="form-group form-checkbox">
                    <input type="checkbox" id="remember" name="remember" value="1">
                    <label for="remember">Remember me for 30 days</label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block btn-lg">Sign In</button>
            </form>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="/register">Create one</a></p>
            </div>
        </div>
    </div>
</div>