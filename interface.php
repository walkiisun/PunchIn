<?php
session_start();

if (!isset($_SESSION['User_ID'])) {
    header("Location: login.php");
    exit();
}

$userFirstName = $_SESSION['First_Name'];
$userId = $_SESSION['User_ID'];

$servername = "127.0.0.1";
$Username = "root";
$Password = "";
$dbname = "mydb";

$conn = new mysqli($servername, $Username, $Password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function getNextWorkTimeId($conn, $userId) {
    $sql = "SELECT MAX(work_time_id) AS max_id FROM Work_Time_Instance WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $maxId = $row['max_id'];
    return ($maxId) ? $maxId + 1 : 1;
}

if (isset($_POST['clock_in'])) {
    $sqlCheck = "SELECT COUNT(*) AS count FROM Work_Time_Instance WHERE User_ID = ? AND Clock_OUT_TimeStamp IS NULL";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("i", $userId);
    $stmtCheck->execute();
    $result = $stmtCheck->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        $_SESSION['message'] = "<p style='color:red; background-color: #f8d7da; padding: 10px; border-radius: 5px; font-weight: bold; display: inline-block; font-size: 1.2rem;'>You are already clocked in! Please clock out first.</p>";
    } else {
        $clockInTime = date("Y-m-d H:i:s");

        // Let AUTO_INCREMENT handle Work_Time_ID
        $sqlInsert = "INSERT INTO Work_Time_Instance (User_ID, Clock_IN_TimeStamp) VALUES (?, ?)";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bind_param("is", $userId, $clockInTime);

        if ($stmtInsert->execute()) {
            $_SESSION['message'] = "<p style='color:green; background-color: #f8d7da; padding: 10px; border-radius: 5px; font-weight: bold; display: inline-block; font-size: 1.2rem;'> Clock-in successful!</p>";
        } else {
            $_SESSION['message'] = "<p style='color:red;'>Error: " . $stmtInsert->error . "</p>";
        }
        $stmtInsert->close();
    }

    $stmtCheck->close();
    header("Location: interface.php");
    exit();
}



if (isset($_POST['clock_out'])) {
    if (isset($_POST['clock_out'])) {
        $clockOutTime = date("Y-m-d H:i:s");
    
        // Get the current clock-in time
        $sqlClockIn = "SELECT Clock_IN_TimeStamp FROM Work_Time_Instance WHERE User_ID = ? AND Clock_OUT_TimeStamp IS NULL";
        $stmtClockIn = $conn->prepare($sqlClockIn);
        $stmtClockIn->bind_param("i", $userId);
        $stmtClockIn->execute();
        $resultClockIn = $stmtClockIn->get_result();
        $row = $resultClockIn->fetch_assoc();
        $clockInTime = $row['Clock_IN_TimeStamp'];
        $stmtClockIn->close();
    
        if ($clockInTime) {
            // Calculate hours worked
            $hoursWorked = (strtotime($clockOutTime) - strtotime($clockInTime)) / 3600;
    
            // Prompt for a reason if under 8 hours
            if ($hoursWorked < 8) {
                // Assume reason comes from a POST input field
                $anomalyReason = isset($_POST['reason']) ? $_POST['reason'] : null;
    
                if (empty($anomalyReason)) {
                    $_SESSION['message'] = "<p style='color:red; background-color: #f8d7da; padding: 3px; border-radius: 3px; font-weight: bold; display: inline-block; font-size: 0.7 rem;'>Error! Reason is required for clocking out under 8 hours.</p>";
                    header("Location: interface.php");
                    exit();
                }
            } else {
                $anomalyReason = null;
            }
    
            // Update the record with Clock-Out timestamp and anomaly reason (if any)
            $sqlUpdate = "UPDATE Work_Time_Instance SET Clock_OUT_TimeStamp = ?, Anomaly_Reason = ? WHERE User_ID = ? AND Clock_OUT_TimeStamp IS NULL";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bind_param("ssi", $clockOutTime, $anomalyReason, $userId);
    
            if ($stmtUpdate->execute()) {
                $_SESSION['message'] = "<p style='color:green; background-color: #f8d7da; padding: 10px; border-radius: 5px; font-weight: bold; display: inline-block; font-size: 1.2rem;'>Clock-out successful!</p>";
            } else {
                $_SESSION['message'] = "<p style='color:red;'>Error: " . $stmtUpdate->error . "</p>";
            }
    
            $stmtUpdate->close();
        } else {
            $_SESSION['message'] = "<p style='color:red; background-color: #f8d7da; padding: 10px; border-radius: 5px; font-weight: bold; display: inline-block; font-size: 1.2rem;'>No active clock-in record found.</p>";
        }
    
        header("Location: interface.php");
        exit();
    }
    
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="css/interface.css">
</head>
<body class="roboto">
<header>
    <nav class="flex">
        <div class="logo flex">
            <img src="images/art-removebg-preview.png" alt="">
            <h1 class="font2">Punch In</h1>
        </div>
        <a href="logout.php">
            <button class="flex">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                    <g fill="none" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                        <path d="M9 8V6a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-7a2 2 0 0 1-2-2v-2"/>
                        <path d="M3 12h13l-3-3m0 6l3-3"/>
                    </g>
                </svg>
                Logout
            </button>
        </a>

    </nav>
</header>
<main>
    <h2>Welcome, <?php echo htmlspecialchars($userFirstName); ?></h2>


    <?php
    if (isset($_SESSION['message'])) {
        echo $_SESSION['message'];
        unset($_SESSION['message']);
    }
    ?>
    <div class="dash">
        <a href="dashboard.php"><button>View Dashboard</button></a>
    </div>

    <div id="reasonContainer">
        <form method="POST" action="">
            <textarea name="reason" placeholder="If working under 8 hours: Provide a reason and clock out here." rows="5"></textarea>
            <button type="submit" name="clock_out" class="clock-buttons">Early Clock-out</button>
        </form>
    </div>
    
    <div class="clock flex">
        <form method="POST" action="">
            <button type="submit" name="clock_in" class="clock-buttons">Clock-in</button>
        </form>
        <form method="POST" action="">
            <button type="submit" name="clock_out" class="clock-buttons">Clock-out</button>
        </form>
    </div>
</main>
</body>
</html>
