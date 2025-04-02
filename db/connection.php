<?php
$host = 'localhost';
$db = 'autodesk';
$user = 'root';
$pass = '';
$port = 3307;


// try {
//     $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// } catch (PDOException $e) {
//     die("Database connection failed: " . $e->getMessage());
// }


try {
    // Include the correct port in the DSN
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

?>