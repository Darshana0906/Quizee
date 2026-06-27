<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../config/db.php';

$error = "";
$success = "";
$quiz_id = null;

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $title = trim($_POST["title"]);
    $duration = trim($_POST["duration"]);
    $teacher_id = $_SESSION["teacher_id"];

    if(empty($title) || empty($duration)){
        $error = "Please Enter both title and duration";
    }
    elseif(!is_numeric($duration) || $duration <= 0){
        $error = "Please Enter valid duration(in Hours)";
    }
    else{
        $statement = $conn->prepare(
            "INSERT INTO quiz (title, duration, teacher_id) 
            VALUES (?, ?, ?)"
        );
        $statement ->bind_param("sii", $title, $duration, $teacher_id);
        
        if($statement ->execute()){
            $quiz_id = $conn->insert_id;
            $success = "Quiz Created successfully";
            $statement->close();
        }
        else{
            $error = "Error".$statement->error;
            $statement->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quiz</title>
</head>
<body>

<h3>Create Quiz</h3>

<!-- ERROR MESSAGE -->
<?php if (!empty($error)) { ?>
    <p style="color:red;"><?php echo $error; ?></p>
<?php } ?>

<!-- SUCCESS MESSAGE + BUTTON -->
<?php if (!empty($success)) { ?>
    <p style="color:green;"><?php echo $success; ?></p>

    <a href="add_questions.php?quiz_id=<?php echo $quiz_id; ?>">
        <button>Add Questions</button>
    </a>
<?php } ?>

<!-- FORM -->
<form method="POST">

    <label>Quiz Title:</label><br>
    <input type="text" name="title" required>
    <br><br>

    <label>Duration (Hours):</label><br>
    <input type="number" name="duration" required>
    <br><br>

    <button type="submit">Create Quiz</button>

</form>

</body>
</html>