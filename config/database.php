<?php
// Database configuration for Nexoom Video Conferencing Platform

class Database {
    private $host = 'localhost';
    private $db_name = 'u765872199_nexoom_db';
    private $username = 'u765872199_patrickssajeev';
    private $password = 'qQ$TFUE]Q5?5';
    private $conn;

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
}

// Database connection instance
function getDB() {
    static $db = null;
    if ($db === null) {
        $database = new Database();
        $db = $database->getConnection();
    }
    return $db;
}

// Helper function to execute queries
function executeQuery($sql, $params = []) {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

// Helper function to fetch single row
function fetchOne($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetch();
}

// Helper function to fetch all rows
function fetchAll($sql, $params = []) {
    $stmt = executeQuery($sql, $params);
    return $stmt->fetchAll();
}

// Helper function to get last insert ID
function getLastInsertId() {
    $db = getDB();
    return $db->lastInsertId();
}

// Helper function to begin transaction
function beginTransaction() {
    $db = getDB();
    return $db->beginTransaction();
}

// Helper function to commit transaction
function commit() {
    $db = getDB();
    return $db->commit();
}

// Helper function to rollback transaction
function rollback() {
    $db = getDB();
    return $db->rollback();
}
?>
