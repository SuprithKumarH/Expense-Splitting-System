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

// Handle payment form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = $_POST['amount'];
    $paid_to = $_POST['paid_to'];
    $group_id = $_POST['group_id'];
    $date = date('Y-m-d');

    // Insert the payment into the database
    $stmt = $conn->prepare("INSERT INTO payments (amount, paid_by, paid_to, group_id, date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("diiis", $amount, $user_id, $paid_to, $group_id, $date);

    if ($stmt->execute()) {
        $message = "Payment of ₹$amount was successful.";
    } else {
        $message = "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch users for the payment form
$sql_users = "SELECT id, name FROM users WHERE id != $user_id";
$result_users = $conn->query($sql_users);
$users = [];
if ($result_users->num_rows > 0) {
    while ($row = $result_users->fetch_assoc()) {
        $users[] = $row;
    }
}

// Fetch groups for the payment form
$sql_groups = "SELECT id, name FROM grps";
$result_groups = $conn->query($sql_groups);
$groups = [];
if ($result_groups->num_rows > 0) {
    while ($row = $result_groups->fetch_assoc()) {
        $groups[] = $row;
    }
}

// Fetch payments for the logged-in user
$sql_payments = "SELECT payments.amount, payments.date, 
                        users_from.name AS paid_by_name, 
                        users_to.name AS paid_to_name, 
                        grps.name AS group_name
                 FROM payments
                 JOIN users AS users_from ON payments.paid_by = users_from.id
                 JOIN users AS users_to ON payments.paid_to = users_to.id
                 JOIN grps ON payments.group_id = grps.id
                 WHERE payments.paid_by = $user_id OR payments.paid_to = $user_id";
$result_payments = $conn->query($sql_payments);
$payments = [];
if ($result_payments->num_rows > 0) {
    while ($row = $result_payments->fetch_assoc()) {
        $payments[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Internal CSS for the Payments page */
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
        .payment-list {
            list-style-type: none;
            padding: 0;
        }
        .payment-list li {
            padding: 15px;
            margin: 15px 0;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease, background 0.3s ease;
            cursor: pointer;
        }
        .payment-list li:hover {
            transform: scale(1.05);
            background: rgba(255, 255, 255, 0.2);
        }
        .form-container {
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 20px;
            margin: 20px auto;
            max-width: 800px;
            color: #2C3E50;
        }
        .form-container form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .form-container input[type="text"], 
        .form-container input[type="number"],
        .form-container input[type="submit"],
        .form-container select {
            padding: 10px;
            margin: 10px 0;
            font-size: 18px;
            border-radius: 5px;
            border: none;
            width: 80%;
            max-width: 500px;
            transition: transform 0.3s ease, background 0.3s ease;
        }
        .form-container input[type="text"], 
        .form-container input[type="number"],
        .form-container select {
            background: rgba(255, 255, 255, 0.5);
            color: #2C3E50;
        }
        .form-container input[type="text"]:hover,
        .form-container input[type="number"]:hover,
        .form-container select:hover {
            transform: scale(1.05);
        }
        .form-container input[type="submit"] {
            background: #4CA1AF;
            color: white;
            cursor: pointer;
        }
        .form-container input[type="submit"]:hover {
            background: #3E94A3;
            transform: scale(1.05);
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
        
        <h1>Manage Your Payments</h1>
        
        <?php if ($message): ?>
            <div class="form-container">
                <p><?php echo $message; ?></p>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <h2>Add Payment</h2>
            <form method="POST" action="payments.php">
                <input type="number" step="0.01" name="amount" placeholder="Amount" required>
                <select name="paid_to" required>
                    <option value="" disabled selected>Select Payee</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>"><?php echo $user['name']; ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="group_id" required>
                    <option value="" disabled selected>Select Group</option>
                    <?php foreach ($groups as $group): ?>
                        <option value="<?php echo $group['id']; ?>"><?php echo $group['name']; ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" value="Add Payment">
            </form>
        </div>
        
        <div class="form-container">
            <h2>Payment History</h2>
            <ul class="payment-list">
                <?php foreach ($payments as $payment): ?>
                    <li>
                        <strong>₹<?php echo number_format($payment['amount'], 2); ?></strong>
                        - <?php echo $payment['paid_by_name']; ?> paid to <?php echo $payment['paid_to_name']; ?>
                        (Group: <?php echo $payment['group_name']; ?>)
                        on <?php echo $payment['date']; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
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
