<?php
    error_reporting(E_ALL);
    ini_Set('display_errors',1);
    session_start();
    require_once '../config/db.php';
    $error = "";
    $success ="";
    //abhi agar register karke aaya hai student then success message mein hum kuch store karenge and display karenge else only form display hogs
    if(isset($_GET['registered']) && $_GET['registered'] == 1){
        $success = "Congratulations! You are now successgullt registed to Quizee!!";

    }
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        if(empty($username) || empty($password)){
            $error = "Please fill in both username and password";
        }
        else{
            $statement = $conn->prepare("SELECT * from student where name = ?");
           // $hashed_password = password_hash($password,PASSWORD_DEFAULT);
            $statement->bind_param("s", $username);
            $statement->execute();
            $result = $statement->get_result();
            if($result->num_rows === 1){
                $row = $result->fetch_assoc();
                if(password_verify($password,$row['password'])){
                    $_SESSION['user_id'] = $row['student_id'];
                    $_SESSION['username'] = $row['name'];
                    $_SESSION['role'] = 'student';
                    header("Location: dashboard.php");
                    exit;
                }else{
                    $error = "OOPS!Invalid Credentials.";
                }
               
            }
            else{
                $error = "OOPS!Invalid Credentials.";
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student - Login</title>
</head>
<body>
    <h2> Login and get Started </h2>
    <?php if(!empty($success)){ ?>
        <p style="color:green;"><?php echo htmlspecialchars($success); ?></p>
    <?php }?>
    <?php if(!empty($error)){ ?>        
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php }?>
    <form method='POST' action="login.php">
        <input type="text" name="username" placeholder="Username" required><br><br>
        <input type="password" name="password" placeholder="Password" requied><br><br>
        <button type="submit" name="login">Login</button>
    </form> 
    <p> Do not have an account? <a href="register.php">Register</a></p>
</body>
</html>