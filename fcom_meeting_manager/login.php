<?php
       include 'config.php';

    // Start session
    session_start();

    if (isset($_POST['login'])) {
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);
    
        // Prepare the SQL statement
        $stmt = $conn->prepare("SELECT * FROM userinfo WHERE user_email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
    
        // Check if the user exists
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
    
            // Compare plain text passwords
            if ($password == $row['user_password']) {
                // Redirect based on user type
                if ($row['user_type'] == 'dean') {
                    $_SESSION['user_name'] = $row['user_name'];
                    $_SESSION['user_email'] = $row['user_email'];
                    $_SESSION['user_id'] = $row['user_id'];
                    header('location:dean_index.php');
                } elseif ($row['user_type'] == 'lecturer') {
                    $_SESSION['user_name'] = $row['user_name'];
                    $_SESSION['user_email'] = $row['user_email'];
                    $_SESSION['user_id'] = $row['user_id'];
                    header('location:index.php');
                }
            } else {
                $message[] = 'Incorrect Email or Password!';
            }
        } else {
            $message[] = 'Incorrect Email or Password!';
        }
    }
    
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Login</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width" />
    <link rel="stylesheet" href="./css/login.css" />
  </head>
  <body>
    <div class="container">
      <div class="center">
        <img src="images/logo_uptm.png" style="height:100px;"/>
          <form action="" method="POST">
              <div class="txt_field">
                  <input type="text" name="email" required>
                  <span></span>
                  <label>Email</label>
              </div>
              <div class="txt_field">
                  <input type="password" name="password" required>
                  <span></span>
                  <label>Password</label>
              </div>
              <?php
        //Displaying message if any
        if(isset($message)){
            foreach($message as $message){
                echo '
                <div class="message" id="messages">
                    <span>'.$message.'</span>
                </div>
                ';
            }
        }
    ?>
              <input name="login" type="Submit" value="Login">
              </div>
          </form>
      </div>
    </div>

    <script>
        //Hide message after 8 seconds
        setTimeout(() => {
        const box = document.getElementById('messages');
        if(box){
            box.style.display = 'none';
        }
        }, 8000);
    </script>
  </body>
</html>