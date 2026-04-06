<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use App\Models\User;
use App\Services\DatabaseService;

/**
 * User repository for database operations
 */
class UserRepository
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = DatabaseService::getConnection();
    }
    
    /**
     * Create users table if not exists
     */
    public function createTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            login VARCHAR(50) UNIQUE NOT NULL,
            password TEXT NOT NULL,
            age_group VARCHAR(20) NOT NULL DEFAULT '18+',
            gender VARCHAR(10) NOT NULL DEFAULT 'Мужской',
            theme VARCHAR(10) DEFAULT 'light',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->db->exec($sql);
    }
    
    /**
     * Find user by login
     */
    public function findByLogin(string $login): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE login = :login");
        $stmt->execute(['login' => $login]);
        $data = $stmt->fetch();
        
        return $data ? User::fromArray($data) : null;
    }
    
    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch();
        
        return $data ? User::fromArray($data) : null;
    }
    
    /**
     * Check if login exists
     */
    public function loginExists(string $login): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE login = :login");
        $stmt->execute(['login' => $login]);
        return (int)$stmt->fetchColumn() > 0;
    }
    
    /**
     * Check if email exists
     */
    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return (int)$stmt->fetchColumn() > 0;
    }
    
    /**
     * Create new user
     */
    public function create(User $user): bool
    {
        $sql = "INSERT INTO users (first_name, last_name, email, login, password, age_group, gender, theme) 
                VALUES (:first_name, :last_name, :email, :login, :password, :age_group, :gender, :theme)";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'first_name' => $user->firstName,
            'last_name' => $user->lastName,
            'email' => $user->email,
            'login' => $user->login,
            'password' => $user->getPassword(),
            'age_group' => $user->ageGroup,
            'gender' => $user->gender,
            'theme' => $user->theme,
        ]);
    }
    
    /**
     * Get all users surnames
     * @return array<string>
     */
    public function getAllSurnames(): array
    {
        $stmt = $this->db->query("SELECT last_name FROM users ORDER BY last_name ASC");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
    
    /**
     * Get total user count
     */
    public function getTotalCount(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
    }
    
    /**
     * Get count of users registered in current month
     */
    public function getCountThisMonth(): int
    {
        $sql = "SELECT COUNT(*) FROM users WHERE created_at >= date_trunc('month', CURRENT_DATE)";
        return (int)$this->db->query($sql)->fetchColumn();
    }
    
    /**
     * Get last registered user's surname
     */
    public function getLastRegisteredSurname(): ?string
    {
        $stmt = $this->db->query("SELECT last_name FROM users ORDER BY created_at DESC LIMIT 1");
        $result = $stmt->fetchColumn();
        return $result ?: null;
    }
    
    /**
     * Search users by name or surname (full-text search)
     * @param string $query Search query
     * @param int $limit Max results
     * @return array<User>
     */
    public function searchByName(string $query, int $limit = 10): array
    {
        $words = explode(' ', trim($query));
        $conditions = [];
        $params = [];
        
        foreach ($words as $i => $word) {
            $paramName = "word{$i}";
            $conditions[] = "(first_name ILIKE :{$paramName} OR last_name ILIKE :{$paramName})";
            $params[$paramName] = "%{$word}%";
        }
        
        $whereClause = implode(' OR ', $conditions);
        $sql = "SELECT * FROM users WHERE {$whereClause} LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($params, ['limit' => $limit]));
        
        $users = [];
        while ($row = $stmt->fetch()) {
            $users[] = User::fromArray($row);
        }
        
        return $users;
    }
    
    /**
     * Update user theme preference
     */
    public function updateTheme(int $userId, string $theme): bool
    {
        $stmt = $this->db->prepare("UPDATE users SET theme = :theme WHERE id = :id");
        return $stmt->execute(['theme' => $theme, 'id' => $userId]);
    }
}
