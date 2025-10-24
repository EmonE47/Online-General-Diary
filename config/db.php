<?php
/**
 * Database Configuration
 * Online General Diary System
 * 
 * This file handles database connection using PDO
 * with proper error handling and security measures
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'online_gd_system';
    private $username = 'root';
    private $password = '';
    private $charset = 'utf8mb4';
    private $pdo;
    
    /**
     * Get database connection
     * @return PDO|null
     */
    public function getConnection() {
        $this->pdo = null;
        
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=" . $this->charset;
            error_log("Attempting database connection to: " . $this->host);
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
            error_log("Database connection successful");
            
        } catch(PDOException $exception) {
            error_log("Database connection error: " . $exception->getMessage());
            die("Database connection failed. Please check your configuration.");
        }
        
        return $this->pdo;
        
        return $this->pdo;
    }
    
    /**
     * Test database connection
     * @return bool
     */
    public function testConnection() {
        try {
            $pdo = $this->getConnection();
            $stmt = $pdo->query("SELECT 1");
            return true;
        } catch(PDOException $e) {
            return false;
        }
    }
}

// Global database instance
$database = new Database();
$pdo = $database->getConnection();

// Helper function to get PDO instance
function getDB() {
    global $pdo;
    return $pdo;
}

// Helper function to execute prepared statement
function executeQuery($sql, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch(PDOException $e) {
        error_log("Query execution error: " . $e->getMessage());
        return false;
    }
}

// Helper function to fetch single row
function fetchRow($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt ? $stmt->fetch() : false;
}

// Helper function to fetch all rows
function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt ? $stmt->fetchAll() : false;
}

// Helper function to get last insert ID
function getLastInsertId() {
    global $pdo;
    return $pdo->lastInsertId();
}

// Helper function to begin transaction
function beginTransaction() {
    global $pdo;
    return $pdo->beginTransaction();
}

// Helper function to commit transaction
function commitTransaction() {
    global $pdo;
    return $pdo->commit();
}

// Helper function to rollback transaction
function rollbackTransaction() {
    global $pdo;
    return $pdo->rollBack();
}
