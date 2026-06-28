<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../config/db.php';

$error = "";
$success = "";

$quiz_id = isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0;
if ($quiz_id <= 0) {
    die("Invalid Quiz ID");
}

$statement = $conn->prepare(
    "SELECT student_id, name, MIS FROM student"
);
$statement->execute();
$result = $statement->get_result();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['students'])) {
        $error = "Select at least one student";
    }
    else{
        $students = $_POST['students'];
        //loop 
        foreach($students as $student_id){
            $statement = $conn->prepare(
            "INSERT INTO quiz_enrollments(quiz_id, student_id)
            VALUES (?, ?)"
            );
            $statement ->bind_param("ii", $quiz_id, $student_id);
            $statement ->execute();
            $statement->close();
        }
       header("Location: dashboard.php");
       exit();
    }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll Students</title>
</head>
<body>

<h2>Enroll Students</h2>

<p>Quiz ID: <?php echo $quiz_id; ?></p>

<?php if(!empty($error)){ ?>
    <p style="color:red;"><?php echo $error; ?></p>
<?php } ?>

<?php if(!empty($success)){ ?>
    <p style="color:green;"><?php echo $success; ?></p>
<?php } ?>

<form method="POST">

    <?php while($row = $result->fetch_assoc()){ ?>

        <label>
            <input
                type="checkbox"
                name="students[]"
                value="<?php echo $row['student_id']; ?>">

            <?php echo $row['name']; ?>
            (<?php echo $row['MIS']; ?>)
        </label>

        <br><br>

    <?php } ?>

    <button type="submit">Enroll Selected Students</button>

</form>

</body>
</html>