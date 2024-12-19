<?php
include 'connection.php';

// Admin email and plain text password
$email = 'rishu@gmail.com';
$plainPassword = 'rishu@1234'; // Replace with your desired password
$secret_s = 'rishu';
// Hash the password
$passwordHash = password_hash($plainPassword, PASSWORD_BCRYPT);
$secretHash = hash('sha256', $secret_s); // Use SHA-256 for hashing the secret string
$true=1;
// Insert into the admin table
$stmt = $conn->prepare("INSERT INTO super_admin (email, password_hash, secret, is_admin) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sssi", $email, $passwordHash, $secretHash, $true);
$stmt->execute();

echo " Super Admin account created successfully.";

$stmt->close();
$conn->close();
?>