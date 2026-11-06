<?php

namespace App\Actions;

use App\Actions\Controller;
use App\Middleware\Auth;
use App\Config\Flash;
use App\Repositories\User;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        Auth::requireGuest();
        $this->view('auth/login', ['title' => 'Login']);
    }
    
    public function login(): void
    {
        Auth::requireGuest();
        
        if (!verifyCsrf()) {
            Flash::error('Token tidak valid');
            $this->redirect('/login');
        }
        
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $redirect = $_POST['redirect'] ?? '/';
        if (!str_starts_with($redirect, '/') || str_starts_with($redirect, '//')) {
            $redirect = '/';
        }
        
        if (!$email || !$password) {
            Flash::error('Email dan password wajib diisi');
            $this->redirect('/login' . ($redirect !== '/' ? '?redirect=' . urlencode($redirect) : ''));
        }
        
        $user = User::findByEmail($email);
        
        if (!$user || !$user->verifyPassword($password)) {
            Flash::error('Email atau password salah');
            $this->redirect('/login' . ($redirect !== '/' ? '?redirect=' . urlencode($redirect) : ''));
        }
        
        Auth::login($user->toArray());
        Flash::success('Selamat datang, ' . $user->name . '!');
        $this->redirect($redirect);
    }
    
    public function showRegister(): void
    {
        Auth::requireGuest();
        $this->view('auth/register', ['title' => 'Daftar']);
    }
    
    public function register(): void
    {
        Auth::requireGuest();
        
        if (!verifyCsrf()) {
            Flash::error('Token tidak valid');
            $this->redirect('/register');
        }
        
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';
        $phone = trim($_POST['phone'] ?? '');
        $redirect = $_POST['redirect'] ?? '/';
        if (!str_starts_with($redirect, '/') || str_starts_with($redirect, '//')) {
            $redirect = '/';
        }
        
        $errors = [];
        
        if (!$name) $errors[] = 'Nama wajib diisi';
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email tidak valid';
        if (!$password) $errors[] = 'Password wajib diisi';
        if (strlen($password) < 8) $errors[] = 'Password minimal 8 karakter';
        if ($password !== $passwordConfirm) $errors[] = 'Konfirmasi password tidak cocok';
        if ($phone && !preg_match('/^[\d\s\-\+\(\)]{10,20}$/', $phone)) $errors[] = 'Nomor telepon tidak valid';
        
        if (User::findByEmail($email)) {
            $errors[] = 'Email sudah terdaftar';
        }
        
        if ($errors) {
            Flash::error(implode('<br>', $errors));
            setOld($_POST);
            $this->redirect('/register' . ($redirect !== '/' ? '?redirect=' . urlencode($redirect) : ''));
        }
        
        $user = User::createUser([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'phone' => $phone ?: null,
            'role' => 'user',
        ]);
        
        clearOld();
        Auth::login($user->toArray());
        Flash::success('Registrasi berhasil! Selamat datang di Artatix.');
        $this->redirect($redirect);
    }
    
    public function logout(): void
    {
        Auth::logout();
        Flash::success('Anda telah logout');
        $this->redirect('/');
    }
}