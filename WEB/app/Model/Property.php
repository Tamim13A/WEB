<?php
/**
 * Property Model - Essential CRUD + listing
 * CIS324 Real Estate Portal
 */

class Property extends Model
{
    protected string $table = 'properties';
    protected array $fillable = [
        'user_id', 'category_id', 'title', 'description', 'price',
        'price_type', 'status', 'address', 'city', 'state',
        'postal_code', 'country', 'latitude', 'longitude',
        'bedrooms', 'bathrooms', 'area_sqft', 'year_built',
        'is_featured', 'approved_by', 'rejected_reason'
    ];

    public function getApproved(int $page = 1, int $perPage = 12, array $filters = []): array
    {
        $where = ['status' => 'approved'];
        if (!empty($filters['category_id'])) $where['category_id'] = $filters['category_id'];
        if (!empty($filters['price_type'])) $where['price_type'] = $filters['price_type'];
        if (!empty($filters['city'])) $where['city'] = $filters['city'];
        if (!empty($filters['min_price'])) $where['price'] = ['price', '>=', $filters['min_price']];
        if (!empty($filters['max_price'])) $where['price'] = ['price', '<=', $filters['max_price']];
        if (!empty($filters['min_bedrooms'])) $where['bedrooms'] = ['bedrooms', '>=', $filters['min_bedrooms']];
        if (!empty($filters['search'])) return $this->search($filters, $page, $perPage);
        return $this->paginate($page, $perPage, $where);
    }

    private function search(array $f, int $page, int $perPage): array
    {
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), 50);
        $offset = ($page - 1) * $perPage;
        $cond = ["p.`status` = 'approved'"];
        $params = [];
        if (!empty($f['category_id'])) { $cond[] = "p.`category_id` = ?"; $params[] = $f['category_id']; }
        if (!empty($f['price_type'])) { $cond[] = "p.`price_type` = ?"; $params[] = $f['price_type']; }
        if (!empty($f['city'])) { $cond[] = "p.`city` = ?"; $params[] = $f['city']; }
        if (!empty($f['min_price'])) { $cond[] = "p.`price` >= ?"; $params[] = $f['min_price']; }
        if (!empty($f['max_price'])) { $cond[] = "p.`price` <= ?"; $params[] = $f['max_price']; }
        if (!empty($f['min_bedrooms'])) { $cond[] = "p.`bedrooms` >= ?"; $params[] = $f['min_bedrooms']; }
        if (!empty($f['search'])) { $s = "%{$f['search']}%"; $cond[] = "(p.`title` LIKE ? OR p.`description` LIKE ? OR p.`address` LIKE ?)"; $params[] = $s; $params[] = $s; $params[] = $s; }
        $where = 'WHERE ' . implode(' AND ', $cond);
        $total = (int)(Database::fetchOne("SELECT COUNT(*) c FROM `properties` p INNER JOIN `users` u ON p.`user_id`=u.`id` INNER JOIN `property_categories` c ON p.`category_id`=c.`id` {$where}", $params)['c'] ?? 0);
        $sql = "SELECT p.*, u.`name` agent_name, u.`email` agent_email, u.`phone` agent_phone, c.`name` cat_name, c.`slug` cat_slug, pi.`file_path` primary_image, pi.`file_name` primary_image_name FROM `properties` p INNER JOIN `users` u ON p.`user_id`=u.`id` INNER JOIN `property_categories` c ON p.`category_id`=c.`id` LEFT JOIN `property_images` pi ON pi.`property_id`=p.`id` AND pi.`is_primary`=1 {$where} ORDER BY p.`created_at` DESC LIMIT ? OFFSET ?";
        $params[] = $perPage; $params[] = $offset;
        $data = Database::fetchAll($sql, $params);
        return ['data' => $data, 'pagination' => ['current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int)ceil($total / $perPage)]];
    }

    public function findWithRelations(int $id): ?array
    {
        $p = Database::fetchOne("SELECT p.*, u.`name` agent_name, u.`email` agent_email, u.`phone` agent_phone, c.`name` cat_name, c.`slug` cat_slug FROM `properties` p INNER JOIN `users` u ON p.`user_id`=u.`id` INNER JOIN `property_categories` c ON p.`category_id`=c.`id` WHERE p.`id`=?", [$id]);
        if (!$p) return null;
        $p['images'] = Database::fetchAll("SELECT * FROM `property_images` WHERE `property_id`=? ORDER BY `display_order`", [$id]);
        return $p;
    }

    public function getImages(int $pid): array
    {
        return Database::fetchAll("SELECT * FROM `property_images` WHERE `property_id`=? ORDER BY `display_order`", [$pid]);
    }

    public function addImage(int $pid, array $d): int
    {
        return Database::insert("INSERT INTO `property_images` (`property_id`,`file_path`,`file_name`,`mime_type`,`file_size`,`is_primary`,`display_order`) VALUES (?,?,?,?,?,?,?)", [$pid, $d['file_path'], $d['file_name'], $d['mime_type'], $d['file_size'], $d['is_primary'] ?? 0, $d['display_order'] ?? 0]);
    }

    public function deleteImages(int $pid): int
    {
        return Database::execute("DELETE FROM `property_images` WHERE `property_id`=?", [$pid]);
    }

    public function setPrimaryImage(int $pid, int $imgId): int
    {
        Database::execute("UPDATE `property_images` SET `is_primary`=0 WHERE `property_id`=?", [$pid]);
        return Database::execute("UPDATE `property_images` SET `is_primary`=1 WHERE `id`=? AND `property_id`=?", [$imgId, $pid]);
    }

    public function incrementViews(int $id): void
    {
        Database::execute("UPDATE `properties` SET `views_count`=`views_count`+1 WHERE `id`=?", [$id]);
    }

    public function getByAgent(int $aid, int $page = 1, int $perPage = 12): array
    {
        return $this->paginate($page, $perPage, ['user_id' => $aid]);
    }

    public function getPending(int $page = 1, int $perPage = 12): array
    {
        return $this->paginate($page, $perPage, ['status' => 'pending']);
    }

    public function approve(int $id, int $adminId): int
    {
        return $this->update($id, ['status' => 'approved', 'approved_at' => date('Y-m-d H:i:s'), 'approved_by' => $adminId, 'rejected_reason' => null]);
    }

    public function reject(int $id, int $adminId, string $reason): int
    {
        return $this->update($id, ['status' => 'rejected', 'approved_by' => $adminId, 'rejected_reason' => $reason]);
    }

    public function getCities(): array
    {
        return array_column(Database::fetchAll("SELECT DISTINCT `city` FROM `properties` WHERE `status`='approved' ORDER BY `city`"), 'city');
    }

    public function getFeatured(int $limit = 6): array
    {
        return Database::fetchAll("SELECT p.*, u.`name` agent_name, c.`name` cat_name, pi.`file_path` primary_image FROM `properties` p INNER JOIN `users` u ON p.`user_id`=u.`id` INNER JOIN `property_categories` c ON p.`category_id`=c.`id` LEFT JOIN `property_images` pi ON pi.`property_id`=p.`id` AND pi.`is_primary`=1 WHERE p.`status`='approved' AND p.`is_featured`=1 ORDER BY p.`created_at` DESC LIMIT ?", [$limit]);
    }
}