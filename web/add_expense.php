<?php
// add_expense.php
include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $description = $_POST['description'];
    $amount = $_POST['amount'];
    $group_id = $_POST['group'];
    $paid_by = $_SESSION['user_id'];

    $sql = "INSERT INTO expenses (description, amount, date, group_id, paid_by) 
            VALUES ('$description', '$amount', NOW(), '$group_id', '$paid_by')";

    if ($conn->query($sql) === TRUE) {
        echo "Expense added successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>
