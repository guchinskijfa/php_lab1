<?php

declare(strict_types=1);

namespace App\Config;

/**
 * Database configuration
 */
class DatabaseConfig
{
    public static function getDsn(): string
    {
        $host = getenv('DB_HOST') ?: 'db';
        $port = getenv('DB_PORT') ?: '5432';
        $dbname = getenv('DB_NAME') ?: 'auth_lab';
        
        return "pgsql:host=$host;port=$port;dbname=$dbname";
    }
    
    public static function getUser(): string
    {
        return getenv('DB_USER') ?: 'lab_user';
    }
    
    public static function getPassword(): string
    {
        return getenv('DB_PASSWORD') ?: 'lab_password_456';
    }
    
    public static function getOptions(): array
    {
        return [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ];
    }
}
