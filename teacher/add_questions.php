<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../config/db.php';

$error = "";
$success = "";
$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;
if ($_SERVER["REQUEST_METHOD"] == "POST"){
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
        if($statement->execute()){
            $success = "Questions added successfully";
        }
        else{
            $error = "Error Happened";
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
    <title>Document</title>
</head>
<body>
<h3>Add Questions</h3>
<p>Quiz ID: <?php echo $quiz_id; ?></p>

    <form method="POST">

    <label>Question:</label><br>
    <input type="text" name="question_text" required>
    <br><br>

    <label>Option 1:</label><br>
    <input type="text" name="option1" required>
    <br><br>

    <label>Option 2:</label><br>
    <input type="text" name="option2" required>
    <br><br>

    <label>Option 3:</label><br>
    <input type="text" name="option3" required>
    <br><br>

    <label>Option 4:</label><br>
    <input type="text" name="option4" required>
    <br><br>

    <label>Correct Option (1-4):</label><br>
    <input type="number" name="correct_option" min="1" max="4" required>
    <br><br>

    <button type="submit">Add Question</button>


</form>
</body>
</html>