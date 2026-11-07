<?php
/**
 * Database Connection (Singleton PDO)
 * CIS324 Real Estate Portal
 */

class Database
{
    private static ?PDO $instance = null;
    private static array $config = [];

    private function __construct() {}

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$config = require __DIR__ . '/../Config/Database.php';
            
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                self::$config['host'],
                self::$config['port'],
                self::$config['database'],
                self::$config['charset']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    self::$config['username'],
                    self::$config['password'],
                    self::$config['options']
                );
            } catch (PDOException $e) {
                if (self::$config['debug'] ?? false) {
                    throw new Exception('Database connection failed: ' . $e->getMessage());
                }
                throw new Exception('Database connection failed. Please check configuration.');
            }
        }
        return self::$instance;
    }

    /**
     * Execute a SELECT query and return all results
     */
    public static function fetchAll(string $sql, array $params = []): array
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Execute a SELECT query and return single result
     */
    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Execute INSERT query and return last insert ID
     */
    public static function insert(string $sql, array $params = []): int
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return (int)self::getInstance()->lastInsertId();
    }

    /**
     * Execute UPDATE/DELETE query and return affected rows
     */
    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Begin transaction
     */
    public static function beginTransaction(): void
    {
        self::getInstance()->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public static function commit(): void
    {
        self::getInstance()->commit();
    }

    /**
     * Rollback transaction
     */
    public static function rollback(): void
    {
        self::getInstance()->rollBack();
    }

    /**
     * Get PDO instance for advanced usage
     */
    public static function getPdo(): PDO
    {
        return self::getInstance();
    }

    /**
     * Close connection (for testing)
     */
    public static function close(): void
    {
        self::$instance = null;
    }
}