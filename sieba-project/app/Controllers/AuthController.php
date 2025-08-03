<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class AuthController extends Controller
{
    protected $userModel;
    protected $session;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->session = session();
        helper(['form', 'url']);
    }

    /**
     * Show login form
     */
    public function login()
    {
        // Redirect if already logged in
        if ($this->session->get('isLoggedIn')) {
            $role = $this->session->get('role');
            return redirect()->to($role === 'admin' ? '/admin/dashboard' : '/user/dashboard');
        }

        $data = [
            'title' => 'Login - SIEBA',
            'validation' => \Config\Services::validation()
        ];

        return view('auth/login', $data);
    }

    /**
     * Process login
     */
    public function processLogin()
    {
        $rules = [
            'email' => 'required|valid_email',
            'password' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $user = $this->userModel->verifyLogin($email, $password);

        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'Email atau password salah');
        }

        if (!$user['is_verified']) {
            return redirect()->back()->withInput()->with('error', 'Akun belum diverifikasi. Silakan cek email Anda.');
        }

        // Set session data
        $sessionData = [
            'user_id' => $user['id'],
            'nama' => $user['nama'],
            'email' => $user['email'],
            'role' => $user['role'],
            'isLoggedIn' => true
        ];

        $this->session->set($sessionData);

        // Redirect based on role
        $redirectUrl = $user['role'] === 'admin' ? '/admin/dashboard' : '/user/dashboard';
        return redirect()->to($redirectUrl)->with('success', 'Login berhasil!');
    }

    /**
     * Show registration form
     */
    public function register()
    {
        // Redirect if already logged in
        if ($this->session->get('isLoggedIn')) {
            return redirect()->to('/user/dashboard');
        }

        $data = [
            'title' => 'Daftar Akun - SIEBA',
            'validation' => \Config\Services::validation()
        ];

        return view('auth/register', $data);
    }

    /**
     * Process registration
     */
    public function processRegister()
    {
        $rules = [
            'nama' => 'required|min_length[3]|max_length[100]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
            'no_hp' => 'required|numeric|min_length[10]|max_length[15]',
            'institusi' => 'required|min_length[3]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nama' => $this->request->getPost('nama'),
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'no_hp' => $this->request->getPost('no_hp'),
            'institusi' => $this->request->getPost('institusi'),
            'jabatan' => $this->request->getPost('jabatan'),
            'alamat' => $this->request->getPost('alamat'),
            'tanggal_lahir' => $this->request->getPost('tanggal_lahir'),
            'jenis_kelamin' => $this->request->getPost('jenis_kelamin')
        ];

        $userId = $this->userModel->createUser($data);

        if ($userId) {
            // Send verification email (implement later)
            // $this->sendVerificationEmail($data['email'], $verificationToken);

            return redirect()->to('/auth/login')->with('success', 'Pendaftaran berhasil! Silakan cek email untuk verifikasi akun.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat mendaftar. Silakan coba lagi.');
        }
    }

    /**
     * Verify email
     */
    public function verifyEmail($token = null)
    {
        if (!$token) {
            return redirect()->to('/')->with('error', 'Token verifikasi tidak valid');
        }

        $verified = $this->userModel->verifyEmail($token);

        if ($verified) {
            return redirect()->to('/auth/login')->with('success', 'Email berhasil diverifikasi! Silakan login.');
        } else {
            return redirect()->to('/')->with('error', 'Token verifikasi tidak valid atau sudah kadaluarsa');
        }
    }

    /**
     * Logout
     */
    public function logout()
    {
        $this->session->destroy();
        return redirect()->to('/')->with('success', 'Anda telah berhasil logout');
    }

    /**
     * Show forgot password form
     */
    public function forgotPassword()
    {
        $data = [
            'title' => 'Lupa Password - SIEBA',
            'validation' => \Config\Services::validation()
        ];

        return view('auth/forgot_password', $data);
    }

    /**
     * Process forgot password
     */
    public function processForgotPassword()
    {
        $rules = [
            'email' => 'required|valid_email'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');
        $reset = $this->userModel->requestPasswordReset($email);

        if ($reset) {
            // Send reset email (implement later)
            // $this->sendPasswordResetEmail($email, $resetToken);
            return redirect()->back()->with('success', 'Link reset password telah dikirim ke email Anda');
        } else {
            return redirect()->back()->with('error', 'Email tidak ditemukan');
        }
    }

    /**
     * Show reset password form
     */
    public function resetPassword($token = null)
    {
        if (!$token) {
            return redirect()->to('/')->with('error', 'Token reset tidak valid');
        }

        $data = [
            'title' => 'Reset Password - SIEBA',
            'token' => $token,
            'validation' => \Config\Services::validation()
        ];

        return view('auth/reset_password', $data);
    }

    /**
     * Process reset password
     */
    public function processResetPassword()
    {
        $rules = [
            'token' => 'required',
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $token = $this->request->getPost('token');
        $password = $this->request->getPost('password');

        $reset = $this->userModel->resetPassword($token, $password);

        if ($reset) {
            return redirect()->to('/auth/login')->with('success', 'Password berhasil direset! Silakan login dengan password baru.');
        } else {
            return redirect()->back()->with('error', 'Token tidak valid atau sudah kadaluarsa');
        }
    }

    /**
     * Send verification email (to be implemented)
     */
    private function sendVerificationEmail($email, $token)
    {
        // Implementation depends on email service
        // Will be implemented when EmailService is created
    }

    /**
     * Send password reset email (to be implemented)
     */
    private function sendPasswordResetEmail($email, $token)
    {
        // Implementation depends on email service
        // Will be implemented when EmailService is created
    }
}