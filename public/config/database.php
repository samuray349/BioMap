<?php
/**
 * Database Configuration and Connection
 * Uses PDO for PostgreSQL connection to Google Cloud SQL
 */

// Database configuration
// In production, these should be set via environment variables or a secure config file
define('DB_HOST', getenv('PGHOST') ?: '34.175.211.25');
define('DB_PORT', getenv('PGPORT') ?: '5432');
define('DB_NAME', getenv('PGDATABASE') ?: 'biomap');
define('DB_USER', getenv('PGUSER') ?: 'admin');
define('DB_PASS', getenv('PGPASSWORD') ?: 'Passwordbd1!');
define('DB_SSL', getenv('PGSSL') !== 'false');

/**
 * Get PDO database connection
 * @return PDO Database connection instance
 * @throws PDOException If connection fails
 */
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = sprintf(
                'pgsql:host=%s;port=%s;dbname=%s',
                DB_HOST,
                DB_PORT,
                DB_NAME
            );
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false, // Don't use persistent connections
            ];
            
            // Add SSL to DSN if enabled
            if (DB_SSL) {
                $dsn .= ';sslmode=require';
            }
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            throw new PDOException('Database connection failed: ' . $e->getMessage(), (int)$e->getCode());
        }
    }
    
    return $pdo;
}

/**
 * Execute a prepared query and return results
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters to bind
 * @return array Query results
 */
function executeQuery($sql, $params = []) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Query execution error: ' . $e->getMessage() . ' | SQL: ' . $sql);
        throw $e;
    }
}

/**
 * Execute a prepared query and return a single row
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters to bind
 * @return array|null Single row or null if not found
 */
function executeQuerySingle($sql, $params = []) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ? $result : null;
    } catch (PDOException $e) {
        error_log('Query execution error: ' . $e->getMessage() . ' | SQL: ' . $sql);
        throw $e;
    }
}

/**
 * Execute an INSERT/UPDATE/DELETE query and return affected rows
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters to bind
 * @return int Number of affected rows
 */
function executeUpdate($sql, $params = []) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log('Update execution error: ' . $e->getMessage() . ' | SQL: ' . $sql);
        throw $e;
    }
}

/**
 * Execute an INSERT query with RETURNING clause and return the ID
 * @param string $sql SQL query with RETURNING clause (e.g., "INSERT INTO ... RETURNING id")
 * @param array $params Parameters to bind
 * @return string|int Last insert ID from RETURNING clause
 */
function executeInsert($sql, $params = []) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        // Get the first column value (the ID from RETURNING clause)
        return $result ? reset($result) : null;
    } catch (PDOException $e) {
        error_log('Insert execution error: ' . $e->getMessage() . ' | SQL: ' . $sql);
        throw $e;
    }
}
