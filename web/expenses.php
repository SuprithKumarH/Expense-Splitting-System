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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_expense'])) {
        // Add a new expense
        $description = $_POST['description'];
        $amount = $_POST['amount'];
        $group_id = $_POST['group_id'];
        $date = date('Y-m-d');

        $bill_image = '';
        if (isset($_FILES['bill_image']) && $_FILES['bill_image']['error'] == 0) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($_FILES["bill_image"]["name"]);
            move_uploaded_file($_FILES["bill_image"]["tmp_name"], $target_file);
            $bill_image = $target_file;
        }

        $sql = "INSERT INTO expenses (description, amount, date, group_id, paid_by, bill_image) VALUES ('$description', $amount, '$date', $group_id, $user_id, '$bill_image')";
        if ($conn->query($sql) === TRUE) {
            $message = "Expense added successfully.";
        } else {
            $message = "Error: " . $conn->error;
        }
    } elseif (isset($_POST['split_expenses'])) {
        // Split expenses for the selected group
        $group_id = $_POST['group_id'];
        
        // Calculate total expenses per user
        $sql = "SELECT paid_by, SUM(amount) AS total_spent FROM expenses WHERE group_id = $group_id GROUP BY paid_by";
        $result = $conn->query($sql);
        $user_expenses = [];
        $total_expenses = 0;

        while ($row = $result->fetch_assoc()) {
            $user_expenses[$row['paid_by']] = $row['total_spent'];
            $total_expenses += $row['total_spent'];
        }

        // Get group members
        $sql = "SELECT user_id FROM group_members WHERE group_id = $group_id";
        $result = $conn->query($sql);
        $members = [];
        while ($row = $result->fetch_assoc()) {
            $members[] = $row['user_id'];
        }

        // Calculate split amount
        $num_members = count($members);
        $split_amount = $total_expenses / $num_members;

        // Calculate how much each user owes or is owed
        $balances = [];
        foreach ($members as $member) {
            $balances[$member] = ($user_expenses[$member] ?? 0) - $split_amount;
        }

        // Generate result message
        $result_message = '';
        foreach ($balances as $user => $balance) {
            if ($balance < 0) {
                $owed_amount = abs($balance);
                foreach ($balances as $other_user => $other_balance) {
                    if ($other_balance > 0) {
                        $amount_to_pay = min($other_balance, $owed_amount);
                        if ($amount_to_pay > 0) {
                            $sql = "SELECT name FROM users WHERE id = $user";
                            $result = $conn->query($sql);
                            $user1 = $result->fetch_row()[0];
                            $sql = "SELECT name FROM users WHERE id = $other_user";
                            $result = $conn->query($sql);
                            $user2 = $result->fetch_row()[0];

                            $result_message .= "$user1 owes $user2 Rs $amount_to_pay<br>";
                            $balances[$other_user] -= $amount_to_pay;
                            $owed_amount -= $amount_to_pay;
                            if ($owed_amount <= 0) break;
                        }
                    }
                }
            }
        }

        $message = $result_message;
    }
}

// Fetch groups for the logged-in user
$sql = "SELECT `grps`.id, `grps`.name 
        FROM `grps` 
        JOIN group_members ON `grps`.id = group_members.group_id 
        WHERE group_members.user_id = $user_id";
$result = $conn->query($sql);
$grps = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $grps[] = $row;
    }
}

// Fetch expenses for the selected group
$selected_group_id = $_GET['group_id'] ?? $grps[0]['id'] ?? 0;
$sql = "SELECT expenses.description, expenses.amount, users.name AS paid_by, expenses.bill_image 
        FROM expenses 
        JOIN users ON expenses.paid_by = users.id 
        WHERE expenses.group_id = $selected_group_id 
        ORDER BY expenses.date DESC";
$result = $conn->query($sql);
$expenses = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $expenses[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expenses</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Internal CSS for the Expenses page */
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
        .expense-list {
            list-style-type: none;
            padding: 0;
        }
        .expense-list li {
            padding: 15px;
            margin: 15px 0;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease, background 0.3s ease;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .expense-list li:hover {
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
        .form-container input[type="text"]:hover {
            transform: scale(1.05);
        }
        .form-container input[type="number"]:hover {
            transform: scale(1.05);
        }
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
        .form-container input[type="file"] {
            display: none; /* Hide the default file input */
        }
        .upload-label {
            background: rgba(255, 255, 255, 0.5);
            color: #2C3E50;
            border-radius: 5px;
            border: none;
            width: 80%;
            max-width: 500px;
            padding: 10px;
            margin: 10px 0;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: transform 0.3s ease, background 0.3s ease;
        }
        .upload-label:hover {
            background: rgba(255, 255, 255, 0.7);
            transform: scale(1.05);
        }
        footer {
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.7);
            border-radius: 10px;
            color: white;
            margin-top: auto; 
        }
        .button {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.5rem;
            color: white;
        }
        .button:hover {
            transform: scale(1.2);
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
        
        <h1>Manage Your Expenses</h1>
        
        <?php if ($message): ?>
            <div class="form-container">
                <p><?php echo $message; ?></p>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <h2>Add New Expense</h2>
            <form method="POST" action="expenses.php" enctype="multipart/form-data">
                <input type="text" name="description" placeholder="Expense Description" required>
                <input type="number" step="0.01" name="amount" placeholder="Amount" required>
                <select name="group_id" required>
                    <?php foreach ($grps as $grp): ?>
                        <option value="<?php echo $grp['id']; ?>"><?php echo $grp['name']; ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="bill_image" class="upload-label">
                    &#x21e7; Upload Bill Image
                </label>
                <input type="file" id="bill_image" name="bill_image" accept="image/*">
                <input type="submit" name="add_expense" value="Add Expense">
            </form>
        </div>
        
        <div class="form-container">
            <h2>Split Expenses</h2>
            <form method="POST" action="expenses.php">
                <select name="group_id" required>
                    <?php foreach ($grps as $grp): ?>
                        <option value="<?php echo $grp['id']; ?>"><?php echo $grp['name']; ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" name="split_expenses" value="Split Expenses">
            </form>
        </div>
        
        <div class="form-container">
            <h2>Expenses</h2>
            <ul class="expense-list">
                <?php foreach ($expenses as $expense): ?>
                    <li>
                        <span>
                            <strong><?php echo $expense['description']; ?></strong> 
                            - Rs <?php echo number_format($expense['amount'], 2); ?> 
                            (Paid by <?php echo $expense['paid_by']; ?>)
                        </span>
                        <?php if ($expense['bill_image']): ?>
                            <form method="GET" action="<?php echo $expense['bill_image']; ?>" style="display: inline;">
                                <button type="submit" class="button">&#x2193;</button>
                            </form>
                        <?php endif; ?>
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

    <script>
        // JavaScript to trigger file input click on label click
        document.querySelector('.upload-label').addEventListener('click', function() {
            document.getElementById('bill_image').click();
        });
    </script>
</body>
</html>
