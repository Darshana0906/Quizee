<?php
// TODO: Delete this file after use

require_once 'config/db.php';

// Security check - only run if you add a secret key
if (!isset($_GET['secret']) || $_GET['secret'] !== 'my_very_secret_key_coep_28') {
    die("Access denied");
}

$result = $conn->query("SELECT student_id, password FROM student WHERE LENGTH(password) < 60");

$count = 0;
while ($row = $result->fetch_assoc()) {
    $hashed = password_hash($row['password'], PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("UPDATE student SET password = ? WHERE student_id = ?");
    $stmt->bind_param("si", $hashed, $row['student_id']);
    $stmt->execute();
    
    $count++;
}

echo "✅ Done! $count passwords hashed successfully.<br>";
echo "Ab yeh file delete kar do security ke liye.";
?>