<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    require_once '../config/db.php';
    $error = "";
    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username']);
        $department = trim($_POST['department']);
        $email = trim($_POST['email']);
        $password = ($_POST['password']);
        $university = trim($_POST['university']);
        if(empty($username) || empty($department) || empty($email) || empty($password) || empty($university)){
            $error = "Please fill in all the credentials";
        }
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid format of email...Please reenter";
        }
        else{
            $statement = $conn->prepare("SELECT teacher_id from teacher where name  = ? OR email = ?");
            $statement->bind_param("ss",$username,$email);
            $statement->execute();
            $answer = $statement->get_result();
            if($answer->num_rows > 0){
                $error = "OOPs! User already exists";
            }
            else{
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $statement = $conn->prepare("INSERT INTO teacher (name,department,email,password,university) VALUES (?,?,?,?,?)");
                $statement->bind_param("sssss",$username,$department,$email,$hashed_password,$university);
                if($statement->execute()){
                    header("Location: login.php?registered=1");
                    exit;
                }
                else{
                    $error = "Database error : " . $statement->error;
                }
            }
        }
        
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel ="stylesheet" href ="../css/loginpage.css">
</head>
<body>
    <div class = "login">
        <h3>Welcome, Register yourself at Quizee</h3>
        <?php if(!empty($error)){ ?>
            <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
        <?php }?>
        <form method="POST" action="register.php">
        <input type="text" name="username" placeholder="Username" required><br><br>
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <input type="text" name="department" placeholder="Department" required><br><br>
        <input type="text" name="university" placeholder="University" required><br><br>
        <button type="submit" name="register">Register</button>
        </form>
        <p> Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>