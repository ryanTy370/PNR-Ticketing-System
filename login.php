<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PNR SYSTEM - Login</title>
    
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
        }

        .logo-container h1 {
            font-size: 28px;
            font-weight: bold;
            color: white;
            text-shadow: 2px 2px 5px black;
        }

        /* Centering Login Box */
        .login-container {
            width: 100%;
            max-width: 1200px; /* Increased size */
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Bigger Transparent Box */
        .box {
            display: flex;
            flex-direction: row;
            width: 90%;
            max-width: 1100px;
            background: rgba(255, 255, 255, 0.15);
            overflow: hidden;
            backdrop-filter: blur(10px);
            box-shadow: 0px 6px 30px rgba(0, 0, 0, 0.5);
        }

        /* Left Side - Greeting */
        .greeting {
            flex: 1;
            background: url('train.jpg') no-repeat center center;
            background-size: cover;
            padding: 60px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
        }

        .greeting h2 {
            font-size: 40px;
            font-weight: bold;
            text-shadow: 4px 4px 8px rgba(0, 0, 0, 1);
        }

        .greeting p {
            font-size: 22px;
            font-weight: bold;
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 1);
        }

        .greeting .btn {
            margin-top: 20px;
            background: white;
            color: black;
            font-weight: bold;
            padding: 15px 30px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 18px;
        }

        .greeting .btn:hover {
            background: #ddd;
        }

        /* Right Side - Login Form */
        .form-container {
            flex: 1;
            padding: 60px;
            background: rgba(255, 255, 255, 0.3);
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        /* Input Fields */
        .input-group {
            display: flex;
            align-items: center;
            border: 1px solid #ccc;
            padding: 12px;
            background: #f9f9f9;
            margin-bottom: 20px;
        }

        .input-group i {
            margin-right: 12px;
            font-size: 18px;
        }

        .input-group input {
            border: none;
            outline: none;
            flex: 1;
            padding: 10px;
            background: transparent;
            font-size: 16px;
        }

        /* Button */
        .btn {
            width: 100%;
            background: #00adb5;
            color: white;
            border: none;
            padding: 14px;
            font-size: 20px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn:hover {
            background: #008c9e;
        }

        /* Remember Me Toggle */
        .remember-me {
            display: flex;
            align-items: center;
            gap: 10px;
            color: black;
            margin-top: 15px;
        }

        /* Forgot Password */
        .forgot-password {
            text-align: right;
            margin-top: 8px;
        }

        .forgot-password a {
            color:rgb(255, 255, 255);
            text-decoration: none;
            font-size: 16px;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        /* Error Message */
        .alert-danger {
            color: red;
            font-size: 16px;
            text-align: center;
            margin-bottom: 20px;
        }
        /* Remember Me Toggle */
            .remember-me {
                display: flex;
                align-items: center;
                gap: 10px;
                color: black;
                margin-top: 15px;
                font-size: 18px;
            }

            /* Customize the Remember Me Toggle */
            .custom-control.custom-switch .custom-control-label::before {
                width: 40px;
                height: 22px;
                margin-top: -3px;
            }

            .custom-control.custom-switch .custom-control-label::after {
                width: 18px;
                height: 18px;
                margin-top: -3px;
            }

            /* Make the toggle switch bigger */
            .custom-control-input:checked ~ .custom-control-label::before {
                background-color: #00adb5;
                border-color: #00adb5;
            }

            .custom-control-label {
                font-size: 18px; /* Bigger font size */
                padding-left: 10px; /* Slightly increase padding */
                margin-top: -15px;
            }
            .logo-container h3 {
            font-size: 22px;
            font-weight: bold;
            color: white;
            text-shadow: 2px 2px 5px black; /* Added black shadow */
             }
            .greeting h2 {
            font-size: 40px;
            font-weight: bold;
            color: white;
            text-shadow: 4px 4px 8px black;
            }

            .text-center mt-3 {
                color: #008c9e;
                font-size: 16px;
                text-align: center;
                margin-top: 20px;
            }
    </style>
</head>
<body>

    <!-- Logo and Title -->
    

    <div class="login-container">
        <div class="box">
            <!-- Left Side - Welcome Greeting -->
            <div class="greeting">
            <div class="logo-container">
                    <img src="logo-white.png" alt="PNR Logo">
                    <h1>Bicol Express</h1>
                    <h3>Skip The Lines, Catch The Train</h3>
                </div>
                <h2>Welcome Back!</h2>
                <p>Sign in to Continue</p>
                <button class="btn" onclick="window.location.href='register.php'">Register</button>
            </div>

            <!-- Right Side - Login Form -->
            <div class="form-container">
                <?php
                if(isset($_SESSION['registration_success'])) {
                    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' 
                        . $_SESSION['registration_success'] 
                        . '<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        </div>';
                    unset($_SESSION['registration_success']);
                }

                if(isset($_GET['error'])) {
                    $error_msg = '';
                    switch($_GET['error']) {
                        case '1':
                            $error_msg = 'Invalid email or password. Please try again.';
                            break;
                        case '2':
                            $error_msg = 'An error occurred. Please try again later.';
                            break;
                    }
                    if($error_msg) {
                        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' 
                            . $error_msg 
                            . '<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            </div>';
                    }
                }
                ?>

                <form action="process_login.php" method="POST">
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="Password" required>
                    </div>
                    <div class="forgot-password">
                        <a href="forgot_password.php">Forgot Password?</a>
                    </div>
                     <!-- Remember Me Toggle Switch -->
                     <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="rememberMe" name="remember_me">
                        <label class="custom-control-label" for="rememberMe">Remember Me</label>
                    </div>
                    <button type="submit" class="btn">Login</button>
                </form>
                <p class="text-center mt-3">Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

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
