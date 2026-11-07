<?php
/**
 * Inquiry Model
 * CIS324 Real Estate Portal
 */

class Inquiry extends Model
{
    protected string $table = 'inquiries';
    protected array $fillable = [
        'property_id', 'user_id', 'message', 'status', 
        'agent_response', 'responded_by'
    ];
    protected array $casts = [
        'property_id' => 'int',
        'user_id' => 'int',
        'responded_by' => 'int',
    ];

    /**
     * Create inquiry
     */
    public function createInquiry(int $propertyId, int $userId, string $message): int
    {
        return $this->create([
            'property_id' => $propertyId,
            'user_id' => $userId,
            'message' => $message,
            'status' => 'new',
        ]);
    }

    /**
     * Get inquiries for a property
     */
    public function getByProperty(int $propertyId, int $page = 1, int $perPage = 12): array
    {
        return $this->paginate($page, $perPage, ['property_id' => $propertyId], ['*'], 'created_at', 'DESC');
    }

    /**
     * Get inquiries by customer
     */
    public function getByCustomer(int $userId, int $page = 1, int $perPage = 12): array
    {
        $sql = "
            SELECT 
                i.*, 
                p.`title` as `property_title`, p.`price`, p.`price_type`, p.`status` as `property_status`,
                pi.`file_path` as `property_image`
            FROM `inquiries` i
            INNER JOIN `properties` p ON i.`property_id` = p.`id`
            LEFT JOIN `property_images` pi ON pi.`property_id` = p.`id` AND pi.`is_primary` = 1
            WHERE i.`user_id` = ?
            ORDER BY i.`created_at` DESC
            LIMIT ? OFFSET ?
        ";
        $offset = ($page - 1) * $perPage;
        $data = Database::fetchAll($sql, [$userId, $perPage, $offset]);

        // Count
        $countSql = "SELECT COUNT(*) as total FROM `inquiries` WHERE `user_id` = ?";
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
     * Get inquiries for agent's properties
     */
    public function getByAgent(int $agentId, int $page = 1, int $perPage = 12): array
    {
        $sql = "
            SELECT 
                i.*, 
                p.`title` as `property_title`, p.`price`, p.`price_type`,
                u.`name` as `customer_name`, u.`email` as `customer_email`, u.`phone` as `customer_phone`
            FROM `inquiries` i
            INNER JOIN `properties` p ON i.`property_id` = p.`id`
            INNER JOIN `users` u ON i.`user_id` = u.`id`
            WHERE p.`user_id` = ?
            ORDER BY i.`created_at` DESC
            LIMIT ? OFFSET ?
        ";
        $offset = ($page - 1) * $perPage;
        $data = Database::fetchAll($sql, [$agentId, $perPage, $offset]);

        $countSql = "
            SELECT COUNT(*) as total 
            FROM `inquiries` i 
            INNER JOIN `properties` p ON i.`property_id` = p.`id` 
            WHERE p.`user_id` = ?
        ";
        $totalResult = Database::fetchOne($countSql, [$agentId]);
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
     * Get all inquiries (admin)
     */
    public function getAll(int $page = 1, int $perPage = 12): array
    {
        $sql = "
            SELECT 
                i.*, 
                p.`title` as `property_title`,
                u1.`name` as `customer_name`, u1.`email` as `customer_email`,
                u2.`name` as `agent_name`
            FROM `inquiries` i
            INNER JOIN `properties` p ON i.`property_id` = p.`id`
            INNER JOIN `users` u1 ON i.`user_id` = u1.`id`
            INNER JOIN `users` u2 ON p.`user_id` = u2.`id`
            ORDER BY i.`created_at` DESC
            LIMIT ? OFFSET ?
        ";
        $offset = ($page - 1) * $perPage;
        $data = Database::fetchAll($sql, [$perPage, $offset]);

        $countSql = "SELECT COUNT(*) as total FROM `inquiries`";
        $totalResult = Database::fetchOne($countSql);
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
     * Respond to inquiry
     */
    public function respond(int $id, int $agentId, string $response): int
    {
        return $this->update($id, [
            'status' => 'contacted',
            'agent_response' => $response,
            'responded_at' => date('Y-m-d H:i:s'),
            'responded_by' => $agentId,
        ]);
    }

    /**
     * Update inquiry status
     */
    public function updateStatus(int $id, string $status): int
    {
        $allowed = ['new', 'contacted', 'negotiating', 'closed', 'rejected'];
        if (!in_array($status, $allowed, true)) {
            return 0;
        }
        return $this->update($id, ['status' => $status]);
    }

    /**
     * Get inquiry with relations
     */
    public function findWithRelations(int $id): ?array
    {
        $sql = "
            SELECT 
                i.*, 
                p.`title` as `property_title`, p.`price`, p.`price_type`, p.`address`, p.`city`,
                u1.`name` as `customer_name`, u1.`email` as `customer_email`, u1.`phone` as `customer_phone`,
                u2.`name` as `agent_name`, u2.`email` as `agent_email`
            FROM `inquiries` i
            INNER JOIN `properties` p ON i.`property_id` = p.`id`
            INNER JOIN `users` u1 ON i.`user_id` = u1.`id`
            INNER JOIN `users` u2 ON p.`user_id` = u2.`id`
            WHERE i.`id` = ?
        ";
        return Database::fetchOne($sql, [$id]);
    }

    /**
     * Get statistics for agent/admin
     */
    public function getStats(int $agentId = 0): array
    {
        $where = $agentId > 0 ? "WHERE p.`user_id` = {$agentId}" : "";
        
        $sql = "
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN i.`status` = 'new' THEN 1 ELSE 0 END) as new,
                SUM(CASE WHEN i.`status` = 'contacted' THEN 1 ELSE 0 END) as contacted,
                SUM(CASE WHEN i.`status` = 'negotiating' THEN 1 ELSE 0 END) as negotiating,
                SUM(CASE WHEN i.`status` = 'closed' THEN 1 ELSE 0 END) as closed,
                SUM(CASE WHEN i.`status` = 'rejected' THEN 1 ELSE 0 END) as rejected
            FROM `inquiries` i
            INNER JOIN `properties` p ON i.`property_id` = p.`id`
            {$where}
        ";
        return Database::fetchOne($sql) ?? [];
    }
}