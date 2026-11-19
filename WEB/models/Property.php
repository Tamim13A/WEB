<?php
// models/Property.php - Data Model for Property Entity (MVC Architecture)
class Property {
    public static function all(PDO $pdo, string $search = '', string $type = ''): array {
        $sql = "SELECT p.*, u.name AS agent_name FROM properties p JOIN users u ON p.user_id = u.id WHERE 1=1";
        $params = [];
        if (!empty($search)) {
            $sql .= " AND (p.title LIKE ? OR p.city LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($type === 'sale' || $type === 'rent') {
            $sql .= " AND p.price_type = ?";
            $params[] = $type;
        }
        $sql .= " ORDER BY p.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function find(PDO $pdo, int $id): ?array {
        $stmt = $pdo->prepare("SELECT p.*, u.name AS agent_name, u.email AS agent_email FROM properties p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
        $stmt->execute([$id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }
}

