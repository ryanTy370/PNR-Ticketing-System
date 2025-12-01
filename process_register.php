<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once 'config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and retrieve form inputs
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_STRING);
    $middle = filter_var($_POST['middle'], FILTER_SANITIZE_STRING);
    $lastname = filter_var($_POST['lastname'], FILTER_SANITIZE_STRING);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $dob = $_POST['dob'];
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_NUMBER_INT);
    $address = filter_var($_POST['address'], FILTER_SANITIZE_STRING);

    try {
        // Prepare the SQL query to insert the new user
        $sql = "INSERT INTO Users (Email, FirstName, MiddleInitial, LastName, Password_hash, DOB, PhoneNumber, Address) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email, $firstname, $middle, $lastname, $password, $dob, $phone, $address]);
        
        // Store user_id and email in session variables
        $_SESSION['user_id'] = $pdo->lastInsertId();  // Get the last inserted user's ID
        $_SESSION['email'] = $email;  // Store the email in the session

        // Set a success message in the session
        $_SESSION['registration_success'] = "Registration successful! Please login with your credentials.";

        // Redirect to the login page
        header("Location: login.php");
        exit();
    } catch(PDOException $e) {
        // Improve error handling
        error_log("Registration error: " . $e->getMessage());  // Log the error message
        header("Location: register.php?error=1");  // Redirect back to the registration page with an error
        exit();
    }
}
?>