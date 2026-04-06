<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;

/**
 * Authentication service for user login/logout
 */
class AuthService
{
    private const SESSION_KEY_USER_ID = 'user_id';
    private const SESSION_KEY_USER_NAME = 'user_name';
    
    /**
     * Start session if not already started
     */
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Authenticate user by login and password
     */
    public static function authenticate(string $login, string $password, UserRepository $userRepository): ?User
    {
        $user = $userRepository->findByLogin($login);
        
        if ($user === null) {
            return null;
        }
        
        if (!password_verify($password, $user->getPassword())) {
            return null;
        }
        
        return $user;
    }
    
    /**
     * Login user and set session data
     */
    public static function login(User $user): void
    {
        self::startSession();
        $_SESSION[self::SESSION_KEY_USER_ID] = $user->id;
        $_SESSION[self::SESSION_KEY_USER_NAME] = $user->firstName;
    }
    
    /**
     * Logout user and destroy session
     */
    public static function logout(): void
    {
        self::startSession();
        session_unset();
        session_destroy();
    }
    
    /**
     * Check if user is logged in
     */
    public static function isLoggedIn(): bool
    {
        self::startSession();
        return isset($_SESSION[self::SESSION_KEY_USER_ID]);
    }
    
    /**
     * Get current logged in user ID
     */
    public static function getCurrentUserId(): ?int
    {
        self::startSession();
        return $_SESSION[self::SESSION_KEY_USER_ID] ?? null;
    }
    
    /**
     * Get current logged in user name
     */
    public static function getCurrentUserName(): ?string
    {
        self::startSession();
        return $_SESSION[self::SESSION_KEY_USER_NAME] ?? null;
    }
    
    /**
     * Hash password using bcrypt
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Verify password against hash
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
