<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\UserRepository;
use App\Services\ThemeService;

/**
 * Dashboard controller for displaying user statistics and lists
 */
class DashboardController
{
    private UserRepository $userRepository;
    
    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }
    
    /**
     * Get all registered users' surnames
     * @return array<string>
     */
    public function getAllSurnames(): array
    {
        return $this->userRepository->getAllSurnames();
    }
    
    /**
     * Get website statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_users' => $this->userRepository->getTotalCount(),
            'users_this_month' => $this->userRepository->getCountThisMonth(),
            'last_registered_surname' => $this->userRepository->getLastRegisteredSurname() ?? '-',
        ];
    }
    
    /**
     * Search users by name or surname
     * @param string $query Search query
     * @param int $limit Max results
     * @return array
     */
    public function searchUsers(string $query, int $limit = 10): array
    {
        $users = $this->userRepository->searchByName($query, $limit);
        
        $results = [];
        foreach ($users as $user) {
            $results[] = [
                'first_name' => $user->firstName,
                'last_name' => $user->lastName,
            ];
        }
        
        return $results;
    }
}
