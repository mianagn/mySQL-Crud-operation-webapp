<?php
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'student_2519');
define('DB_USER', 'student_2519');
define('DB_PASS', 'pass2519'); // same password you type in PuTTY

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    
    if ($conn->connect_error) {
        die("Σφάλμα σύνδεσης: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Σφάλμα σύνδεσης με τη βάση δεδομένων: " . $e->getMessage());
}
?>

