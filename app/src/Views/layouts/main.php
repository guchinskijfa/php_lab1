<!DOCTYPE html>
<html lang="ru" data-bs-theme="<?= htmlspecialchars($theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    

    <!-- reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    
    <!-- Custom styles -->
    <style>
        .form-control.is-invalid, .form-select.is-invalid {
            border-color: #dc3545;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        .form-control.is-valid, .form-select.is-valid {
            border-color: #198754;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        .card-stat {
            transition: transform 0.2s ease-in-out;
        }
        .card-stat:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="min-vh-100">

<div class="container py-4" style="max-width: 1200px;">
    <!-- Header with theme toggle -->
    <header class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
        <h1 class="h3 mb-0">Портал регистрации пользователей</h1>
        <button id="themeToggle" class="btn btn-outline-secondary" type="button" aria-label="Переключить тему">
            <?= $themeIcon ?> <span class="ms-2"><?= $themeButtonText ?></span>
        </button>
    </header>

    <!-- Flash messages / Alerts -->
    <?php if (!empty($globalMessage)): ?>
    <div class="alert alert-<?= htmlspecialchars($globalMessageType) ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($globalMessage) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Main content placeholder -->
    <?= $content ?>

    <!-- Footer -->
    <footer class="mt-5 pt-3 border-top text-center text-muted small">
        <p>&copy; <?= date('Y') ?> Portal Lab. Все права защищены.</p>
    </footer>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

<!-- Theme toggle script -->
<script>
(function() {
    'use strict';
    
    const themeBtn = document.getElementById('themeToggle');
    
    if (themeBtn) {
        themeBtn.addEventListener('click', function() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            // Update HTML attribute
            html.setAttribute('data-bs-theme', newTheme);
            
            // Set cookie (30 days)
            document.cookie = "theme=" + newTheme + "; path=/; max-age=" + (30 * 86400) + "; SameSite=Lax";
            
            // Update button text
            const iconSpan = this.querySelector('span');
            if (iconSpan) {
                iconSpan.textContent = newTheme === 'light' ? 'Светлая тема' : 'Темная тема';
            }
            
            // Update icon
            const iconNode = this.firstChild;
            if (iconNode) {
                iconNode.textContent = newTheme === 'light' ? '☀️' : '🌙';
            }
        });
    }
})();
</script>

<?= $scripts ?? '' ?>

</body>
</html>
