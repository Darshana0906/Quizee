<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];
$question_id = isset($_GET['question_id']) ? (int)$_GET['question_id'] : 0;

// question fetch + ownership confirm ek saath
$statement = $conn->prepare("SELECT q.question_id, q.quiz_id, q.question_text, q.option1, q.option2, q.option3, q.option4, q.correct_option 
                              FROM question q 
                              INNER JOIN quiz z ON q.quiz_id = z.quiz_id 
                              WHERE q.question_id = ? AND z.teacher_id = ?");
$statement->bind_param("ii", $question_id, $teacher_id);
$statement->execute();
$question = $statement->get_result()->fetch_assoc();
$statement->close();

if (!$question) {
    die("Invalid question, or you don't have permission to edit it.");
}

$quiz_id = $question['quiz_id'];
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_text  = trim($_POST['question_text']);
    $option1 = trim($_POST['option1']);
    $option2 = trim($_POST['option2']);
    $option3 = trim($_POST['option3']);
    $option4 = trim($_POST['option4']);
    $correct_option = trim($_POST['correct_option']);

    if (empty($question_text) || empty($option1) || empty($option2) || empty($option3) || empty($option4) || empty($correct_option)) {
        $error = "Please fill all fields.";
    } elseif (!is_numeric($correct_option) || $correct_option < 1 || $correct_option > 4) {
        $error = "Correct option must be between 1 and 4.";
    } else {
        $update = $conn->prepare("UPDATE question SET question_text=?, option1=?, option2=?, option3=?, option4=?, correct_option=? WHERE question_id=?");
        $update->bind_param("sssssii", $question_text, $option1, $option2, $option3, $option4, $correct_option, $question_id);

        if ($update->execute()) {
            header("Location: manage_quiz.php?quiz_id=$quiz_id&question_updated=1");
            exit;
        } else {
            $error = "Database error: " . $update->error;
        }
        $update->close();
    }

    // error case mein typed values dikhao
    $question['question_text'] = $question_text;
    $question['option1'] = $option1;
    $question['option2'] = $option2;
    $question['option3'] = $option3;
    $question['option4'] = $option4;
    $question['correct_option'] = $correct_option;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Question</title>
<style>
    body { font-family: Arial, sans-serif; max-width: 600px; margin: 30px auto; }
    input { width: 100%; padding: 8px; margin: 6px 0; box-sizing: border-box; }
    .error { color: red; background: #f8d7da; padding: 10px; border-radius: 4px; }
</style>
</head>
<body>

<h2>Edit Question</h2>

<?php if (!empty($error)) { ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php } ?>

<form method="POST" action="edit_question.php?question_id=<?php echo $question_id; ?>">
    <label>Question:</label>
    <input type="text" name="question_text" value="<?php echo htmlspecialchars($question['question_text']); ?>" required>

    <label>Option 1:</label>
    <input type="text" name="option1" value="<?php echo htmlspecialchars($question['option1']); ?>" required>

    <label>Option 2:</label>
    <input type="text" name="option2" value="<?php echo htmlspecialchars($question['option2']); ?>" required>

    <label>Option 3:</label>
    <input type="text" name="option3" value="<?php echo htmlspecialchars($question['option3']); ?>" required>

    <label>Option 4:</label>
    <input type="text" name="option4" value="<?php echo htmlspecialchars($question['option4']); ?>" required>

    <label>Correct Option (1-4):</label>
    <input type="number" name="correct_option" min="1" max="4" value="<?php echo htmlspecialchars($question['correct_option']); ?>" required>

    <button type="submit">Save Changes</button>
</form>

<p><a href="manage_quiz.php?quiz_id=<?php echo $quiz_id; ?>">Back to Manage Quiz</a></p>

</body>
<div style="text-align:right;">
    <a href="../logout.php"><button>Logout</button></a>
</div>
</html>