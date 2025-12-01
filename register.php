<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PNR SYSTEM - Register</title>

    <!-- Bootstrap & Stylesheets -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css"> <!-- External CSS -->

    <style>
        /* Background Image */
        body {
            background: url('train.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* Logo & Title */
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo-container img {
            width: 140px;
            margin-bottom: 10px;
            margin-left: 40px;
        }

        .logo-container h1 {
            font-size: 28px;
            font-weight: bold;
            color: white;
            text-shadow: 2px 2px 5px black;
            margin-left: 40px;
        }

        /* Centering Register Box */
        .register-container {
            width: 100%;
            max-width: 1000px;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 50px;
            margin-right: 100px;
        }

        /* Box with Transparency */
        .box {
            display: flex;
            flex-direction: row;
            width: 80%;
            max-width: 900px;
            background: rgba(255, 255, 255, 0.15); /* Slightly transparent */
            border-radius: 15px;
            overflow: hidden;
            backdrop-filter: blur(10px); /* Blur effect */
            box-shadow: 0px 6px 30px rgba(0, 0, 0, 0.5);
            margin-left: 160px;
            margin-right: auto;
            margin-top: -50px;
        }

        /* Left Side - Greeting */
        .greeting {
            flex: 1;
            background: url('train.jpg') no-repeat center center;
            background-size: cover;
            padding: 40px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
        }

         .greeting h2 {
            font-size: 35px;
            font-weight: bold;
            text-shadow: 4px 4px 8px rgba(0, 0, 0, 1); /* Stronger black shadow */
        }

        .greeting p {
            font-size: 18px;
            font-weight: bold;
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 1); /* Darker shadow for better visibility */
        }


        .greeting .btn {
            margin-top: 20px;
            background: white;
            color: black;
            font-weight: bold;
            padding: 10px 20px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }

        .greeting .btn:hover {
            background: #ddd;
        }

        /* Right Side - Form */
        .form-container {
            flex: 1;
            padding: 40px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 15px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Input Fields */
        .input-group {
            display: flex;
            align-items: center;
            border: 1px solid #ccc;
            padding: 8px;
            border-radius: 5px;
            background: #f9f9f9;
            margin-bottom: 15px;
        }

        .input-group i {
            margin-right: 10px;
        }

        .input-group input,
        .input-group textarea {
            border: none;
            outline: none;
            flex: 1;
            padding: 5px;
            background: transparent;
        }
            input[type="date"] {
        color: #777; /* Grey text */
        background: #f0f0f0; /* Light grey background */
        border: 1px solid #ccc;
        padding: 8px;
        border-radius: 5px;
        outline: none;
        transition: 0.3s;
    }

    /* Change color on focus */
    input[type="date"]:focus {
        background: #e0e0e0;
        border: 1px solid #999;
    }

    /* Placeholder Text Color (For WebKit Browsers like Chrome) */
    input[type="date"]::placeholder {
        color: #777;
    }

        /* Button */
        .btn {
            width: 100%;
            background: #00adb5;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        .btn:hover {
            background: #008c9e;
        }

        /* Alert Auto Fade-Out */
        .alert {
            animation: fadeOut 5s forwards;
            animation-delay: 3s;
        }

        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        .greeting h2 {
        font-size: 40px;
        font-weight: bold;
        color: white;
        text-shadow: 4px 4px 8px black;
        }
        .box {
        width: 85%;
        max-width: 1500px; /* Increase width */
        height: 700px; /* Increase height */
        }

        /* Increase the size of the form container */
        .form-container {
            padding: 50px; /* Increase padding for more space */
        }

        
    </style>
</head>
<body>
    <div class="register-container">
        <div class="box">
            <!-- Left Side - Welcome Greeting -->
            <div class="greeting">
            <div class="logo-container">
                <img src="logo-white.png" alt="PNR Logo">
                <h1>BICOL EXPRESS REGISTER</h1>
            </div>
                <h2>Join Us!</h2>
                <p>Create an account to get started</p>
                <button class="btn" onclick="window.location.href='login.php'">Login</button>
            </div>

            <!-- Right Side - Registration Form -->
            <div class="form-container">
                <?php
                if(isset($_GET['error'])) {
                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            An error occurred during registration. Please try again.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                          </div>';
                }
                ?>
                <form action="process_register.php" method="POST">
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="firstname" placeholder="First Name" required>
                    </div>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="middle" placeholder="Middle Initial" maxlength="5">
                    </div>
                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="lastname" placeholder="Last Name" required>
                    </div>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="Password" required>
                    </div>
                    <div class="input-group">
                        <i class="fas fa-calendar"></i>
                        <input type="date" name="dob" required>
                    </div>
                    <div class="input-group">
                        <i class="fas fa-phone"></i>
                        <input type="tel" name="phone" placeholder="Phone Number" required>
                    </div>
                    <div class="input-group">
                        <i class="fas fa-home"></i>
                        <textarea name="address" placeholder="Address" required rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn">Register</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Dependencies -->
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const logoContainer = document.querySelector(".logo-container");
        
        // Initial state (hidden & moved up)
        logoContainer.style.opacity = "0";
        logoContainer.style.transform = "translateY(-50px)";

        // Animate after a short delay
        setTimeout(() => {
            logoContainer.style.transition = "all 1.0s ease-out";
            logoContainer.style.opacity = "2";
            logoContainer.style.transform = "translateY(0)";
        }, 200);
    });
</script>
</body>
</html>