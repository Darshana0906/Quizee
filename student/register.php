<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    require_once '../config/db.php';
    $error = "";
    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username']);
        $mis = trim($_POST['mis']);
        $branch = ($_POST['branch']);
        $division = trim($_POST['division']);
        $email = trim($_POST['email']);
        $password = ($_POST['password']);
        $university = trim($_POST['university']);
        if(empty($username) || empty($mis) || empty($branch) || empty($division) || empty($email) || empty($password) || empty($university)){
            $error = "Please fill in all the credentials";
        }
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid format of email...Please reenter";
        }
        else{
            $statement = $conn->prepare("SELECT student_id from student where name  = ? OR email = ? OR mis = ?");
            $statement->bind_param("sss",$username,$email,$mis);
            $statement->execute();
            $answer = $statement->get_result();
            if($answer->num_rows > 0){
                $error = "OOPs! User already exists";
            }
            else{
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $statement = $conn->prepare("INSERT INTO student (name,mis,branch,division,email,password,university) VALUES (?,?,?,?,?,?,?)");
                $statement->bind_param("sssssss",$username,$mis,$branch,$division,$email,$hashed_password,$university);
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
</head>
<body>
    <h2>Welcome, Register yourself at Quizee</h2>
    <?php if(!empty($error)){ ?>
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php }?>
    <form method="POST" action="register.php">
    <input type="text" name="username" placeholder="Username" required><br><br>
    <input type="text" name="mis" placeholder="MIS" required><br><br>
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <select name="branch" required> 
        <option value="Computer Engineering">Computer Engineering</option>
        <option value="Electronics and Telecommunication Engineering">Electronics and Telecommunication Engineering</option>
        <option value="Mechanical Engineering">Mechanical Engineering</option>
        <option value="Civil Engineering">Civil Engineering</option>
        <option value="Electrical Engineering">Electrical Engineering</option>    
        <option value="AIML">AIML</option>
        <option value="Instrumentation and Control Engineering">Instrumentation and Control Engineering</option>
        <option value="Manufacturing Science and Engineering">Manufacturing Science and Engineering</option>
        <option value="Metallurgy and Materials Technology">Metallurgy and Materials Technology</option>
    </select><br><br>
    <input type="text" name="division" placeholder="Division" required><br><br>
    <input type="text" name="university" placeholder="University" required><br><br>
    <button type="submit" name="register">Register</button>
    </form>
    <p> Already have an account? <a href="login.php">Login here</a></p>
</body>
</html>