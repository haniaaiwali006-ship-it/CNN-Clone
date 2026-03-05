<?php
require_once 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check if user exists
        $check_sql = "SELECT id FROM users WHERE email = ? OR username = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $email, $username);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = 'Username or email already exists.';
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_sql = "INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if ($insert_stmt->execute()) {
                $success = 'Registration successful! You can now login.';
                // Redirect to login after 2 seconds
                echo '<script>
                        setTimeout(function() {
                            window.location.href = "login.php";
                        }, 2000);
                      </script>';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
?>

<?php include 'header.php'; ?>

<div style="max-width: 400px; margin: 50px auto; padding: 30px; background: white; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <h2 style="color: #cc0000; text-align: center; margin-bottom: 30px;">Create CNN Account</h2>
    
    <?php if($error): ?>
        <div style="background: #ffcccc; color: #990000; padding: 10px; border-radius: 3px; margin-bottom: 20px;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div style="background: #ccffcc; color: #006600; padding: 10px; border-radius: 3px; margin-bottom: 20px;">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; color: #666;">Username</label>
            <input type="text" name="username" required 
                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px; font-size: 14px;"
                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; color: #666;">Email Address</label>
            <input type="email" name="email" required 
                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px; font-size: 14px;"
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; color: #666;">Password</label>
            <input type="password" name="password" required 
                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px; font-size: 14px;">
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; color: #666;">Confirm Password</label>
            <input type="password" name="confirm_password" required 
                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px; font-size: 14px;">
        </div>
        
        <button type="submit" style="width: 100%; padding: 12px; background: #cc0000; color: white; border: none; border-radius: 3px; font-size: 16px; cursor: pointer;">
            Create Account
        </button>
    </form>
    
    <div style="text-align: center; margin-top: 20px; color: #666;">
        Already have an account? <a href="login.php" style="color: #cc0000;">Log In</a>
    </div>
</div>

<?php include 'footer.php'; ?>
