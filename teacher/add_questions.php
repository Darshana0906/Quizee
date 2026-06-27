<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../config/db.php';
//session validation
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}
$error = "";
$success = "";
$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;
if ($quiz_id <= 0) {
    die("Invalid Quiz ID");
}
$teacher_id = $_SESSION['user_id'];
//step to confirm if this teacher has permission to handle this quiz
$statement = $conn->prepare("SELECT title,no_of_questions FROM quiz WHERE quiz_id = ? AND teacher_id = ?");
$statement->bind_param("ii", $quiz_id, $teacher_id);
$statement->execute();
$quiz = $statement->get_result()->fetch_assoc();

if (!$quiz) {
    die("Invalid quiz, or you don't have permission to edit this quiz.");
}
//check if quiz exists and get total number of questions
// $stmt = $conn->prepare("SELECT title, no_of_questions FROM quiz WHERE quiz_id = ?");
// $stmt->bind_param("i", $quiz_id);
// $stmt->execute();
// $quiz = $stmt->get_result()->fetch_assoc();
// $stmt->close();

// if (!$quiz) {
//     die("Quiz not found");
// }
$total_questions = (int)$quiz['no_of_questions'];
// Count existing questions
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM question WHERE quiz_id = ?");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$current_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

$is_complete = $current_count >= $total_questions;
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$is_complete){
    $question_text = trim($_POST["question_text"]);
    $option1 = trim($_POST["option1"]);
    $option2 = trim($_POST["option2"]);
    $option3 = trim($_POST["option3"]);
    $option4 = trim($_POST["option4"]);
    $correct_option = trim($_POST["correct_option"]);

    if(empty($question_text) || empty($option1) || empty($option2) || empty($option3) || empty($option4) || empty($correct_option)){
        $error = "Please Enter all information";
    }
    elseif(!is_numeric($correct_option) || $correct_option > 4 || $correct_option < 1){
        $error = "Correct option number should be between 1 to 4";
    }
    else{
        $statement = $conn->prepare(
            "INSERT INTO question (quiz_id,question_text,option1, option2, option3, option4, correct_option) 
            VALUES (?,?,?,?,?, ?, ?)");
        $statement->bind_param("isssssi",$quiz_id, $question_text,$option1,$option2,$option3,$option4, $correct_option);
        if ($statement->execute()) {
            $success = "Question " . ($current_count + 1) . " added successfully!";
            // Refresh count
            $current_count++;
            $is_complete = $current_count >= $total_questions;
        } else {
            $error = "Error adding question: " . $statement->error;
        }
        $statement->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Questions - <?= htmlspecialchars($quiz['title']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 40px auto; padding: 20px; }
        .card { border: 1px solid #ddd; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        input[type="text"], input[type="number"] { width: 100%; padding: 10px; margin: 8px 0; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 12px 24px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #0056b3; }
        .success { color: green; background: #d4edda; padding: 12px; border-radius: 4px; }
        .error { color: red; background: #f8d7da; padding: 12px; border-radius: 4px; }
        .progress { font-size: 18px; font-weight: bold; margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="card">
    <h2>Add Questions to Quiz: <?= htmlspecialchars($quiz['title']) ?></h2>
    <p class="progress">Question <?= $current_count + 1 ?> of <?= $total_questions ?></p>

    <?php if ($success): ?>
        <div class="success"><?= $success ?></div><br>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?= $error ?></div><br>
    <?php endif; ?>
    <?php if (!$is_complete): ?>
        <form method="POST">
            <label><strong>Question <?= $current_count + 1 ?>:</strong></label><br>
            <input type="text" name="question_text" required placeholder="Enter question text"><br><br>

            <label>Option 1:</label><br>
            <input type="text" name="option1" required><br><br>

            <label>Option 2:</label><br>
            <input type="text" name="option2" required><br><br>

            <label>Option 3:</label><br>
            <input type="text" name="option3" required><br><br>

            <label>Option 4:</label><br>
            <input type="text" name="option4" required><br><br>
            <label><strong>Correct Option (1-4):</strong></label><br>
            <input type="number" name="correct_option" min="1" max="4" required><br><br>

            <button type="submit">Add Question</button>
        </form>

    <?php else: ?>
        <div class="success">
            <h3> Quiz Created Successfully!</h3>
            <p>All <?= $total_questions ?> questions have been added.</p>
        </div>
        <br>
        <a href="view_quiz.php?quiz_id=<?= $quiz_id ?>">
            <button style="background: #28a745;">View Quiz</button>
        </a>
    <?php endif; ?>
    <?php if ($success && !$is_complete): ?>
        <br><br>
        <a href="add_question.php?quiz_id=<?= $quiz_id ?>">
            <button style="background: #28a745;">Add Next Question</button>
        </a>
    <?php endif; ?>

    <br><br>
    <a href="dashboard.php">← Back to Dashboard</a>
</div>
</body>
</html>