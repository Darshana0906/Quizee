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
$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;

if ($quiz_id <= 0) {
    die("Invalid Quiz ID");
}

// ownership confirm + quiz details fetch
$statement = $conn->prepare("SELECT title, duration, no_of_questions FROM quiz WHERE quiz_id = ? AND teacher_id = ?");
$statement->bind_param("ii", $quiz_id, $teacher_id);
$statement->execute();
$quiz = $statement->get_result()->fetch_assoc();
$statement->close();

if (!$quiz) {
    die("Invalid quiz, or you don't have permission to manage this quiz.");
}

$error = "";
$success = "";

// quiz details update (title/duration/num_questions)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_quiz'])) {
    $quiz_name     = trim($_POST['title']);
    $duration      = trim($_POST['duration']);
    $num_questions = trim($_POST['no_of_questions']);

    $count_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM question WHERE quiz_id = ?");
    $count_stmt->bind_param("i", $quiz_id);
    $count_stmt->execute();
    $current_count = $count_stmt->get_result()->fetch_assoc()['total'];
    $count_stmt->close();

    if (empty($quiz_name) || empty($duration) || empty($num_questions)) {
        $error = "Please fill all fields.";
    } elseif (!is_numeric($duration) || !is_numeric($num_questions)) {
        $error = "Duration and number of questions must be numbers.";
    } elseif ((int)$num_questions < $current_count) {
        $error = "You currently have $current_count questions. Delete some first before reducing the count below that.";
    } else {
        $update_stmt = $conn->prepare("UPDATE quiz SET title = ?, duration = ?, no_of_questions = ? WHERE quiz_id = ? AND teacher_id = ?");
        $update_stmt->bind_param("siiii", $quiz_name, $duration, $num_questions, $quiz_id, $teacher_id);

        if ($update_stmt->execute()) {
            header("Location: manage_quiz.php?quiz_id=$quiz_id&updated=1");
            exit;
        } else {
            $error = "Database error: " . $update_stmt->error;
        }
        $update_stmt->close();
    }

    // error ke case mein form mein wahi values dikhao jo type ki thi
    $quiz['title'] = $quiz_name;
    $quiz['duration'] = $duration;
    $quiz['no_of_questions'] = $num_questions;
}

if (isset($_GET['updated'])) { $success = "Quiz details updated successfully!"; }
if (isset($_GET['deleted'])) { $success = "Question deleted successfully!"; }
if (isset($_GET['question_updated'])) { $success = "Question updated successfully!"; }

// saare questions fetch karo is quiz ke
$q_stmt = $conn->prepare("SELECT question_id, question_text, option1, option2, option3, option4, correct_option FROM question WHERE quiz_id = ? ORDER BY question_id ASC");
$q_stmt->bind_param("i", $quiz_id);
$q_stmt->execute();
$questions = $q_stmt->get_result();
$question_count = $questions->num_rows;
$q_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Quiz</title>
<style>
    body { font-family: Arial, sans-serif; max-width: 800px; margin: 30px auto; }
    .card { border: 1px solid #ddd; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
    input { width: 100%; padding: 8px; margin: 6px 0; box-sizing: border-box; }
    .success { color: green; background: #d4edda; padding: 10px; border-radius: 4px; }
    .error { color: red; background: #f8d7da; padding: 10px; border-radius: 4px; }
    table { width: 100%; border-collapse: collapse; }
    td, th { border: 1px solid #ddd; padding: 8px; text-align: left; }
    .correct { color: green; font-weight: bold; }
</style>
</head>
<body>

<h2>Manage Quiz</h2>

<?php if (!empty($success)) { ?><p class="success"><?php echo htmlspecialchars($success); ?></p><?php } ?>
<?php if (!empty($error)) { ?><p class="error"><?php echo htmlspecialchars($error); ?></p><?php } ?>

<div class="card">
    <h3>Quiz Details</h3>
    <form method="POST" action="manage_quiz.php?quiz_id=<?php echo $quiz_id; ?>">
        <label>Quiz Name:</label>
        <input type="text" name="title" value="<?php echo htmlspecialchars($quiz['title']); ?>" required>

        <label>Duration (minutes):</label>
        <input type="number" name="duration" value="<?php echo htmlspecialchars($quiz['duration']); ?>" required>

        <label>Number of Questions:</label>
        <input type="number" name="no_of_questions" value="<?php echo htmlspecialchars($quiz['no_of_questions']); ?>" required>

        <button type="submit" name="update_quiz">Save Changes</button>
    </form>
</div>

<div class="card">
    <h3>Questions (<?php echo $question_count; ?> of <?php echo $quiz['no_of_questions']; ?> added)</h3>

    <?php if ($question_count === 0) { ?>
        <p>No questions added yet.</p>
    <?php } else { ?>
        <table>
            <tr><th>#</th><th>Question</th><th>Correct Option</th><th>Actions</th></tr>
            <?php $i = 1; while ($q = $questions->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo htmlspecialchars($q['question_text']); ?></td>
                <td class="correct">Option <?php echo $q['correct_option']; ?></td>
                <td>
                    <a href="edit_question.php?question_id=<?php echo $q['question_id']; ?>">Edit</a> |
                    <a href="delete_question.php?question_id=<?php echo $q['question_id']; ?>&quiz_id=<?php echo $quiz_id; ?>"
                       onclick="return confirm('Delete this question?');">Delete</a>
                </td>
            </tr>
            <?php } ?>
        </table>
    <?php } ?>

    <?php if ($question_count < $quiz['no_of_questions']) { ?>
        <br>
        <a href="add_questions.php?quiz_id=<?php echo $quiz_id; ?>"><button>Add More Questions</button></a>
    <?php } ?>
</div>

<a href="dashboard.php">← Back to Dashboard</a>

</body>
</html>