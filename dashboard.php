<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['User_ID'])) {
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "127.0.0.1";
$Username = "root";
$Password = "Monday14#";
$dbname = "mydb";

$conn = new mysqli($servername, $Username, $Password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userId = $_SESSION['User_ID'];

// Check if the user is an admin
$isAdminQuery = "SELECT if_Admin FROM User WHERE User_ID = ?";
$stmt = $conn->prepare($isAdminQuery);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($isAdmin);
$stmt->fetch();
$stmt->close();

if ($isAdmin) {
    // Fetch all users' timecards for admin
    $sql = "
        SELECT 
            u.User_ID, 
            u.First_Name, 
            u.Last_Name, 
            DATE_FORMAT(w.Clock_IN_TimeStamp, '%M %e, %Y %l:%i %p') AS Formatted_Clock_IN_TimeStamp, 
            DATE_FORMAT(w.Clock_OUT_TimeStamp, '%M %e, %Y %l:%i %p') AS Formatted_Clock_OUT_TimeStamp,
            TIMESTAMPDIFF(HOUR, w.Clock_IN_TimeStamp, w.Clock_OUT_TimeStamp) AS Hours_Worked,
            COALESCE(w.Anomaly_Reason, 'N/A') AS Anomaly_Reason
        FROM 
            Work_Time_Instance w
        JOIN 
            User u ON u.User_ID = w.User_ID
        ORDER BY 
            w.Clock_IN_TimeStamp DESC;
    ";

    
} else {
    // Fetch only logged-in user's timecards
    $sql = "
        SELECT Work_Time_ID, 
        DATE_FORMAT(Clock_IN_TimeStamp, '%M %e, %Y %l:%i %p') AS Formatted_Clock_IN_TimeStamp, 
        DATE_FORMAT(Clock_OUT_TimeStamp, '%M %e, %Y %l:%i %p') AS Formatted_Clock_OUT_TimeStamp, 
        TIMESTAMPDIFF(HOUR, Clock_IN_TimeStamp, Clock_OUT_TimeStamp) AS Hours_Worked,
        COALESCE(Anomaly_Reason, 'N/A') AS Anomaly_Reason
        FROM Work_Time_Instance 
        WHERE User_ID = ?
        ORDER BY 
            Clock_IN_TimeStamp DESC;

    ";
}

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if (!$isAdmin) {
    $stmt->bind_param("i", $userId); // Bind parameter for non-admin
}
$stmt->execute();
$result = $stmt->get_result();
$timecards = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
<header>
    <nav class="flex">
        <div class="logo flex">
            <img src="images/art-removebg-preview.png" alt="">
            <h1 class="font2">Punch In</h1>
        </div>
        <a href="interface.php"><button>Back to Interface</button></a>
    </nav>
</header>
<main>
    <h2>Dashboard</h2>
    <?php if (!empty($timecards)): ?>
        <table border="1">
            <thead>
                <tr>
                    <?php if ($isAdmin): ?>
                    <th>User</th>
                    <?php endif; ?>
                    <th>Clock-In Time</th>
                    <th>Clock-Out Time</th>
                    <th>Hours Worked</th>
                    <th>Early Clock-Out Reason</th>
                    
                </tr>
            </thead>
            <tbody>
                <?php foreach ($timecards as $row): ?>
                <tr style="<?php echo $row['Anomaly_Reason'] !== 'N/A' ? 'background-color: #ffcccc;' : ''; ?>">
                    <?php if ($isAdmin): ?>
                    <td><?php echo htmlspecialchars($row['First_Name'] . ' ' . $row['Last_Name']); ?></td>
                    <?php endif; ?>
                    <td><?php echo htmlspecialchars($row['Formatted_Clock_IN_TimeStamp']); ?></td>
                    <td><?php echo htmlspecialchars($row['Formatted_Clock_OUT_TimeStamp'] ?? 'Still Clocked In'); ?></td>
                    <td><?php echo htmlspecialchars($row['Hours_Worked'] ?? 'Still Clocked In'); ?></td>

                    <td><?php echo htmlspecialchars($row['Anomaly_Reason']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No timecard data available.</p>
    <?php endif; ?>
</main>
</body>
</html>
