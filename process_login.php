<?php
session_start();
require_once 'config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    try {
        $sql = "SELECT * FROM Users WHERE Email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['Password_hash'])) {
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['email'] = $user['Email'];
            header("Location: homepage.html");
            exit();
        } else {
            header("Location: login.php?error=1");
            exit();
        }
    } catch(PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        header("Location: login.php?error=2");
        exit();
    }
}
?>