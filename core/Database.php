<?php

namespace Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $connection;

    public static function getConnection(): PDO
    {
        if (!isset(self::$connection)) {
            try {
                $host = $GLOBALS['database']['host'];
                $db = $GLOBALS['database']['name'];
                $user = $GLOBALS['database']['user'];
                $pass = $GLOBALS['database']['password'];

                $dsn = "mysql:host=$host;dbname=$db";
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false, // TODO: Check if necessary
                ];

                self::$connection = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                throw new RuntimeException('Database connection failed: ' . $e->getMessage());
            }
        }

        return self::$connection;
    }
}
