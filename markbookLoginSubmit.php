<!DOCTYPE html>
<?php
session_start();
?>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Login Check Results</title>
    </head>
    <body>
        <h1>Login Check</h1>

        <?php
        include('markbookConnect.php');
            $username = $_GET['username'];
            $password = $_GET['pass'];
            $role = $_GET['role']; 

            if ($role === 'teacher') {
                $sql = "SELECT * FROM teacher WHERE teacher_user = '$username' AND teacher_pass = '$password'";
            } elseif ($role === 'student') {
                $sql = "SELECT * FROM student WHERE student_user = '$username' AND student_pass = '$password'";
            } else {
                header("Location: markbookLogin.php?error=invalid_role");
                exit();
            }

            $result = mysqli_query($conn, $sql);
            if (mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_assoc($result);
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;

                //different pages for teacher + students
                if ($role === 'teacher') {
                    header("Location: markbookTeacherPage.php");
                } else { 
                    header("Location: markbookStudentPage.php");
                }
                exit();
            } else {
                header("Location: markbookLogin.php?error=invalid_credentials");
                exit();
            }

            mysqli_close($conn);
        ?>
    </body>
</html>