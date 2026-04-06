<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Theme service for managing user theme preferences via cookies
 */
class ThemeService
{
    private const COOKIE_NAME = 'theme';
    private const COOKIE_EXPIRY_DAYS = 30;
    
    /**
     * Get current theme from cookie
     */
    public static function getCurrentTheme(): string
    {
        return $_COOKIE[self::COOKIE_NAME] ?? 'light';
    }
    
    /**
     * Set theme cookie
     */
    public static function setTheme(string $theme): bool
    {
        $allowedThemes = ['light', 'dark'];
        
        if (!in_array($theme, $allowedThemes, true)) {
            return false;
        }
        
        $expiryTime = time() + (self::COOKIE_EXPIRY_DAYS * 86400);
        
        return setcookie(
            self::COOKIE_NAME,
            $theme,
            [
                'expires' => $expiryTime,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );
    }
    
    /**
     * Toggle theme (light <-> dark)
     */
    public static function toggleTheme(): string
    {
        $currentTheme = self::getCurrentTheme();
        $newTheme = $currentTheme === 'light' ? 'dark' : 'light';
        
        self::setTheme($newTheme);
        
        return $newTheme;
    }
    
    /**
     * Get button text for theme toggle
     */
    public static function getToggleButtonText(): string
    {
        $currentTheme = self::getCurrentTheme();
        
        return $currentTheme === 'light' ? '🌙 Темная тема' : '☀️ Светлая тема';
    }
    
    /**
     * Get theme icon
     */
    public static function getThemeIcon(): string
    {
        $currentTheme = self::getCurrentTheme();
        
        return $currentTheme === 'light' ? '🌙' : '☀️';
    }
}
