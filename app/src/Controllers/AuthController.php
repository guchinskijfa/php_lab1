<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\UserRepository;
use App\Services\ValidationService;
use App\Services\AuthService;
use App\Services\ThemeService;
use App\Models\User;

/**
 * Auth controller handling registration and login actions
 */
class AuthController
{
    private UserRepository $userRepository;
    
    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->userRepository->createTable();
    }
    
    /**
     * Handle registration form submission
     */
    public function register(array $data): array
    {
        $errors = [];
        $recaptchaSecret = getenv('RECAPTCHA_SECRET_KEY') ?: '';
        
        // Validate first name
        $firstNameErrors = ValidationService::validateFirstName($data['first_name'] ?? '');
        $errors = array_merge($errors, $firstNameErrors);
        
        // Validate last name
        $lastNameErrors = ValidationService::validateLastName($data['last_name'] ?? '');
        $errors = array_merge($errors, $lastNameErrors);
        
        // Validate email format
        $emailErrors = ValidationService::validateEmail($data['email'] ?? '');
        $errors = array_merge($errors, $emailErrors);
        
        // Check if email already exists
        if (!empty($data['email']) && $this->userRepository->emailExists($data['email'])) {
            $errors[] = 'Этот Email уже зарегистрирован.';
        }
        
        // Validate login
        $loginErrors = ValidationService::validateLogin($data['login'] ?? '');
        $errors = array_merge($errors, $loginErrors);
        
        // Check if login already exists
        if (!empty($data['login']) && $this->userRepository->loginExists($data['login'])) {
            $errors[] = 'Этот логин уже занят.';
        }
        
        // Validate password
        $passwordErrors = ValidationService::validatePassword($data['password'] ?? '');
        $errors = array_merge($errors, $passwordErrors);
        
        // Validate password confirmation
        $confirmErrors = ValidationService::validatePasswordConfirmation(
            $data['password'] ?? '',
            $data['confirm_password'] ?? ''
        );
        $errors = array_merge($errors, $confirmErrors);
        
        // Validate rules acceptance
        $rulesErrors = ValidationService::validateRulesAccepted(isset($data['rules']));
        $errors = array_merge($errors, $rulesErrors);
        
        // Validate captcha
        $captchaErrors = ValidationService::validateCaptcha(
            $data['g-recaptcha-response'] ?? null,
            $recaptchaSecret
        );
        $errors = array_merge($errors, $captchaErrors);
        
        // If no errors, create user
        if (empty($errors)) {
            $user = new User(
                firstName: trim($data['first_name']),
                lastName: trim($data['last_name']),
                email: trim($data['email']),
                login: trim($data['login']),
                password: AuthService::hashPassword($data['password']),
                ageGroup: $data['age'] ?? '18+',
                gender: $data['gender'] ?? 'Мужской'
            );
            
            try {
                $success = $this->userRepository->create($user);
                
                if ($success) {
                    return [
                        'success' => true,
                        'message' => 'Регистрация успешна! Теперь вы можете войти.',
                        'errors' => []
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Ошибка базы данных при регистрации.',
                        'errors' => ['Ошибка базы данных.']
                    ];
                }
            } catch (\PDOException $e) {
                error_log("Registration error: " . $e->getMessage());
                return [
                    'success' => false,
                    'message' => 'Произошла ошибка при регистрации.',
                    'errors' => ['Ошибка базы данных.']
                ];
            }
        }
        
        return [
            'success' => false,
            'message' => 'Исправьте ошибки в форме.',
            'errors' => $errors
        ];
    }
    
    /**
     * Handle login form submission
     */
    public function login(string $login, string $password): array
    {
        $user = AuthService::authenticate($login, $password, $this->userRepository);
        
        if ($user === null) {
            return [
                'success' => false,
                'message' => 'Неверный логин или пароль.',
                'user' => null
            ];
        }
        
        AuthService::login($user);
        
        return [
            'success' => true,
            'message' => 'Добро пожаловать!',
            'user' => $user
        ];
    }
    
    /**
     * Logout current user
     */
    public function logout(): void
    {
        AuthService::logout();
    }
    
    /**
     * Check if login is available (AJAX)
     */
    public function checkLoginAvailability(string $login): bool
    {
        return !$this->userRepository->loginExists($login);
    }
    
    /**
     * Check if email is available (AJAX)
     */
    public function checkEmailAvailability(string $email): bool
    {
        return !$this->userRepository->emailExists($email);
    }
}
