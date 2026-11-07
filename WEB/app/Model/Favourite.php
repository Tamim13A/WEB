<?php
/**
 * Favourite Model
 * CIS324 Real Estate Portal
 */

class Favourite extends Model
{
    protected string $table = 'favourites';
    protected array $fillable = ['user_id', 'property_id'];
    protected array $casts = [
        'user_id' => 'int',
        'property_id' => 'int',
    ];

    /**
     * Add to favourites
     */
    public function add(int $userId, int $propertyId): int
    {
        // Check if already exists
        if ($this->exists(['user_id' => $userId, 'property_id' => $propertyId])) {
            return 0; // Already favourited
        }
        return $this->create([
            'user_id' => $userId,
            'property_id' => $propertyId,
        ]);
    }

    /**
     * Remove from favourites
     */
    public function remove(int $userId, int $propertyId): int
    {
        $sql = "DELETE FROM `{$this->table}` WHERE `user_id` = ? AND `property_id` = ?";
        return Database::execute($sql, [$userId, $propertyId]);
    }

    /**
     * Check if favourited
     */
    public function isFavourited(int $userId, int $propertyId): bool
    {
        return $this->exists(['user_id' => $userId, 'property_id' => $propertyId]);
    }

    /**
     * Get user's favourites with property details
     */
    public function getByUser(int $userId, int $page = 1, int $perPage = 12): array
    {
        $sql = "
            SELECT 
                f.`created_at` as `favourited_at`,
                p.*, 
                u.`name` as `agent_name`,
                c.`name` as `category_name`,
                pi.`file_path` as `primary_image`
            FROM `favourites` f
            INNER JOIN `properties` p ON f.`property_id` = p.`id`
            INNER JOIN `users` u ON p.`user_id` = u.`id`
            INNER JOIN `property_categories` c ON p.`category_id` = c.`id`
            LEFT JOIN `property_images` pi ON pi.`property_id` = p.`id` AND pi.`is_primary` = 1
            WHERE f.`user_id` = ? AND p.`status` = 'approved'
            ORDER BY f.`created_at` DESC
            LIMIT ? OFFSET ?
        ";
        $offset = ($page - 1) * $perPage;
        $data = Database::fetchAll($sql, [$userId, $perPage, $offset]);

        $countSql = "
            SELECT COUNT(*) as total 
            FROM `favourites` f 
            INNER JOIN `properties` p ON f.`property_id` = p.`id` 
            WHERE f.`user_id` = ? AND p.`status` = 'approved'
        ";
        $totalResult = Database::fetchOne($countSql, [$userId]);
        $total = $totalResult['total'] ?? 0;

        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => (int)ceil($total / $perPage),
            ],
        ];
    }

    /**
     * Get favourite count for property
     */
    public function getPropertyFavouriteCount(int $propertyId): int
    {
        $sql = "SELECT COUNT(*) as total FROM `{$this->table}` WHERE `property_id` = ?";
        $result = Database::fetchOne($sql, [$propertyId]);
        return $result['total'] ?? 0;
    }

    /**
     * Toggle favourite (add if not exists, remove if exists)
     */
    public function toggle(int $userId, int $propertyId): array
    {
        if ($this->isFavourited($userId, $propertyId)) {
            $this->remove($userId, $propertyId);
            return ['favourited' => false, 'count' => $this->getPropertyFavouriteCount($propertyId)];
        } else {
            $this->add($userId, $propertyId);
            return ['favourited' => true, 'count' => $this->getPropertyFavouriteCount($propertyId)];
        }
    }

    /**
     * Get user's favourite property IDs (for quick checks)
     */
    public function getUserFavouriteIds(int $userId): array
    {
        $sql = "SELECT `property_id` FROM `{$this->table}` WHERE `user_id` = ?";
        return array_column(Database::fetchAll($sql, [$userId]), 'property_id');
    }
}