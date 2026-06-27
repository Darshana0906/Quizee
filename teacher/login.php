<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once '../config/db.php';

$error = "";
$success = "";

//login here
if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if(empty($username) || empty($password)){
        $error = "Please Enter both Username and Password";
    }
    else{
        //database search if teacher exits or  not
        $statement = $conn->prepare(
            "SELECT * FROM teacher WHERE name = ?"
        );
        $statement ->bind_param("s", $username);
        $statement ->execute();
        $result = $statement->get_result();

        if($result->num_rows === 1){
            //teacher exists now check if password is correctt
            $row = $result->fetch_assoc();
            if(password_verify($password, $row['password'])){
               //login hoo gayi
               //save all info
                $_SESSION['user_id'] = $row['teacher_id'];
                $_SESSION['name'] = $row['name'];
                $_SESSION['role'] = 'teacher';

                //to dashboard
                $statement->close();
                header("Location: dashboard.php");
                exit();
            }
            else{
                $error = "Password incorrect";
                $statement->close();
            }
        }
        else{
        $error = " Username doesnt exist. Please contact admin";
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
    <title>Teacher Login</title>
    <link rel ="stylesheet" href = "../css/loginpage.css">

</head>
<body>
    <h3>Teacher Login</h3>

    <?php if(!empty($error)){ ?>        
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php }?>

    <form method="POST" action="login.php">

    <input type="text" name="username" placeholder="Username">
    <br><br>

    <input type="password" name="password" placeholder="Password">
    <br><br>

    <button type="submit" name="login">Login</button>

    </form>
    <p>Don't have an account?
        <a href="register.php">Register</a>
    </p>
    
</body>
</html>



