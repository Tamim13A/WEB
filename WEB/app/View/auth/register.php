<?php
/**
 * Register View
 * CIS324 Real Estate Portal
 */
$this->layout = 'layout/main';
?>
<div class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>Create Account</h1>
                <p>Join thousands of property seekers</p>
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
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" 
                           value="<?= Security::escape($old['name'] ?? '') ?>" 
                           required autocomplete="name" minlength="2" maxlength="100">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" 
                           value="<?= Security::escape($old['email'] ?? '') ?>" 
                           required autocomplete="email">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number (Bangladesh)</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?= Security::escape($old['phone'] ?? '') ?>" 
                           placeholder="+8801XXXXXXXXX or 01XXXXXXXXX" autocomplete="tel">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="new-password" minlength="8">
                    <small class="form-hint">At least 8 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block btn-lg">Create Account</button>
            </form>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="/login">Sign in</a></p>
            </div>
        </div>
    </div>
</div>