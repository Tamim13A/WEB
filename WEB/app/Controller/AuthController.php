<?php
/**
 * Authentication Controller
 * CIS324 Real Estate Portal
 */

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Show login form
     */
    public function showLogin(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirect(Auth::dashboardRoute());
        }
        
        $this->view('auth/login', [
            'csrf_token' => Security::generateCsrfToken(),
        ]);
    }

    /**
     * Handle login
     */
    public function login(): void
    {
        $email = trim($this->input('email'));
        $password = $this->input('password');
        $remember = $this->input('remember', false);

        $errors = $this->validate([
            'email' => $email,
            'password' => $password,
        ], [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (!empty($errors)) {
            $this->view('auth/login', [
                'errors' => $errors,
                'old' => ['email' => $email],
                'csrf_token' => Security::generateCsrfToken(),
            ]);
            return;
        }

        $user = $this->userModel->findByEmail($email);
        
        if (!$user || !$this->userModel->verifyPassword($user['id'], $password)) {
            $this->view('auth/login', [
                'errors' => ['email' => 'Invalid email or password'],
                'old' => ['email' => $email],
                'csrf_token' => Security::generateCsrfToken(),
            ]);
            return;
        }

        if (!$user['is_active']) {
            $this->view('auth/login', [
                'errors' => ['email' => 'Account is deactivated. Contact administrator.'],
                'old' => ['email' => $email],
                'csrf_token' => Security::generateCsrfToken(),
            ]);
            return;
        }

        // Login successful
        Session::login($user);

        // Handle remember me
        if ($remember) {
            $token = Security::randomString(64);
            // In production, store hashed token in database
            Response::cookie('remember_token', $token, time() + 2592000, '/', '', true, true, 'Strict');
        }

        $this->redirect(Auth::dashboardRoute());
    }

    /**
     * Show registration form
     */
    public function showRegister(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirect(Auth::dashboardRoute());
        }

        $this->view('auth/register', [
            'csrf_token' => Security::generateCsrfToken(),
        ]);
    }

    /**
     * Handle registration
     */
    public function register(): void
    {
        $data = [
            'name' => trim($this->input('name')),
            'email' => trim($this->input('email')),
            'password' => $this->input('password'),
            'password_confirmation' => $this->input('password_confirmation'),
            'phone' => trim($this->input('phone')),
            'role' => 'customer', // Default role
        ];

        $errors = $this->validate($data, [
            'name' => 'required|min:2|max:100',
            'email' => 'required|email|max:150',
            'password' => 'required|min:8|confirmed',
            'phone' => 'phone',
        ]);

        // Check email uniqueness
        if ($this->userModel->findByEmail($data['email'])) {
            $errors['email'] = 'Email is already registered';
        }

        if (!empty($errors)) {
            $this->view('auth/register', [
                'errors' => $errors,
                'old' => ['name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone']],
                'csrf_token' => Security::generateCsrfToken(),
            ]);
            return;
        }

        $userId = $this->userModel->createUser($data);
        
        // Auto-login after registration
        $user = $this->userModel->find($userId);
        Session::login($user);

        Session::flash('success', 'Registration successful! Welcome to Real Estate Portal.');
        $this->redirect(Auth::dashboardRoute());
    }

    /**
     * Handle logout
     */
    public function logout(): void
    {
        Session::logout();
        Response::deleteCookie('remember_token');
        $this->redirect('/login');
    }
}