<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}
$teacher_id = $_SESSION['user_id'];

$statement = $conn->prepare("SELECT quiz_id, title, duration,no_of_questions,created_at from quiz where teacher_id = ? order by created_at DESC");
$statement->bind_param("i",$teacher_id);
$statement->execute();
$quizes = $statement->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
</head>
<body>
<h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
<button ><a href="quize_create.php">Create New Quiz</a></button>
<h3>Your Quizzes:</h3>
<?php if($quizes->num_rows === 0){ ?>
    <p>You have not created any quizzes yet.</p>
<?php } else { ?>
    <table cellpadding="8">
        <tr>
            <th>Quiz Name</th>
            <th>Duration(in mins)</th>
            <th>No of Questions</th>
            <th>Created on</th>
        </tr>
        <?php while($quiz = $quizes->fetch_assoc()){ ?>
            <tr>
                <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                <td><?php echo htmlspecialchars($quiz['duration']); ?></td>
                <td><?php echo htmlspecialchars($quiz['no_of_questions']); ?></td>
                <td><?php echo htmlspecialchars($quiz['created_at']); ?></td>
                <td>
                    <a href="manage_quiz.php?quiz_id=<?php echo $quiz['quiz_id'];?>">Manage</a>
                    <a href="enroll_students.php?quiz_id=<?php echo $quiz['quiz_id'];?>">Enroll Students</a>

                </td>     
            </tr>
            <?php } ?>
        </table>
    <?php } ?>  
    
</body>
<div style="text-align:right;">
    <a href="../logout.php"><button>Logout</button></a>
</div>
</html>