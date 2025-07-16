<?php

/**
 * Database Configuration
 * Centralized database settings for the Building Material Management System
 */

class DatabaseConfig
{
    // Database connection settings
    const DB_HOST = 'localhost';
    const DB_NAME = 'bmmss';
    const DB_USER = 'root';
    const DB_PASS = '';
    const DB_CHARSET = 'utf8mb4';

    // Connection options
    const DB_OPTIONS = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
}

/**
 * Database Connection Class
 * Handles database connections using PDO
 */
class Database
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        try {
            $dsn = "mysql:host=" . DatabaseConfig::DB_HOST .
                ";dbname=" . DatabaseConfig::DB_NAME .
                ";charset=" . DatabaseConfig::DB_CHARSET;

            $this->connection = new PDO(
                $dsn,
                DatabaseConfig::DB_USER,
                DatabaseConfig::DB_PASS,
                DatabaseConfig::DB_OPTIONS
            );
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Get database instance (Singleton pattern)
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get PDO connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Execute a query with parameters
     */
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Query execution failed: " . $e->getMessage());
        }
    }

    /**
     * Fetch all rows
     */
    public function fetchAll($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Fetch single row
     */
    public function fetchOne($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Get row count
     */
    public function rowCount($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit()
    {
        return $this->connection->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback()
    {
        return $this->connection->rollback();
    }
}
