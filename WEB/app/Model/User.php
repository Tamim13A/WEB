<?php
/**
 * User Model
 * CIS324 Real Estate Portal
 */

class User extends Model
{
    protected string $table = 'users';
    protected array $fillable = [
        'name', 'email', 'password_hash', 'role', 'phone', 
        'profile_image', 'is_active', 'email_verified_at'
    ];
    protected array $hidden = ['password_hash', 'remember_token'];
    protected array $casts = [
        'is_active' => 'bool',
        'email_verified_at' => 'datetime',
    ];

    /**
     * Find by email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }

    /**
     * Get users by role
     */
    public function getByRole(string $role, array $columns = ['*']): array
    {
        return $this->all($columns, 'created_at', 'DESC', ['role' => $role]);
    }

    /**
     * Get agents (for admin management)
     */
    public function getAgents(int $page = 1, int $perPage = 12): array
    {
        return $this->paginate($page, $perPage, ['role' => 'agent', 'is_active' => 1]);
    }

    /**
     * Get customers
     */
    public function getCustomers(int $page = 1, int $perPage = 12): array
    {
        return $this->paginate($page, $perPage, ['role' => 'customer', 'is_active' => 1]);
    }

    /**
     * Create user with hashed password
     */
    public function createUser(array $data): int
    {
        if (isset($data['password'])) {
            $data['password_hash'] = Security::hashPassword($data['password']);
            unset($data['password']);
        }
        $data['role'] = $data['role'] ?? 'customer';
        $data['is_active'] = $data['is_active'] ?? 1;
        return $this->create($data);
    }

    /**
     * Update user (handle password separately)
     */
    public function updateUser(int $id, array $data): int
    {
        if (isset($data['password']) && $data['password'] !== '') {
            $data['password_hash'] = Security::hashPassword($data['password']);
        }
        unset($data['password'], $data['password_confirmation']);
        return $this->update($id, $data);
    }

    /**
     * Verify password
     */
    public function verifyPassword(int $id, string $password): bool
    {
        $user = $this->find($id);
        if (!$user) {
            return false;
        }
        return Security::verifyPassword($password, $user['password_hash']);
    }

    /**
     * Change password
     */
    public function changePassword(int $id, string $newPassword): int
    {
        return $this->update($id, ['password_hash' => Security::hashPassword($newPassword)]);
    }

    /**
     * Get user's properties count
     */
    public function getPropertiesCount(int $userId): int
    {
        $sql = "SELECT COUNT(*) as total FROM `properties` WHERE `user_id` = ?";
        $result = Database::fetchOne($sql, [$userId]);
        return $result['total'] ?? 0;
    }

    /**
     * Get user's inquiries count
     */
    public function getInquiriesCount(int $userId): int
    {
        $sql = "SELECT COUNT(*) as total FROM `inquiries` WHERE `user_id` = ?";
        $result = Database::fetchOne($sql, [$userId]);
        return $result['total'] ?? 0;
    }

    /**
     * Toggle user active status
     */
    public function toggleActive(int $id): int
    {
        $user = $this->find($id);
        if (!$user) {
            return 0;
        }
        return $this->update($id, ['is_active' => !$user['is_active']]);
    }
}