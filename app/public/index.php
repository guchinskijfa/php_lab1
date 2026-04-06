<?php

declare(strict_types=1);

/**
 * Main Application Entry Point
 * 
 * This file bootstraps the application and handles all HTTP requests
 */

// Error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Don't display errors to users
ini_set('log_errors', '1');

// Set default timezone
date_default_timezone_set('Europe/Moscow');

// Load autoloader
require_once __DIR__ . '/src/autoload.php';

// Import classes
use App\Services\AuthService;
use App\Services\ThemeService;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;

// Start session
AuthService::startSession();

// Get current theme
$theme = ThemeService::getCurrentTheme();
$themeIcon = ThemeService::getThemeIcon();
$themeButtonText = ThemeService::getToggleButtonText();

// Initialize controllers
$authController = new AuthController();
$dashboardController = new DashboardController();

// Initialize view variables
$pageTitle = 'Портал регистрации';
$globalMessage = '';
$globalMessageType = 'info';
$content = '';
$scripts = '';

// Handle theme toggle AJAX request
if (isset($_POST['toggle_theme'])) {
    $newTheme = ThemeService::toggleTheme();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'theme' => $newTheme,
        'icon' => ThemeService::getThemeIcon(),
        'buttonText' => ThemeService::getToggleButtonText()
    ]);
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    $authController->logout();
    header('Location: index.php');
    exit;
}

// Handle AJAX availability check
if (isset($_GET['check_ajax'])) {
    header('Content-Type: application/json');
    $field = $_GET['field'] ?? '';
    $value = trim($_GET['value'] ?? '');
    
    if ($field === 'login') {
        $available = $authController->checkLoginAvailability($value);
        echo json_encode(['available' => $available, 'taken' => !$available]);
    } elseif ($field === 'email') {
        $available = $authController->checkEmailAvailability($value);
        echo json_encode(['available' => $available, 'taken' => !$available]);
    } else {
        echo json_encode(['error' => 'Invalid field']);
    }
    exit;
}

// Form data preservation
$formData = [
    'first_name' => $_POST['first_name'] ?? '',
    'last_name' => $_POST['last_name'] ?? '',
    'email' => $_POST['email'] ?? '',
    'login' => $_POST['login'] ?? '',
    'age' => $_POST['age'] ?? '18+',
    'gender' => $_POST['gender'] ?? 'Мужской',
    'rules' => isset($_POST['rules']),
];

$fieldErrors = [];
$formErrors = [];
$loginError = null;

// Handle registration
if (isset($_POST['register'])) {
    $result = $authController->register($_POST);
    
    if ($result['success']) {
        $globalMessage = $result['message'];
        $globalMessageType = 'success';
        // Clear form data on successful registration
        $formData = [
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'login' => '',
            'age' => '18+',
            'gender' => 'Мужской',
            'rules' => false,
        ];
    } else {
        $formErrors = $result['errors'];
        $globalMessage = $result['message'];
        $globalMessageType = 'warning';
    }
}

// Handle login
if (isset($_POST['login_btn'])) {
    $login = trim($_POST['user_login'] ?? '');
    $password = $_POST['user_pass'] ?? '';
    
    $result = $authController->login($login, $password);
    
    if ($result['success']) {
        header('Location: index.php');
        exit;
    } else {
        $loginError = $result['message'];
    }
}

// Check if user is logged in
$isLoggedIn = AuthService::isLoggedIn();
$userName = AuthService::getCurrentUserName();

// Dashboard data (for logged in users)
$surnames = [];
$stats = [];
$searchResults = [];
$searchQuery = '';

if ($isLoggedIn) {
    $surnames = $dashboardController->getAllSurnames();
    $stats = $dashboardController->getStatistics();
    
    // Handle search
    if (!empty($_GET['usersearch'])) {
        $searchQuery = trim($_GET['usersearch']);
        $searchResults = $dashboardController->searchUsers($searchQuery);
    }
    
    // Render dashboard view
    ob_start();
    include __DIR__ . '/src/Views/partials/dashboard.php';
    $content = ob_get_clean();
} else {
    // Render auth forms view
    ob_start();
    include __DIR__ . '/src/Views/partials/auth_forms.php';
    $content = ob_get_clean();
}

// Client-side validation script
$scripts = '<script>
(function() {
    "use strict";
    
    const patterns = {
        email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        login: /^.{6,}$/,
        name: /^[A-Za-zА-Яа-яЁё]{2,15}$/,
        password: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/
    };
    
    // AJAX availability check
    async function checkAvailability(field, value) {
        if (value.length < 3) return false;
        try {
            const response = await fetch(`?check_ajax=1&field=${field}&value=${encodeURIComponent(value)}`);
            const data = await response.json();
            return data.taken || false;
        } catch (e) {
            return false;
        }
    }
    
    // Validate single field
    async function validateField(field) {
        if (!field || !field.name) return true;
        
        let isValid = field.checkValidity();
        let errorMsg = "";
        
        if (field.type === "checkbox") {
            isValid = field.checked;
        } else if (field.name === "first_name" || field.name === "last_name") {
            if (field.value.includes(" ")) {
                isValid = false;
                errorMsg = "Только одно слово, без пробелов.";
            } else if (!patterns.name.test(field.value.trim())) {
                isValid = false;
                errorMsg = "2-15 букв, только кириллица или латиница.";
            }
        } else if (field.name === "email" || field.name === "login") {
            isValid = patterns[field.name].test(field.value);
            if (isValid && field.value.length >= 3) {
                const taken = await checkAvailability(field.name, field.value);
                if (taken) {
                    isValid = false;
                    errorMsg = "Уже занято в базе.";
                }
            }
        } else if (field.name === "password") {
            isValid = patterns.password.test(field.value);
        } else if (field.name === "confirm_password") {
            const password = document.getElementById("password");
            isValid = field.value === password.value && field.value !== "";
        }
        
        // Update UI
        field.classList.remove("is-valid", "is-invalid");
        if (field.value !== "" || field.type === "checkbox") {
            field.classList.add(isValid ? "is-valid" : "is-invalid");
        }
        
        // Update error message
        if (!isValid && errorMsg) {
            const feedbackId = field.id + "Feedback";
            const feedbackEl = document.getElementById(feedbackId);
            if (feedbackEl) feedbackEl.textContent = errorMsg;
        }
        
        return isValid;
    }
    
    // Attach validators to form
    const regForm = document.getElementById("registrationForm");
    if (regForm) {
        // Input validation on change
        regForm.querySelectorAll("input, select").forEach(input => {
            const eventType = input.type === "checkbox" ? "change" : "input";
            input.addEventListener(eventType, () => validateField(input));
        });
        
        // Form submission validation
        regForm.addEventListener("submit", async function(e) {
            const inputs = Array.from(this.querySelectorAll("input[required], select"));
            const results = await Promise.all(inputs.map(input => validateField(input)));
            
            if (results.some(r => !r)) {
                e.preventDefault();
                // Scroll to first invalid field
                const firstInvalid = this.querySelector(".is-invalid");
                if (firstInvalid) firstInvalid.scrollIntoView({ behavior: "smooth", block: "center" });
            }
        });
    }
})();
</script>';

// Render main layout
include __DIR__ . '/src/Views/layouts/main.php';
