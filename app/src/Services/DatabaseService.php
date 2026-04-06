<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use PDOException;
use App\Config\DatabaseConfig;

/**
 * Database connection service using PDO
 */
class DatabaseService
{
    private static ?PDO $instance = null;
    
    /**
     * Get singleton PDO instance
     * @throws PDOException
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            self::$instance = new PDO(
                DatabaseConfig::getDsn(),
                DatabaseConfig::getUser(),
                DatabaseConfig::getPassword(),
                DatabaseConfig::getOptions()
            );
        }
        
        return self::$instance;
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup(): void
    {
        throw new \Exception("Cannot unserialize singleton");
    }
}
