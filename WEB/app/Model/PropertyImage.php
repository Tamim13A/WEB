<?php
/**
 * PropertyImage Model
 * CIS324 Real Estate Portal
 */

class PropertyImage extends Model
{
    protected string $table = 'property_images';
    protected array $fillable = [
        'property_id', 'file_path', 'file_name', 'mime_type', 
        'file_size', 'is_primary', 'display_order'
    ];
    protected array $casts = [
        'property_id' => 'int',
        'file_size' => 'int',
        'is_primary' => 'bool',
        'display_order' => 'int',
    ];

    /**
     * Get images for property
     */
    public function getByProperty(int $propertyId): array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `property_id` = ? ORDER BY `display_order`, `id`";
        return Database::fetchAll($sql, [$propertyId]);
    }

    /**
     * Get primary image
     */
    public function getPrimary(int $propertyId): ?array
    {
        $sql = "SELECT * FROM `{$this->table}` WHERE `property_id` = ? AND `is_primary` = 1 LIMIT 1";
        return Database::fetchOne($sql, [$propertyId]);
    }

    /**
     * Add image
     */
    public function add(int $propertyId, array $data): int
    {
        $data['property_id'] = $propertyId;
        return $this->create($data);
    }

    /**
     * Set as primary
     */
    public function setPrimary(int $imageId, int $propertyId): int
    {
        // Reset all
        Database::execute("UPDATE `{$this->table}` SET `is_primary` = 0 WHERE `property_id` = ?", [$propertyId]);
        // Set new primary
        return $this->update($imageId, ['is_primary' => 1]);
    }

    /**
     * Delete image
     */
    public function deleteImage(int $id, int $propertyId): int
    {
        // Verify ownership
        $image = $this->find($id);
        if (!$image || $image['property_id'] != $propertyId) {
            return 0;
        }
        return $this->delete($id);
    }

    /**
     * Update display order
     */
    public function updateOrder(int $propertyId, array $orderedIds): int
    {
        $count = 0;
        foreach ($orderedIds as $index => $id) {
            $count += $this->update($id, ['display_order' => $index]);
        }
        return $count;
    }

    /**
     * Count images for property
     */
    public function countByProperty(int $propertyId): int
    {
        $sql = "SELECT COUNT(*) as total FROM `{$this->table}` WHERE `property_id` = ?";
        $result = Database::fetchOne($sql, [$propertyId]);
        return $result['total'] ?? 0;
    }
}