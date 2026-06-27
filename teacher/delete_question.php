<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];
$question_id = isset($_GET['question_id']) ? (int)$_GET['question_id'] : 0;
$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

// confirm yeh question isi teacher ki quiz ka hai (JOIN se ownership check)
$check = $conn->prepare("SELECT q.question_id FROM question q 
                          INNER JOIN quiz z ON q.quiz_id = z.quiz_id 
                          WHERE q.question_id = ? AND z.teacher_id = ?");
$check->bind_param("ii", $question_id, $teacher_id);
$check->execute();
$found = $check->get_result()->fetch_assoc();
$check->close();

if (!$found) {
    die("Invalid request.");
}

$delete = $conn->prepare("DELETE FROM question WHERE question_id = ?");
$delete->bind_param("i", $question_id);
$delete->execute();
$delete->close();

header("Location: manage_quiz.php?quiz_id=$quiz_id&deleted=1");
exit;
?>