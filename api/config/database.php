<?php
/**
 * Database Configuration for PHP API
 * Uses PDO PostgreSQL to connect to Google Cloud SQL
 */

class Database {
    private static $connection = null;
    
    /**
     * Get database connection (singleton pattern)
     */
    public static function getConnection() {
        if (self::$connection !== null) {
            return self::$connection;
        }
        
        try {
            $host = getenv('PGHOST') ?: '34.175.211.25';
            $port = getenv('PGPORT') ?: '5432';
            $database = getenv('PGDATABASE') ?: 'biomap';
            $user = getenv('PGUSER') ?: 'admin';
            $password = getenv('PGPASSWORD') ?: 'Passwordbd1!';
            $ssl = getenv('PGSSL') !== 'false';
            
            // Build DSN
            // Note: SSL is typically configured at the system level for PostgreSQL
            // For Railway/Cloud SQL, SSL should work automatically if configured
            $dsn = "pgsql:host=$host;port=$port;dbname=$database";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            self::$connection = new PDO($dsn, $user, $password, $options);
            
            return self::$connection;
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Erro ao conectar à base de dados.");
        }
    }
    
    /**
     * Execute a query and return all rows
     */
    public static function query($sql, $params = []) {
        try {
            $conn = self::getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage() . " | SQL: " . $sql);
            throw new Exception("Erro ao executar consulta.");
        }
    }
    
    /**
     * Execute a query and return a single row
     */
    public static function queryOne($sql, $params = []) {
        $result = self::query($sql, $params);
        return $result[0] ?? null;
    }
    
    /**
     * Execute an insert/update/delete and return affected rows
     */
    public static function execute($sql, $params = []) {
        try {
            $conn = self::getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Execute error: " . $e->getMessage() . " | SQL: " . $sql);
            throw new Exception("Erro ao executar operação.");
        }
    }
    
    /**
     * Execute an insert and return the inserted row
     * SQL should include RETURNING clause
     */
    public static function insert($sql, $params = []) {
        try {
            $conn = self::getConnection();
            // Check if RETURNING is already in the SQL
            if (stripos($sql, 'RETURNING') === false) {
                $sql .= ' RETURNING *';
            }
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Insert error: " . $e->getMessage() . " | SQL: " . $sql);
            throw new Exception("Erro ao inserir dados.");
        }
    }
    
    /**
     * Begin a transaction
     */
    public static function beginTransaction() {
        return self::getConnection()->beginTransaction();
    }
    
    /**
     * Commit a transaction
     */
    public static function commit() {
        return self::getConnection()->commit();
    }
    
    /**
     * Rollback a transaction
     */
    public static function rollback() {
        return self::getConnection()->rollBack();
    }
}
?>
