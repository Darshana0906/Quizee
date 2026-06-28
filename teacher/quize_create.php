<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../config/db.php';

$error = "";
$success = "";
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}
$teacher_id = $_SESSION["user_id"];
if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $title = trim($_POST["title"]);
    $duration = trim($_POST["duration"]);
    $no_of_questions = trim($_POST["no_of_questions"]);
    $start_time_temp = trim($_POST["start_time"]);
    $end_time_temp = trim($_POST["end_time"]);
    $start_time = date('Y-m-d H:i:s', strtotime($start_time_temp));
    $end_time   = date('Y-m-d H:i:s', strtotime($end_time_temp));
    // echo "Raw start: $start_time_temp | Raw end: $end_time_temp <br>";
    // echo "Converted start: $start_time | Converted end: $end_time <br>";
    // exit; 
    if(empty($title) || empty($duration) || empty($no_of_questions)|| empty($start_time) || empty($end_time) ){
        $error = "Please Enter both title and duration";
    }
    elseif(!is_numeric($duration) || $duration <= 0){
        $error = "Please Enter valid duration(in minutes)";
    }
    else if(!is_numeric($no_of_questions) || $no_of_questions <= 0){
        $error = "Please Enter valid number of questions";
    }
    else if ($end_time <= $start_time) {
        $error = "End time must be after start time.";
    } 
    else{
        $statement = $conn->prepare(
            "INSERT INTO quiz (title, duration, no_of_questions, teacher_id, start_time, end_time) 
            VALUES (?, ?, ?, ?, ?, ?)"
        );
        $statement ->bind_param("siiiss", $title, $duration,$no_of_questions, $teacher_id, $start_time, $end_time);
        
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

    <label>Duration (minutes):</label><br>
    <input type="number" name="duration" required>
    <br><br>

    <label>Number of Questions:</label><br>
    <input type="number" name="no_of_questions" required>
    <br><br>
    
    <label>Start Time :</label><br>
    <input type="datetime-local" name="start_time" required>
  
   
    <br><br>

    <label>End Time:</label><br>
    <input type="datetime-local" name="end_time" required>
   
   

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
<div style="text-align:right;">
    <a href="../logout.php"><button>Logout</button></a>
</div>
</html>