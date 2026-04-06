<!-- Welcome message -->
<div class="alert alert-primary d-flex justify-content-between align-items-center mb-4">
    <div>
        <strong>Добро пожаловать, <?= htmlspecialchars($userName) ?>!</strong>
    </div>
    <a href="?logout=1" class="btn btn-sm btn-outline-danger">Выйти</a>
</div>

<div class="row g-4">
    <!-- Statistics Card -->
    <div class="col-md-4">
        <div class="card shadow-sm card-stat h-100 border-success">
            <div class="card-header bg-success text-white">
                <h3 class="h6 mb-0">📊 Статистика сайта</h3>
            </div>
            <div class="card-body">
                <dl class="mb-0">
                    <dt class="text-muted small">Всего пользователей:</dt>
                    <dd class="fs-4 fw-bold"><?= (int)$stats['total_users'] ?></dd>
                    
                    <dt class="text-muted small mt-3">Зарегистрировано за месяц:</dt>
                    <dd class="fs-5 fw-semibold"><?= (int)$stats['users_this_month'] ?></dd>
                    
                    <dt class="text-muted small mt-3">Последний зарегистрированный:</dt>
                    <dd class="fs-6"><?= htmlspecialchars($stats['last_registered_surname']) ?></dd>
                </dl>
            </div>
        </div>
    </div>
    
    <!-- All Surnames List -->
    <div class="col-md-4">
        <div class="card shadow-sm card-stat h-100 border-info">
            <div class="card-header bg-info text-white">
                <h3 class="h6 mb-0">👥 Список фамилий</h3>
            </div>
            <div class="card-body overflow-auto" style="max-height: 250px;">
                <?php if (empty($surnames)): ?>
                <p class="text-muted small mb-0">Пока нет зарегистрированных пользователей.</p>
                <?php else: ?>
                <?php foreach ($surnames as $surname): ?>
                <div class="border-bottom py-1"><?= htmlspecialchars($surname) ?></div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Search Card -->
    <div class="col-md-4">
        <div class="card shadow-sm card-stat h-100 border-warning">
            <div class="card-header bg-warning">
                <h3 class="h6 mb-0">🔍 Поиск пользователей</h3>
            </div>
            <div class="card-body">
                <form method="GET" class="input-group input-group-sm mb-3">
                    <input type="text" class="form-control" name="usersearch" 
                           placeholder="Имя или фамилия..." 
                           value="<?= htmlspecialchars($searchQuery ?? '') ?>">
                    <button class="btn btn-primary" type="submit">Найти</button>
                </form>
                
                <?php if (!empty($searchResults)): ?>
                <div class="small">
                    <strong>Результаты:</strong>
                    <?php foreach ($searchResults as $result): ?>
                    <div class="border-bottom py-1">
                        <?= htmlspecialchars($result['first_name'] . ' ' . $result['last_name']) ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php elseif (isset($searchQuery) && $searchQuery !== ''): ?>
                <p class="text-muted small mb-0">Ничего не найдено.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
