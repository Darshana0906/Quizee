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
    $no_of_questions = trim($_POST["no_of_questions"]);
    $start_time = trim($_POST["start_time"]);
    $end_time = trim($_POST["end_time"]);

    if(empty($title) || empty($duration) || empty($no_of_questions)|| empty($start_time) || empty($end_time) ){
        $error = "Please Enter both title and duration";
    }
    elseif(!is_numeric($duration) || $duration <= 0){
        $error = "Please Enter valid duration(in Hours)";
    }
    else if(!is_numeric($no_of_questions) || $no_of_questions <= 0){
        $error = "Please Enter valid number of questions";
    }
    elseif($start_time > 12 || $end_time > 12){
        $error = "Invalid Start or end time";
    }
    else{
        $statement = $conn->prepare(
            "INSERT INTO quiz (title, duration, no_of_questions, teacher_id, start_time, end_time) 
            VALUES (?, ?, ?, ?, ?, ?)"
        );
        $statement ->bind_param("siiiii", $title, $duration,$no_of_questions, $teacher_id, $start_time, $end_time);
        
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

<?php if (!empty($error)) { ?>
    <p style="color:red;"><?php echo $error; ?></p>
<?php } ?>

<form method="POST">

    <label>Quiz Title:</label><br>
    <input type="text" name="title" required>
    <br><br>

    <label>Duration (Hours):</label><br>
    <input type="number" name="duration" required>
    <br><br>

    <label>Number of Questions:</label><br>
    <input type="number" name="no_of_questions" required>
    <br><br>
    
    <label>Start Time :</label><br>
    <input type="number" name="start_time" required>
    <select name="start_period">
    <option value="AM">AM</option>
    <option value="PM">PM</option>
    </select>
    <br><br>

    <label>End Time:</label><br>
    <input type="number" name="end_time" required>
    <select name="start_period">
    <option value="AM">AM</option>
    <option value="PM">PM</option>
    </select>
    <br><br>


    <button type="submit">Create Quiz</button>

</form>

<?php if (!empty($success)) { ?>
    <p style="color:green;"><?php echo $success; ?></p>

    <a href="add_questions.php?quiz_id=<?php echo $quiz_id; ?>">
        <button>Add Questions</button>
    </a>
<?php } ?>

</body>
</html>