<?php
$servername = "127.0.0.1";
$username = "darshana99";
$password = "password";
$dbname = "QUIZ";

$conn = mysqli_connect(
    $servername,
    $username,
    $password,
    $dbname
);
if(!$conn){
    die("Sorry connection failed:". mysqli_connect_error());

}
// else{
//     echo "Connected successfully";
// }
?>