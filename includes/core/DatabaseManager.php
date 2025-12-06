<?php

/**
 * Nyalife HMS - Database Manager
 *
 * A singleton class to manage database connections using .env configuration.
 */

class DatabaseManager
{
    private static ?\DatabaseManager $instance = null;
    
    private readonly \mysqli $connection;

    /**
     * Private constructor to prevent direct creation
     */
    private function __construct()
    {
        // Load environment variables if not already loaded
        if (!isset($_ENV['DB_HOST'])) {
            $this->loadEnv();
        }

        // Create the database connection using env variables
        $this->connection = new mysqli(
            $_ENV['DB_HOST'],
            $_ENV['DB_USER'],
            $_ENV['DB_PASS'],
            $_ENV['DB_NAME']
        );

        if ($this->connection->connect_error !== null && $this->connection->connect_error !== '' && $this->connection->connect_error !== '0') {
            throw new Exception("Connection failed: " . $this->connection->connect_error);
        }

        $this->connection->set_charset("utf8mb4");
    }

    /**
     * Loads the .env file if not already loaded
     */
    private function loadEnv(): void
    {
        $dotenvPath = dirname(__DIR__, 2); // Go up two levels to reach project root
        require_once $dotenvPath . '/vendor/autoload.php';

        $dotenv = Dotenv\Dotenv::createImmutable($dotenvPath);
        $dotenv->load();
    }

    /**
     * Get the singleton instance
     *
     * @return DatabaseManager The singleton instance
     */
    public static function getInstance(): DatabaseManager
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get the database connection
     *
     * @return mysqli The database connection
     */
    public function getConnection(): mysqli
    {
        return $this->connection;
    }

    /**
     * Close the connection when the object is destroyed
     */
    public function __destruct()
    {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
