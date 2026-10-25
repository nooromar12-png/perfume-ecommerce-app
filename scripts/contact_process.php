<?php
session_start();
require_once '../config/database.php';

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? 'General');
$message = trim($_POST['message'] ?? '');

if (empty($name) || empty($email) || empty($message)) {
    $_SESSION['contact_error'] = 'Please fill in all required fields.';
    header('Location: ../contact.php');
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO contacts (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $subject, $message]);

    $_SESSION['contact_success'] = 'Thank you! Your message has been sent.';
    header('Location: ../contact.php');

} catch (PDOException $e) {
    $_SESSION['contact_error'] = 'Error sending message. Please try again.';
    header('Location: ../contact.php');
}
?>