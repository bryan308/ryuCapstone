<?php
session_start();
require '../db_connect.php';

// Ensure the user is logged in as a customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Query to fetch customer messages and corresponding admin replies
$query = "SELECT cm.message, cm.created_at AS message_date,
                 ar.reply_message AS admin_reply, ar.created_at AS admin_reply_date
          FROM customer_messages cm
          LEFT JOIN admin_replies ar ON cm.id = ar.message_id
          WHERE cm.customer_id = ?
          ORDER BY cm.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Messages and Admin Replies</title>
    <link rel="stylesheet" href="../assets/customer-styles/view-replies.css">
</head>
<body>
    <h1>Your Messages and Admin Replies</h1>
    <table border="1">
        <thead>
            <tr>
                <th>Your Message</th>
                <th>Submitted On</th>
                <th>Admin Reply</th>
                <th>Reply Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $formattedMessageDate = (new DateTime($row['message_date']))->format('F j, Y, g:i a');
                    $adminReplyDate = $row['admin_reply_date'] ? (new DateTime($row['admin_reply_date']))->format('F j, Y, g:i a') : "No reply yet";
            ?>
            <tr>
                <td><?php echo htmlspecialchars($row['message']); ?></td>
                <td><?php echo htmlspecialchars($formattedMessageDate); ?></td>
                <td><?php echo htmlspecialchars($row['admin_reply'] ?? 'No reply yet'); ?></td>
                <td><?php echo htmlspecialchars($adminReplyDate); ?></td>
            </tr>
            <?php } } else { ?>
            <tr>
                <td colspan="4">No messages found.</td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <?php
    $stmt->close();
    $conn->close();
    ?>
</body>
</html>
