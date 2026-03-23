<?php

// DatabaseConnection is a singleton that manage the connection to the database
// it's a singleton because we only want one connection to the database
class DatabaseConnection {
    // ATTRIBUTES
    // Contain the unique instance of the PDO connection to the database
    private static ?PDO $pdo = null;

    // CONSTRUCTOR
    // Private constructor to prevent instantiation of the class
    private function __construct() {}

    // GETTERS
    // Static method to get the PDO connection to the database
    // If the connection does not exist yet, it creates it and returns it
    public static function getInstance() : PDO {
        if (self::$pdo == null) {
            // Path to the SQLite database file
            $dbPath = __DIR__ . "/../../database/theSimsDen.sqli";

            // Create the PDO object to connect to the SQLite database
            // We only need the dsn to connect to a SQLite database, because there is no username and password
            self::$pdo = new PDO("sqlite:" . $dbPath);

            // Set the error mode to exceptions (if not set, PDO will fail withou saying anything)
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Set the default fetch mode to associative array (so that we can access the columns by their name)
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // Enable foreign key constraints (SQLite does not enable them by default)
            // if we don't enable it, we can delete a game that has a reference and the reference will not be deleted for example
            self::$pdo->exec("PRAGMA foreign_keys = ON;");
        }
        // Return the PDO connection to the database
        return self::$pdo;
    }
}