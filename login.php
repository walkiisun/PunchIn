<?php
session_start();  // Start the session

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection setup
    $servername = "127.0.0.1";
    $Username = "root";
    $Password = "Monday14#";
    $dbname = "mydb";

    // Create connection
    $conn = new mysqli($servername, $Username, $Password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Retrieve user inputs
    $inputUsername = $_POST['username'];
    $inputPassword = $_POST['password'];

    // Prepare and execute the query
    $sql = "SELECT User_ID, First_Name FROM User WHERE Username = ? AND Password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $inputUsername, $inputPassword);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $user = $result->fetch_assoc();
        $_SESSION['User_ID'] = $user['User_ID'];
        $_SESSION['First_Name'] = $user['First_Name'];


        header("Location: interface.php");
        exit();
    } else {
        $error = "Invalid username or password";
    }


    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../css/login.css">
</head>
<body class="roboto">
<figure>
    <div class="top">
        <img src="../images/art-removebg-preview (1).png" alt="">
        <h1>PunchIn</h1>
    </div>

    <aside>
        <form action="" method="POST">
            <input placeholder="Username" type="text" name="username" required>
            <input placeholder="Password" type="password" name="password" required>
            <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
            <button type="submit">
                <svg xmlns="http://www.w3.org/2000/svg" width="2.5em" height="2.5em" viewBox="0 0 24 24">
                    <path fill="none" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m16 16l-4-4m0 0L8 8m4 4l4-4m-4 4l-4 4"/>
                </svg>
                LOGIN
            </button>
        </form>
    </aside>

    <div class="other">
        <p><input type="checkbox" name="remember" id="remember"> Remember me</p>
        <a href="#">Forgot password?</a>
    </div>
</figure>
</body>
</html>
