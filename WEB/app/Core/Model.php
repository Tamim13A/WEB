<?php
/**
 * Base Model - Basic CRUD only
 * CIS324 Real Estate Portal
 */

abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];

    public function find(int|string $id): ?array
    {
        return Database::fetchOne("SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?", [$id]);
    }

    public function findBy(string $column, mixed $value): ?array
    {
        return Database::fetchOne("SELECT * FROM `{$this->table}` WHERE `{$column}` = ?", [$value]);
    }

    public function all(array $columns = ['*'], string $orderBy = 'created_at', string $direction = 'DESC'): array
    {
        $cols = implode(', ', array_map(fn($c) => "`{$c}`", $columns));
        return Database::fetchAll("SELECT {$cols} FROM `{$this->table}` ORDER BY `{$orderBy}` {$direction}");
    }

    public function create(array $data): int
    {
        $data = $this->filterFillable($data);
        unset($data[$this->primaryKey]);
        $cols = implode(', ', array_map(fn($c) => "`{$c}`", array_keys($data)));
        $ph = implode(', ', array_fill(0, count($data), '?'));
        return Database::insert("INSERT INTO `{$this->table}` ({$cols}) VALUES ({$ph})", array_values($data));
    }

    public function update(int|string $id, array $data): int
    {
        $data = $this->filterFillable($data);
        unset($data[$this->primaryKey]);
        if (empty($data)) return 0;
        $sets = implode(', ', array_map(fn($c) => "`{$c}` = ?", array_keys($data)));
        $params = array_values($data);
        $params[] = $id;
        return Database::execute("UPDATE `{$this->table}` SET {$sets} WHERE `{$this->primaryKey}` = ?", $params);
    }

    public function delete(int|string $id): int
    {
        return Database::execute("DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?", [$id]);
    }

    public function count(array $where = []): int
    {
        if (empty($where)) return (int)(Database::fetchOne("SELECT COUNT(*) as c FROM `{$this->table}`")['c'] ?? 0);
        $w = implode(' AND ', array_map(fn($c) => "`{$c}` = ?", array_keys($where)));
        return (int)(Database::fetchOne("SELECT COUNT(*) as c FROM `{$this->table}` WHERE {$w}", array_values($where))['c'] ?? 0);
    }

    public function exists(array $where): bool
    {
        return $this->count($where) > 0;
    }

    public function paginate(int $page = 1, int $perPage = 12, array $where = [], array $columns = ['*'], string $orderBy = 'created_at', string $direction = 'DESC'): array
    {
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), 50);
        $offset = ($page - 1) * $perPage;
        $whereSql = '';
        $params = [];
        if (!empty($where)) {
            $cond = [];
            foreach ($where as $col => $val) {
                if (is_array($val) && count($val) === 3) {
                    [$c, $op, $v] = $val;
                    $cond[] = "`{$c}` {$op} ?";
                    $params[] = $v;
                } else {
                    $cond[] = "`{$col}` = ?";
                    $params[] = $val;
                }
            }
            $whereSql = 'WHERE ' . implode(' AND ', $cond);
        }
        $cols = implode(', ', array_map(fn($c) => "`{$c}`", $columns));
        $total = (int)(Database::fetchOne("SELECT COUNT(*) as c FROM `{$this->table}` {$whereSql}", $params)['c'] ?? 0);
        $params[] = $perPage;
        $params[] = $offset;
        $data = Database::fetchAll("SELECT {$cols} FROM `{$this->table}` {$whereSql} ORDER BY `{$orderBy}` {$direction} LIMIT ? OFFSET ?", $params);
        return ['data' => $data, 'pagination' => ['current_page' => $page, 'per_page' => $perPage, 'total' => $total, 'last_page' => (int)ceil($total / $perPage)]];
    }

    protected function query(string $sql, array $params = []): array
    {
        return Database::fetchAll($sql, $params);
    }

    protected function queryOne(string $sql, array $params = []): ?array
    {
        return Database::fetchOne($sql, $params);
    }

    protected function command(string $sql, array $params = []): int
    {
        return Database::execute($sql, $params);
    }

    private function filterFillable(array $data): array
    {
        if (empty($this->fillable)) return $data;
        return array_intersect_key($data, array_flip($this->fillable));
    }
}