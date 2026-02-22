<?php
// fetch_expenses.php
include 'config.php';
session_start();

$user_id = $_SESSION['user_id'];

$sql = "SELECT description, amount, date, (SELECT name FROM groups WHERE id=group_id) as group_name 
        FROM expenses WHERE paid_by='$user_id'";
$result = $conn->query($sql);

$expenses = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $expenses[] = $row;
    }
}

echo json_encode($expenses);
?>
