<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Validation service for user input
 */
class ValidationService
{
    /**
     * Validate first name
     * Rules: 2-15 characters, only letters (Cyrillic or Latin), no spaces (no double names)
     */
    public static function validateFirstName(string $name): array
    {
        $errors = [];
        $trimmed = trim($name);
        
        // Check for spaces (double names not allowed)
        if (str_contains($trimmed, ' ')) {
            $errors[] = 'Имя должно быть одним словом, без пробелов.';
        }
        
        // Check length and characters
        if (!preg_match('/^[A-Za-zА-Яа-яЁё]{2,15}$/', $trimmed)) {
            $errors[] = 'Имя должно содержать от 2 до 15 букв (только кириллица или латиница).';
        }
        
        return $errors;
    }
    
    /**
     * Validate last name
     * Rules: 2-15 characters, only letters (Cyrillic or Latin), no spaces (no double surnames)
     */
    public static function validateLastName(string $name): array
    {
        $errors = [];
        $trimmed = trim($name);
        
        // Check for spaces (double surnames not allowed per requirements)
        if (str_contains($trimmed, ' ')) {
            $errors[] = 'Фамилия должна быть одним словом, без пробелов.';
        }
        
        // Check length and characters
        if (!preg_match('/^[A-Za-zА-Яа-яЁё]{2,15}$/', $trimmed)) {
            $errors[] = 'Фамилия должна содержать от 2 до 15 букв (только кириллица или латиница).';
        }
        
        return $errors;
    }
    
    /**
     * Validate email
     * Rules: Must match email pattern
     */
    public static function validateEmail(string $email): array
    {
        $errors = [];
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Некорректный формат email адреса.';
        }
        
        return $errors;
    }
    
    /**
     * Validate login
     * Rules: Minimum 6 characters
     */
    public static function validateLogin(string $login): array
    {
        $errors = [];
        
        if (strlen(trim($login)) < 6) {
            $errors[] = 'Логин должен содержать не менее 6 символов.';
        }
        
        return $errors;
    }
    
    /**
     * Validate password
     * Rules: Min 8 chars, must contain lowercase, uppercase, digits, special characters
     */
    public static function validatePassword(string $password): array
    {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = 'Пароль должен содержать не менее 8 символов.';
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Пароль должен содержать строчные буквы.';
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Пароль должен содержать прописные буквы.';
        }
        
        if (!preg_match('/\d/', $password)) {
            $errors[] = 'Пароль должен содержать цифры.';
        }
        
        if (!preg_match('/[\W_]/', $password)) {
            $errors[] = 'Пароль должен содержать специальные символы.';
        }
        
        return $errors;
    }
    
    /**
     * Validate password confirmation
     */
    public static function validatePasswordConfirmation(string $password, string $confirmation): array
    {
        $errors = [];
        
        if ($password !== $confirmation) {
            $errors[] = 'Пароли не совпадают.';
        }
        
        return $errors;
    }
    
    /**
     * Validate rules acceptance checkbox
     */
    public static function validateRulesAccepted(bool $accepted): array
    {
        $errors = [];
        
        if (!$accepted) {
            $errors[] = 'Необходимо принять правила сайта.';
        }
        
        return $errors;
    }
    
    /**
     * Validate reCAPTCHA response
     */
    public static function validateCaptcha(?string $response, string $secretKey): array
    {
        $errors = [];
        
        if (empty($response)) {
            $errors[] = 'Подтвердите, что вы не робот.';
            return $errors;
        }
        
        $verifyUrl = "https://www.google.com/recaptcha/api/siteverify";
        $postData = http_build_query([
            'secret' => $secretKey,
            'response' => $response
        ]);
        
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => $postData
            ]
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents($verifyUrl, false, $context);
        
        if ($result === false) {
            $errors[] = 'Ошибка проверки капчи.';
            return $errors;
        }
        
        $responseData = json_decode($result, true);
        
        if (!isset($responseData['success']) || !$responseData['success']) {
            $errors[] = 'Капча не пройдена. Попробуйте еще раз.';
        }
        
        return $errors;
    }
    
    /**
     * Validate age group selection
     */
    public static function validateAgeGroup(string $ageGroup): array
    {
        $errors = [];
        $allowedValues = ['18+', '<18'];
        
        if (!in_array($ageGroup, $allowedValues, true)) {
            $errors[] = 'Некорректное значение возрастной группы.';
        }
        
        return $errors;
    }
    
    /**
     * Validate gender selection
     */
    public static function validateGender(string $gender): array
    {
        $errors = [];
        $allowedValues = ['Мужской', 'Женский'];
        
        if (!in_array($gender, $allowedValues, true)) {
            $errors[] = 'Некорректное значение пола.';
        }
        
        return $errors;
    }
}
