<div class="row g-4">
    <!-- Registration Form -->
    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h3 class="h5 mb-0">Регистрация</h3>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($formErrors)): ?>
                <div class="alert alert-warning" role="alert">
                    <strong>Исправьте ошибки:</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($formErrors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                
                <form method="POST" id="registrationForm" novalidate autocomplete="off">
                    <div class="row g-3">
                        <!-- First Name -->
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">Имя <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($fieldErrors['first_name']) ? 'is-invalid' : '' ?>" 
                                   id="first_name" name="first_name" 
                                   value="<?= htmlspecialchars($formData['first_name'] ?? '') ?>"
                                   required minlength="2" maxlength="15" 
                                   pattern="[A-Za-zА-Яа-яЁё]+"
                                   placeholder="Иван">
                            <div class="invalid-feedback" id="firstNameFeedback">
                                2-15 букв, без пробелов
                            </div>
                        </div>
                        
                        <!-- Last Name -->
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Фамилия <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($fieldErrors['last_name']) ? 'is-invalid' : '' ?>" 
                                   id="last_name" name="last_name" 
                                   value="<?= htmlspecialchars($formData['last_name'] ?? '') ?>"
                                   required minlength="2" maxlength="15" 
                                   pattern="[A-Za-zА-Яа-яЁё]+"
                                   placeholder="Иванов">
                            <div class="invalid-feedback" id="lastNameFeedback">
                                2-15 букв, без пробелов (одна фамилия)
                            </div>
                        </div>
                        
                        <!-- Email -->
                        <div class="col-12">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control <?= isset($fieldErrors['email']) ? 'is-invalid' : '' ?>" 
                                   id="email" name="email" 
                                   value="<?= htmlspecialchars($formData['email'] ?? '') ?>"
                                   required
                                   placeholder="example@mail.ru">
                            <div class="invalid-feedback" id="emailFeedback">
                                Некорректный email
                            </div>
                        </div>
                        
                        <!-- Login -->
                        <div class="col-12">
                            <label for="login" class="form-label">Логин <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?= isset($fieldErrors['login']) ? 'is-invalid' : '' ?>" 
                                   id="login" name="login" 
                                   value="<?= htmlspecialchars($formData['login'] ?? '') ?>"
                                   required minlength="6"
                                   placeholder="min 6 символов">
                            <div class="invalid-feedback" id="loginFeedback">
                                Минимум 6 символов
                            </div>
                        </div>
                        
                        <!-- Password -->
                        <div class="col-md-6">
                            <label for="password" class="form-label">Пароль <span class="text-danger">*</span></label>
                            <input type="password" class="form-control <?= isset($fieldErrors['password']) ? 'is-invalid' : '' ?>" 
                                   id="password" name="password" 
                                   required minlength="8"
                                   pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}"
                                   placeholder="••••••••">
                            <div class="invalid-feedback" id="passwordFeedback">
                                Мин. 8 симв.: строчные, прописные, цифры, спецсимволы
                            </div>
                        </div>
                        
                        <!-- Confirm Password -->
                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label">Подтверждение пароля <span class="text-danger">*</span></label>
                            <input type="password" class="form-control <?= isset($fieldErrors['confirm_password']) ? 'is-invalid' : '' ?>" 
                                   id="confirm_password" name="confirm_password" 
                                   required>
                            <div class="invalid-feedback">
                                Пароли не совпадают
                            </div>
                        </div>
                        
                        <!-- Age Group -->
                        <div class="col-md-6">
                            <label for="age" class="form-label">Возрастная группа</label>
                            <select class="form-select" id="age" name="age">
                                <option value="18+" <?= ($formData['age'] ?? '18+') === '18+' ? 'selected' : '' ?>>Мне есть 18 лет</option>
                                <option value="<18" <?= ($formData['age'] ?? '18+') === '<18' ? 'selected' : '' ?>>Мне меньше 18 лет</option>
                            </select>
                        </div>
                        
                        <!-- Gender Radio Buttons -->
                        <div class="col-md-6">
                            <label class="form-label d-block">Пол</label>
                            <div class="d-flex gap-3 pt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="gender_male" value="Мужской" 
                                           <?= ($formData['gender'] ?? 'Мужской') === 'Мужской' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="gender_male">Мужской</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="gender_female" value="Женский" 
                                           <?= ($formData['gender'] ?? 'Мужской') === 'Женский' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="gender_female">Женский</label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Rules Checkbox -->
                        <div class="col-12">
                            <div class="form-check <?= isset($fieldErrors['rules']) ? 'is-invalid' : '' ?>">
                                <input class="form-check-input" type="checkbox" id="rules" name="rules" 
                                       <?= isset($formData['rules']) && $formData['rules'] ? 'checked' : '' ?> required>
                                <label class="form-check-label" for="rules">
                                    Я принимаю правила сайта <span class="text-danger">*</span>
                                </label>
                                <div class="invalid-feedback d-block">
                                    Необходимо принять правила
                                </div>
                            </div>
                        </div>
                        
                        <!-- reCAPTCHA -->
                        <div class="col-12">
                            <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars(getenv('RECAPTCHA_SITE_KEY') ?: '') ?>"></div>
                            <?php if (isset($fieldErrors['captcha'])): ?>
                            <div class="text-danger small mt-1"><?= htmlspecialchars($fieldErrors['captcha']) ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="col-12">
                            <button type="submit" name="register" class="btn btn-success w-100 py-2">
                                Зарегистрироваться
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Login Form -->
    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-secondary text-white">
                <h3 class="h5 mb-0">Вход</h3>
            </div>
            <div class="card-body p-4">
                <?php if (isset($loginError)): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($loginError) ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" autocomplete="off">
                    <div class="mb-3">
                        <label for="user_login" class="form-label">Логин</label>
                        <input type="text" class="form-control" id="user_login" name="user_login" required>
                    </div>
                    <div class="mb-3">
                        <label for="user_pass" class="form-label">Пароль</label>
                        <input type="password" class="form-control" id="user_pass" name="user_pass" required>
                    </div>
                    <button type="submit" name="login_btn" class="btn btn-primary w-100">
                        Войти
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
