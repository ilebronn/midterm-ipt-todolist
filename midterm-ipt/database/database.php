<?php

require_once('db_credentials.php'); // getting credentials from file db_credentials.php

function db_connect() { // function to connect your form to your database
    try {
        $dsn = "mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME . ";charset=utf8mb4"; // Data Source Name
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Enable exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Default fetch mode
            PDO::ATTR_EMULATE_PREPARES => false, // Disable emulation of prepared statements
        ];
        $connection = new PDO($dsn, DB_USER, DB_PASS, $options); // Connecting to the database using PDO
        return $connection;
    } catch (PDOException $e) {
        exit("Database connection failed: " . $e->getMessage());
    }
}

function db_disconnect($connection) { // function to disconnect the form from the database
    // PDO does not need to explicitly close the connection,
    // It will close automatically when there are no references to it.
    $connection = null; // Set the connection to null to help with garbage collection
}

function confirm_result_set($result_set) {  // check query
    if (!$result_set) {
        exit("Database query failed.");
    }
}

?>
