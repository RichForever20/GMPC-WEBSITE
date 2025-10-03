<?php
header("Content-Type: application/json");
require "db.php";
require "vendor/autoload.php"; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get JSON from checkout.js
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Invalid data"]);
    exit;
}

// Generate Tracking ID
$tracking_id = "GMPC-" . strtoupper(substr(md5(uniqid()), 0, 8));

$status = "Pending";  // default
$reference = null;    // for bank transfer

// ---------- PAYSTACK PAYMENT ----------
if ($data['payment_method'] === "paystack") {
    if (empty($data['reference'])) {
        echo json_encode(["success" => false, "message" => "Missing transaction reference"]);
        exit;
    }

    $paystack_secret = "sk_test_xxxxxxxxxxxxx"; // replace with your SECRET key
    $ref = $data['reference'];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.paystack.co/transaction/verify/" . $ref);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $paystack_secret"
    ]);

    $result = curl_exec($ch);
    curl_close($ch);
    $response = json_decode($result, true);

    if (!$response['status'] || $response['data']['status'] !== 'success') {
        echo json_encode(["success" => false, "message" => "Payment not verified"]);
        exit;
    }

    $status = "Paid";
    $reference = $ref;
}

// ---------- SAVE ORDER ----------
$stmt = $conn->prepare("INSERT INTO orders 
(tracking_id, reference, firstname, lastname, email, phone, address, city, zip, items, total, status) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$items_json = json_encode($data['items']);
$stmt->bind_param("sssssssssdss", 
  $tracking_id, 
  $reference,
  $data['firstname'],
  $data['lastname'],
  $data['email'],
  $data['phone'],
  $data['address'],
  $data['city'],
  $data['zip'],
  $items_json,
  $data['total'],
  $status
);

$stmt->execute();
$stmt->close();

// ---------- SEND EMAIL ----------
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;
    $mail->Username = "your-email@gmail.com"; // replace
    $mail->Password = "your-app-password";   // use App Password, not Gmail password
    $mail->SMTPSecure = "tls";
    $mail->Port = 587;

    $mail->setFrom("your-email@gmail.com", "GMPC Ltd");
    $mail->addAddress($data['email'], $data['firstname']." ".$data['lastname']);

    $mail->isHTML(true);
    $mail->Subject = "Your Order Confirmation - Tracking ID $tracking_id";

    if ($status === "Paid") {
        $mail->Body = "
            <h2>Thank you for your order!</h2>
            <p><strong>Tracking ID:</strong> $tracking_id</p>
            <p><strong>Total Paid:</strong> ₦{$data['total']}</p>
            <p>We’ll notify you when your order is shipped.</p>
        ";
    } else {
        $mail->Body = "
            <h2>Your order has been placed!</h2>
            <p><strong>Tracking ID:</strong> $tracking_id</p>
            <p><strong>Total Due:</strong> ₦{$data['total']}</p>
            <p>Status: Awaiting Bank Transfer.</p>
        ";
    }

    $mail->send();
} catch (Exception $e) {
    // Log error but continue
}

echo json_encode([
    "success" => true, 
    "tracking_id" => $tracking_id,
    "status" => $status
]);
