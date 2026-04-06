<?php
session_start();

// --- 1. ПОДКЛЮЧЕНИЕ К БД ---
$host = getenv('DB_HOST') ?: 'db';
$db   = getenv('DB_NAME') ?: 'auth_lab';
$user = getenv('DB_USER') ?: 'lab_user';
$pass = getenv('DB_PASSWORD') ?: 'lab_password_456';

$conn = pg_connect("host=$host dbname=$db user=$user password=$pass");
if (!$conn) { die("Ошибка подключения к базе данных."); }

// --- AJAX ОБРАБОТЧИК (Для проверки занятости логина/почты) ---
if (isset($_GET['check_ajax'])) {
    $field = $_GET['field'];
    $value = trim($_GET['value']);
    if ($field === 'login' || $field === 'email') {
        $res = pg_query_params($conn, "SELECT id FROM users WHERE $field = $1", [$value]);
        $exists = pg_fetch_assoc($res);
        echo json_encode(['taken' => (bool)$exists]);
    }
    exit;
}

// Создание таблицы
pg_query($conn, "CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    email VARCHAR(100) UNIQUE,
    login VARCHAR(50) UNIQUE,
    password TEXT,
    age_group VARCHAR(20),
    gender VARCHAR(10),
    theme VARCHAR(10) DEFAULT 'light',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$theme = $_COOKIE['theme'] ?? 'light';
$errors = [];
$success = "";

// Данные из POST для сохранения в формах
$f_name = $_POST['first_name'] ?? '';
$l_name = $_POST['last_name'] ?? '';
$u_email = $_POST['email'] ?? '';
$u_login = $_POST['login'] ?? '';
$u_age = $_POST['age'] ?? '18+';
$u_gender = $_POST['gender'] ?? 'Мужской';
$rules_accepted = isset($_POST['rules']);

// --- 2. ОБРАБОТКА РЕГИСТРАЦИИ ---
if (isset($_POST['register'])) {
    $pass   = $_POST['password'];
    $pass2  = $_POST['confirm_password'];

    // Валидация имени (2-15 символов, только буквы, без пробелов)
    if (!preg_match('/^[A-Za-zА-Яа-яЁё]{2,15}$/', trim($f_name))) {
        $errors['first_name'] = "Имя: 2-15 букв, без пробелов.";
    }
    // Валидация фамилии (2-15 символов, только буквы, без пробелов - запрет двойных фамилий)
    if (!preg_match('/^[A-Za-zА-Яа-яЁё]{2,15}$/', trim($l_name))) {
        $errors['last_name'] = "Фамилия: 2-15 букв, без пробелов (одна фамилия).";
    }

    // Проверка занятости (защита от Warning Postgres)
    $check_res = pg_query_params($conn, "SELECT login, email FROM users WHERE login = $1 OR email = $2", [$u_login, $u_email]);
    while ($row = pg_fetch_assoc($check_res)) {
        if ($row['login'] === $u_login) $errors['login'] = "Этот логин уже занят.";
        if ($row['email'] === $u_email) $errors['email'] = "Этот Email уже зарегистрирован.";
    }

    if (strlen($u_login) < 6) $errors['login'] = "Логин от 6 символов.";
    
    // Валидация сложности пароля
    if (strlen($pass) < 8 || 
        !preg_match('/[a-z]/', $pass) || 
        !preg_match('/[A-Z]/', $pass) || 
        !preg_match('/\d/', $pass) || 
        !preg_match('/[\W_]/', $pass)) {
        $errors['password'] = "Пароль должен содержать: строчные, прописные буквы, цифры и спецсимволы (мин. 8).";
    }
    
    if ($pass !== $pass2) $errors['pass2'] = "Пароли не совпадают.";
    if (!$rules_accepted) $errors['rules'] = "Примите правила.";

    // Проверка капчи
    $recaptcha_secret = getenv('RECAPTCHA_SECRET_KEY');
    if (!empty($_POST['g-recaptcha-response'])) {
        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$recaptcha_secret&response=".$_POST['g-recaptcha-response']);
        $resp = json_decode($response, true);
        if (!$resp["success"]) $errors['captcha'] = "Капча не пройдена.";
    } else {
        $errors['captcha'] = "Подтвердите, что вы не робот.";
    }

    if (empty($errors)) {
        $hashed_pass = password_hash($pass, PASSWORD_BCRYPT);
        $res = @pg_query_params($conn, 
            "INSERT INTO users (first_name, last_name, email, login, password, age_group, gender) VALUES ($1, $2, $3, $4, $5, $6, $7)", 
            [$f_name, $l_name, $u_email, $u_login, $hashed_pass, $u_age, $u_gender]
        );
        if ($res) {
            $success = "Регистрация успешна! Теперь вы можете войти.";
            $f_name = $l_name = $u_email = $u_login = ""; $rules_accepted = false;
        } else {
            $errors['db'] = "Ошибка базы данных.";
        }
    }
}

// --- 3. ОБРАБОТКА ВХОДА ---
if (isset($_POST['login_btn'])) {
    $l = trim($_POST['user_login']);
    $p = $_POST['user_pass'];
    $res = pg_query_params($conn, "SELECT * FROM users WHERE login = $1", [$l]);
    $user_data = pg_fetch_assoc($res);
    if ($user_data && password_verify($p, $user_data['password'])) {
        $_SESSION['user_id'] = $user_data['id'];
        $_SESSION['user_name'] = $user_data['first_name'];
        header("Location: index.php"); exit;
    } else { $errors['login_fail'] = "Неверный логин или пароль."; }
}

if (isset($_GET['logout'])) { session_destroy(); header("Location: index.php"); exit; }

// --- 4. ПОИСК ПО ФРАЗЕ ---
$search_results = [];
if (!empty($_GET['usersearch'])) {
    $s = trim($_GET['usersearch']);
    $words = explode(' ', $s);
    $where = "";
    foreach($words as $w) {
        $w = pg_escape_string($conn, $w);
        if ($where) $where .= " OR ";
        $where .= "first_name ILIKE '%$w%' OR last_name ILIKE '%$w%'";
    }
    $res = pg_query($conn, "SELECT first_name, last_name FROM users WHERE $where LIMIT 10");
    while($row = pg_fetch_assoc($res)) $search_results[] = $row;
}
?>

<!DOCTYPE html>
<html lang="ru" data-bs-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <title>Auth System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body class="p-4">

<div class="container" style="max-width: 1100px;">
    
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <h2 class="mb-0">Portal Lab</h2>
        <button id="themeToggle" class="btn btn-outline-secondary">
            <?= ($theme == 'light') ? '🌙 Темная' : '☀️ Светлая' ?>
        </button>
    </div>

    <!-- Уведомления -->
    <?php if (isset($errors['captcha']) || isset($errors['db']) || isset($errors['login_fail'])): ?>
        <div class="alert alert-danger"><?= $errors['captcha'] ?? $errors['db'] ?? $errors['login_fail'] ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if (isset($errors['first_name']) || isset($errors['last_name']) || isset($errors['password'])): ?>
        <div class="alert alert-warning">
            <ul class="mb-0">
            <?php if (isset($errors['first_name'])): ?><li><?= $errors['first_name'] ?></li><?php endif; ?>
            <?php if (isset($errors['last_name'])): ?><li><?= $errors['last_name'] ?></li><?php endif; ?>
            <?php if (isset($errors['password'])): ?><li><?= $errors['password'] ?></li><?php endif; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['user_id'])): ?>
        
        <!-- --- ЛИЧНЫЙ КАБИНЕТ --- -->
        <div class="alert alert-primary d-flex justify-content-between align-items-center">
            <span>Добро пожаловать, <b><?= htmlspecialchars($_SESSION['user_name']) ?></b>!</span>
            <a href="?logout=1" class="btn btn-sm btn-danger">Выйти</a>
        </div>

        <div class="row g-4">
            <!-- Статистика -->
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-success text-white">Статистика</div>
                    <div class="card-body">
                        <?php
                        $t = pg_fetch_assoc(pg_query($conn, "SELECT COUNT(*) FROM users"));
                        $m = pg_fetch_assoc(pg_query($conn, "SELECT COUNT(*) FROM users WHERE created_at > date_trunc('month', current_date)"));
                        $l = pg_fetch_assoc(pg_query($conn, "SELECT last_name FROM users ORDER BY created_at DESC LIMIT 1"));
                        ?>
                        <p>Всего: <b><?= $t['count'] ?></b></p>
                        <p>За месяц: <b><?= $m['count'] ?></b></p>
                        <p>Последний: <b><?= htmlspecialchars($l['last_name'] ?? '-') ?></b></p>
                    </div>
                </div>
            </div>

            <!-- Список -->
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-info text-white">Пользователи</div>
                    <div class="card-body overflow-auto" style="max-height: 200px;">
                        <?php
                        $list = pg_query($conn, "SELECT last_name FROM users ORDER BY last_name ASC");
                        while($u = pg_fetch_assoc($list)) echo htmlspecialchars($u['last_name'])."<br>";
                        ?>
                    </div>
                </div>
            </div>

            <!-- Поиск -->
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header bg-warning">Поиск</div>
                    <div class="card-body">
                        <form method="GET" class="input-group input-group-sm mb-3">
                            <input type="text" name="usersearch" class="form-control" placeholder="Имя или фамилия...">
                            <button class="btn btn-primary" type="submit">Найти</button>
                        </form>
                        <?php foreach($search_results as $sr): ?>
                            <div class="small border-bottom"><?= htmlspecialchars($sr['first_name']." ".$sr['last_name']) ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>

        <!-- --- ФОРМЫ АВТОРИЗАЦИИ --- -->
        <div class="row g-4 shadow-lg rounded-4 overflow-hidden border">
            <!-- Регистрация -->
            <div class="col-md-7 bg-body p-5">
                <h3 class="mb-4">Регистрация</h3>
                <form method="POST" id="regForm" novalidate class="row g-3" autocomplete="off">
                    <div class="col-md-6">
                        <label class="form-label small">Имя</label>
                        <input type="text" name="first_name" id="first_name" class="form-control" value="<?= htmlspecialchars($f_name) ?>" required minlength="2" maxlength="15" pattern="[A-Za-zА-Яа-яЁё]+" title="Только буквы, от 2 до 15 символов">
                        <div class="invalid-feedback" id="firstNameFeedback">Имя: 2-15 букв, без пробелов.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Фамилия</label>
                        <input type="text" name="last_name" id="last_name" class="form-control" value="<?= htmlspecialchars($l_name) ?>" required minlength="2" maxlength="15" pattern="[A-Za-zА-Яа-яЁё]+" title="Только буквы, от 2 до 15 символов">
                        <div class="invalid-feedback" id="lastNameFeedback">Фамилия: 2-15 букв, без пробелов (одна фамилия).</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Email</label>
                        <input type="email" name="email" id="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($u_email) ?>" required>
                        <div class="invalid-feedback" id="emailFeedback"><?= $errors['email'] ?? 'Некорректный Email.' ?></div>
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Логин</label>
                        <input type="text" name="login" id="login" class="form-control <?= isset($errors['login']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($u_login) ?>" required>
                        <div class="invalid-feedback" id="loginFeedback"><?= $errors['login'] ?? 'Минимум 6 символов.' ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Пароль</label>
                        <input type="password" name="password" id="password" class="form-control" required minlength="8" pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}" title="Минимум 8 символов: строчные, прописные буквы, цифры и спецсимволы">
                        <div class="invalid-feedback" id="passwordFeedback">Пароль должен содержать: строчные, прописные буквы, цифры и спецсимволы (мин. 8).</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Повтор пароля</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control <?= isset($errors['pass2']) ? 'is-invalid' : '' ?>" required>
                        <div class="invalid-feedback">Пароли не совпадают.</div>
                    </div>

                    <!-- ВОЗВРАЩЕННЫЙ ВЫБОР ПОЛА И ВОЗРАСТА -->
                    <div class="col-md-6">
                        <label class="form-label small">Возрастная группа</label>
                        <select name="age" class="form-select">
                            <option value="18+" <?= ($u_age == '18+') ? 'selected' : '' ?>>Мне есть 18 лет</option>
                            <option value="<18" <?= ($u_age == '<18') ? 'selected' : '' ?>>Мне меньше 18 лет</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small d-block">Пол</label>
                        <div class="d-flex gap-3 pt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" value="Мужской" <?= ($u_gender == 'Мужской') ? 'checked' : '' ?>>
                                <label class="form-check-label">М</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="gender" value="Женский" <?= ($u_gender == 'Женский') ? 'checked' : '' ?>>
                                <label class="form-check-label">Ж</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input <?= isset($errors['rules']) ? 'is-invalid' : '' ?>" type="checkbox" name="rules" id="rules" required <?= $rules_accepted ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="rules">Я согласен с правилами</label>
                            <div class="invalid-feedback">Нужно ваше согласие.</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="g-recaptcha" data-sitekey="<?= getenv('RECAPTCHA_SITE_KEY') ?>"></div>
                    </div>
                    <div class="col-12">
                        <button type="submit" name="register" class="btn btn-success w-100 py-2">Зарегистрироваться</button>
                    </div>
                </form>
            </div>

            <!-- Вход -->
            <div class="col-md-5 bg-body-tertiary p-5 border-start">
                <h3 class="mb-4">Вход</h3>
                <form method="POST">
                    <div class="mb-3"><input type="text" name="user_login" class="form-control" placeholder="Логин" required></div>
                    <div class="mb-3"><input type="password" name="user_pass" class="form-control" placeholder="Пароль" required></div>
                    <button type="submit" name="login_btn" class="btn btn-primary w-100 py-2">Войти</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    // ТЕМА
    const themeBtn = document.getElementById('themeToggle');
    themeBtn.addEventListener('click', () => {
        const html = document.documentElement;
        const next = html.getAttribute('data-bs-theme') === 'light' ? 'dark' : 'light';
        html.setAttribute('data-bs-theme', next);
        document.cookie = "theme=" + next + "; path=/; max-age=" + (86400 * 30);
    });

    const patterns = {
        email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        login: /^.{6,}$/,
        name: /^[A-Za-zА-Яа-яЁё]{2,15}$/,
        password: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/
    };

    // AJAX проверка
    async function checkAvailability(field, value) {
        if (value.length < 3) return false;
        const response = await fetch(`?check_ajax=1&field=${field}&value=${encodeURIComponent(value)}`);
        const data = await response.json();
        return data.taken;
    }

    // Валидация поля
    const validate = async (field) => {
        if (!field || !field.name) return;
        let isValid = field.checkValidity();
        let errorMsg = "";

        if (field.type === 'checkbox') {
            isValid = field.checked;
        } else if (field.name === 'first_name' || field.name === 'last_name') {
            // Проверка на пробелы (двойные фамилии/имена)
            if (field.value.includes(' ')) {
                isValid = false;
                errorMsg = "Только одно слово, без пробелов.";
            } else if (!patterns.name.test(field.value)) {
                isValid = false;
                errorMsg = "2-15 букв, только кириллица или латиница.";
            }
        } else if (field.name === 'email' || field.name === 'login') {
            isValid = patterns[field.name].test(field.value);
            if (isValid) {
                const taken = await checkAvailability(field.name, field.value);
                if (taken) { isValid = false; errorMsg = "Уже занято в базе"; }
            }
        } else if (field.name === 'password') {
            isValid = patterns.password.test(field.value);
        } else if (field.name === 'confirm_password') {
            isValid = field.value === document.getElementById('password').value && field.value !== '';
        }

        if (isValid) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            if (errorMsg) {
                const feedback = document.getElementById(field.id + 'Feedback');
                if (feedback) feedback.innerText = errorMsg;
            }
        }
        return isValid;
    };

    // При вводе
    document.querySelectorAll('#regForm input').forEach(input => {
        const evt = input.type === 'checkbox' ? 'change' : 'input';
        input.addEventListener(evt, () => validate(input));
    });

    // При загрузке страницы (восстановление подсветки)
    window.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('#regForm input').forEach(input => {
            if (input.value !== "" || input.type === 'checkbox' || input.classList.contains('is-invalid')) {
                validate(input);
            }
        });
    });

    // Перед отправкой
    document.getElementById('regForm')?.addEventListener('submit', async function(e) {
        const inputs = Array.from(this.querySelectorAll('input'));
        let results = await Promise.all(inputs.map(input => validate(input)));
        if (results.includes(false)) {
            e.preventDefault();
            alert('Исправьте ошибки в форме!');
        }
    });
</script>
</body>
</html>