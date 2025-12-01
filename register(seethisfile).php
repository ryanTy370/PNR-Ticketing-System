<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('error_log', 'php_errors.log');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .alert {
            animation: fadeOut 5s forwards;
            animation-delay: 3s;
        }
        @keyframes fadeOut {
            from {opacity: 1;}
            to {opacity: 0;}
        }
    </style>
</head>
<body>
    <div class="container mt-5">
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
        <div class="card">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Register</h2>
                <form action="process_register.php" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" name="firstname" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Middle Initial</label>
                                <input type="text" name="middle" class="form-control" maxlength="5">
                            </div>
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" name="lastname" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" name="password" class="form-control" required 
                                       pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" 
                                       title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters">
                            </div>
                            <div class="form-group">
                                <label>Date of Birth</label>
                                <input type="date" name="dob" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="tel" name="phone" class="form-control" pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number" required>
                            </div>
                            <div class="form-group">
                                <label>Address</label>
                                <textarea name="address" class="form-control" required rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block mt-4">Register</button>
                </form>
                <p class="text-center mt-3">Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>

    <!-- Add Bootstrap JS and its dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
