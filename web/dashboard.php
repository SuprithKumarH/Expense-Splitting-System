<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

include 'config.php';

$user_id = $_SESSION['user_id'];
$message = '';

// Fetch groups for the logged-in user
$sql = "SELECT grps.id, grps.name 
        FROM grps 
        JOIN group_members ON grps.id = group_members.group_id 
        WHERE group_members.user_id = $user_id";
$result = $conn->query($sql);
$grps = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $grps[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Internal CSS for the Dashboard */
        body {
            margin: 0;
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to bottom right, #2C3E50, #4CA1AF);
            color: white;
            text-align: center;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .container {
            padding: 20px;
            flex: 1;
        }
        header {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.7);
            border-radius: 10px;
            margin-bottom: 20px;
        }
        header img {
            width: 200px; 
        }
        nav {
            margin-top: 20px;
        }
        nav a {
            color: #4CA1AF;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            display: inline-block;
            transition: transform 0.3s ease, background 0.3s ease;
        }
        nav a:hover {
            transform: scale(1.05);
            background: rgba(255, 255, 255, 0.2);
        }
        h1, h2 {
            font-family: 'Georgia', serif;
        }
        .group-list {
            list-style-type: none;
            padding: 0;
        }
        .group-list li {
            padding: 15px;
            margin: 15px 0;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease, background 0.3s ease;
            cursor: pointer;
        }
        .group-list li:hover {
            transform: scale(1.05);
            background: rgba(255, 255, 255, 0.2);
        }
        .group-list li a {
            color: #4CA1AF;
            text-decoration: none;
            font-size: 18px;
        }
        .group-container {
            margin: 0 auto;
            max-width: 800px; 
            background-color: transparent;
            border-radius: 10px;
            padding: 20px;
            flex: 1;
        }
        footer {
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.7);
            border-radius: 10px;
            color: white;
            margin-top: auto; 
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <img src="splitly.png" alt="Splitly Logo">
            <nav>
                <a href="dashboard.php">Dashboard</a>
                <a href="groups.php">Groups</a>
                <a href="expenses.php">Expenses</a>
                <a href="payments.php">Payments</a>
                <a href="logout.php">Logout</a>
            </nav>
        </header>
        <main>
            <div class="group-container">
                <h2>Your Groups</h2>
                <ul class="group-list">
                    <?php if (empty($grps)) { ?>
                        <li>No groups found.</li>
                    <?php } else { ?>
                        <?php foreach ($grps as $group) { ?>
                            <li><a href="group_details.php?group_id=<?php echo $group['id']; ?>"><?php echo htmlspecialchars($group['name']); ?></a></li>
                        <?php } ?>
                    <?php } ?>
                </ul>
            </div>
        </main>
    </div>
    <footer>
        <p>&copy; 2024 Splitly. All rights reserved.</p>
        <p>Contact us: info@splitly.com | Follow us on social media: 
            <a href="#" style="color: #4CA1AF;">Facebook</a>, 
            <a href="#" style="color: #4CA1AF;">Twitter</a>, 
            <a href="#" style="color: #4CA1AF;">Instagram</a>
        </p>
    </footer>
</body>
</html>
