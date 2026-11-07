<?php
/**
 * PropertyCategory Model
 * CIS324 Real Estate Portal
 */

class PropertyCategory extends Model
{
    protected string $table = 'property_categories';
    protected array $fillable = ['name', 'slug', 'description', 'is_active'];
    protected array $casts = [
        'is_active' => 'bool',
    ];

    /**
     * Get all active categories
     */
    public function getActive(): array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `is_active` = 1 ORDER BY `name`";
        return Database::fetchAll($sql);
    }

    /**
     * Find by slug
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->findBy('slug', $slug);
    }

    /**
     * Get category with property count
     */
    public function getWithCount(): array
    {
        $sql = "
            SELECT c.*, COUNT(p.`id`) as `property_count`
            FROM `{$this->table}` c
            LEFT JOIN `properties` p ON c.`id` = p.`category_id` AND p.`status` = 'approved'
            WHERE c.`is_active` = 1
            GROUP BY c.`id`
            ORDER BY c.`name`
        ";
        return Database::fetchAll($sql);
    }

    /**
     * Create category
     */
    public function createCategory(array $data): int
    {
        $data['slug'] = $data['slug'] ?? strtolower(str_replace(' ', '-', $data['name']));
        return $this->create($data);
    }

    /**
     * Update category
     */
    public function updateCategory(int $id, array $data): int
    {
        if (isset($data['name']) && !isset($data['slug'])) {
            $data['slug'] = strtolower(str_replace(' ', '-', $data['name']));
        }
        return $this->update($id, $data);
    }

    /**
     * Toggle active status
     */
    public function toggleActive(int $id): int
    {
        $cat = $this->find($id);
        if (!$cat) {
            return 0;
        }
        return $this->update($id, ['is_active' => !$cat['is_active']]);
    }
}