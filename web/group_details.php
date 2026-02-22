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

if (!isset($_GET['group_id'])) {
    echo "Group ID not specified.";
    exit;
}

$group_id = $_GET['group_id'];
$user_id = $_SESSION['user_id'];

// Fetch group details
$sql = "SELECT name FROM grps WHERE id = $group_id";
$result = $conn->query($sql);
$group = $result->fetch_assoc();

// Fetch group members
$sql_members = "SELECT users.name FROM users 
                JOIN group_members ON users.id = group_members.user_id 
                WHERE group_members.group_id = $group_id";
$result_members = $conn->query($sql_members);
$members = [];
if ($result_members->num_rows > 0) {
    while ($row = $result_members->fetch_assoc()) {
        $members[] = $row['name'];
    }
}

// Fetch group expenses
$sql_expenses = "SELECT description, amount FROM expenses 
                 WHERE group_id = $group_id ORDER BY id DESC";
$result_expenses = $conn->query($sql_expenses);
$expenses = [];
if ($result_expenses->num_rows > 0) {
    while ($row = $result_expenses->fetch_assoc()) {
        $expenses[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Group Details</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            margin: 0;
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to bottom right, #6DD5FA, #2980B9);
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
            color: #6DD5FA;
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
        .content {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin: 20px auto;
            max-width: 800px;
            color: #2C3E50;
        }
        .section {
            margin-bottom: 20px;
        }
        .section h2 {
            background: rgba(0, 0, 0, 0.2);
            padding: 10px;
            border-radius: 5px;
        }
        .member-list, .expense-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        .member-list li, .expense-list li {
            padding: 15px;
            margin: 10px 0;
            background: rgba(255, 255, 255, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 10px;
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease, background 0.3s ease;
            cursor: pointer;
        }
        .member-list li:hover, .expense-list li:hover {
            transform: scale(1.05);
            background: rgba(255, 255, 255, 0.5);
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
        
        <h1>Group Details: <?php echo htmlspecialchars($group['name']); ?></h1>
        
        <div class="content">
            <div class="section">
                <h2>Group Members</h2>
                <ul class="member-list">
                    <?php foreach ($members as $member): ?>
                        <li><?php echo htmlspecialchars($member); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="section">
                <h2>Group Expenses</h2>
                <ul class="expense-list">
                    <?php foreach ($expenses as $expense): ?>
                        <li>
                            <?php echo htmlspecialchars($expense['description']); ?> - ₹<?php echo number_format($expense['amount'], 2); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
    <footer>
        <p>&copy; 2024 Splitly. All rights reserved.</p>
        <p>Contact us: info@splitly.com | Follow us on social media: 
            <a href="#" style="color: #6DD5FA;">Facebook</a>, 
            <a href="#" style="color: #6DD5FA;">Twitter</a>, 
            <a href="#" style="color: #6DD5FA;">Instagram</a>
        </p>
    </footer>
</body>
</html>
