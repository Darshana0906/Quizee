<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];
$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

$statement = $conn->prepare("SELECT title, duration, no_of_questions FROM quiz WHERE quiz_id = ? AND teacher_id = ?");
$statement->bind_param("ii", $quiz_id, $teacher_id);
$statement->execute();
$quiz = $statement->get_result()->fetch_assoc();

if (!$quiz) {
    die("Quiz not found.");
}

$q_statement = $conn->prepare("SELECT question_text, option1, option2, option3, option4, correct_option FROM question WHERE quiz_id = ?");
$q_statement->bind_param("i", $quiz_id);
$q_statement->execute();
$questions = $q_statement->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>View Quiz</title>
<style>
    body { font-family: Arial, sans-serif; max-width: 700px; margin: 30px auto; }
    .question-card { border: 1px solid #ccc; border-radius: 8px; padding: 15px; margin-bottom: 20px; }
    .option { padding: 6px; margin: 4px 0; border-radius: 4px; }
    .correct { background-color: #d4f8d4; font-weight: bold; }
</style>
</head>
<body>

<h2><?php echo htmlspecialchars($quiz['title']); ?></h2>
<p>Duration: <?php echo htmlspecialchars($quiz['duration']); ?> minutes | Total Questions: <?php echo htmlspecialchars($quiz['no_of_questions']); ?></p>

<?php
$qnum = 1;
while ($q = $questions->fetch_assoc()) {
    $options = [1 => $q['option1'], 2 => $q['option2'], 3 => $q['option3'], 4 => $q['option4']];
?>
    <div class="question-card">
        <p><strong>Q<?php echo $qnum; ?>. <?php echo htmlspecialchars($q['question_text']); ?></strong></p>
        <?php foreach ($options as $num => $text) {
            $class = ($num == $q['correct_option']) ? "option correct" : "option";
        ?>
            <div class="<?php echo $class; ?>"><?php echo $num; ?>. <?php echo htmlspecialchars($text); ?></div>
        <?php } ?>
    </div>
<?php
    $qnum++;
}
?>

<a href="dashboard.php"><button>Back to Dashboard</button></a>

</body>
</html>