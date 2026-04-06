<?php

declare(strict_types=1);

namespace App\Models;

/**
 * User model representing a registered user
 */
class User
{
    public ?int $id;
    public string $firstName;
    public string $lastName;
    public string $email;
    public string $login;
    private string $password;
    public string $ageGroup;
    public string $gender;
    public string $theme;
    public ?\DateTime $createdAt;
    
    public function __construct(
        ?int $id = null,
        string $firstName = '',
        string $lastName = '',
        string $email = '',
        string $login = '',
        string $password = '',
        string $ageGroup = '18+',
        string $gender = 'Мужской',
        string $theme = 'light',
        ?\DateTime $createdAt = null
    ) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->login = $login;
        $this->password = $password;
        $this->ageGroup = $ageGroup;
        $this->gender = $gender;
        $this->theme = $theme;
        $this->createdAt = $createdAt;
    }
    
    public function getPassword(): string
    {
        return $this->password;
    }
    
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }
    
    public function getFullName(): string
    {
        return "{$this->firstName} {$this->lastName}";
    }
    
    /**
     * Create User from database row
     */
    public static function fromArray(array $data): self
    {
        $user = new self(
            isset($data['id']) ? (int)$data['id'] : null,
            $data['first_name'] ?? '',
            $data['last_name'] ?? '',
            $data['email'] ?? '',
            $data['login'] ?? '',
            $data['password'] ?? '',
            $data['age_group'] ?? '18+',
            $data['gender'] ?? 'Мужской',
            $data['theme'] ?? 'light'
        );
        
        if (isset($data['created_at'])) {
            $user->createdAt = new \DateTime($data['created_at']);
        }
        
        return $user;
    }
    
    /**
     * Convert to array for template usage
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'login' => $this->login,
            'age_group' => $this->ageGroup,
            'gender' => $this->gender,
            'theme' => $this->theme,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
        ];
    }
}
