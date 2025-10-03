<?php
require "db.php";

$status = null;
$order = null;

if (isset($_POST['tracking_id'])) {
    $tracking_id = trim($_POST['tracking_id']);

    $stmt = $conn->prepare("SELECT * FROM orders WHERE tracking_id = ?");
    $stmt->bind_param("s", $tracking_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        $status = "found";
    } else {
        $status = "not_found";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Track Your Order</title>
  <link rel="stylesheet" href="style.css"> <!-- optional -->
</head>
<body>
  <h2>Track Your Order</h2>
  <form method="POST" action="">
    <input type="text" name="tracking_id" placeholder="Enter Tracking ID" required>
    <button type="submit">Track</button>
  </form>

  <?php if ($status === "found"): ?>
      <h3>Order Details</h3>
      <p><strong>Tracking ID:</strong> <?= htmlspecialchars($order['tracking_id']) ?></p>
      <p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>
      <p><strong>Total Paid:</strong> ₦<?= number_format($order['total'], 2) ?></p>
      <p><strong>Customer:</strong> <?= htmlspecialchars($order['firstname']." ".$order['lastname']) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
      <p><strong>Phone:</strong> <?= htmlspecialchars($order['phone']) ?></p>
      <p><strong>Address:</strong> <?= htmlspecialchars($order['address'].", ".$order['city']." ".$order['zip']) ?></p>

      <h4>Items:</h4>
      <ul>
        <?php
          $items = json_decode($order['items'], true);
          foreach ($items as $item) {
              echo "<li>".$item['name']." (x".$item['quantity'].") - ₦".$item['price']."</li>";
          }
        ?>
      </ul>
  <?php elseif ($status === "not_found"): ?>
      <p style="color:red;">Tracking ID not found. Please check and try again.</p>
  <?php endif; ?>
</body>
</html>
