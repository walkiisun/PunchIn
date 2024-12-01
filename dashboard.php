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
$Password = "";
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
        SELECT u.First_Name, u.Last_Name, 
               w.Clock_IN_TimeStamp, w.Clock_OUT_TimeStamp,
               TIMESTAMPDIFF(HOUR, w.Clock_IN_TimeStamp, w.Clock_OUT_TimeStamp) AS Hours_Worked,
               COALESCE(p.Wage * TIMESTAMPDIFF(HOUR, w.Clock_IN_TimeStamp, w.Clock_OUT_TimeStamp), 0) AS Earnings,
               COALESCE(el.Early_Leave_Reason, 'N/A') AS Early_Leave_Reason
        FROM Work_Time_Instance w
        JOIN User u ON u.User_ID = w.User_ID
        LEFT JOIN Position p ON p.Position_ID = u.Position_Position_ID
        LEFT JOIN ClockingIN c ON c.Time_Table_Work_Time_ID = w.Work_Time_ID
        LEFT JOIN Early_Leave_Types el ON el.idEarly_Leave = c.Early_Leave_id
    ";
} else {
    // Fetch only logged-in user's timecards
    $sql = "
        SELECT u.First_Name, u.Last_Name, 
               w.Clock_IN_TimeStamp, w.Clock_OUT_TimeStamp,
               TIMESTAMPDIFF(HOUR, w.Clock_IN_TimeStamp, w.Clock_OUT_TimeStamp) AS Hours_Worked
        FROM Work_Time_Instance w
        JOIN User u ON u.User_ID = w.User_ID
        WHERE u.User_ID = ?
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
                    <th>Clock-In</th>
                    <th>Clock-Out</th>
                    <th>Hours Worked</th>
                    <?php if ($isAdmin): ?>
                    <th>Earnings</th>
                    <th>Early Leave Reason</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($timecards as $row): ?>
                <tr>
                    <?php if ($isAdmin): ?>
                    <td><?php echo htmlspecialchars($row['First_Name'] . ' ' . $row['Last_Name']); ?></td>
                    <?php endif; ?>
                    <td><?php echo htmlspecialchars($row['Clock_IN_TimeStamp']); ?></td>
                    <td><?php echo htmlspecialchars($row['Clock_OUT_TimeStamp'] ?? 'Still Clocked In'); ?></td>
                    <td><?php echo htmlspecialchars($row['Hours_Worked']); ?></td>
                    <?php if ($isAdmin): ?>
                    <td><?php echo htmlspecialchars($row['Earnings']); ?></td>
                    <td><?php echo htmlspecialchars($row['Early_Leave_Reason']); ?></td>
                    <?php endif; ?>
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
