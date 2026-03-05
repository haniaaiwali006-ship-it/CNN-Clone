<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        // Check user
        $sql = "SELECT id, username, email, password FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                
                // Redirect to dashboard
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>

<?php include 'header.php'; ?>

<div style="max-width: 400px; margin: 50px auto; padding: 30px; background: white; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
    <h2 style="color: #cc0000; text-align: center; margin-bottom: 30px;">Log In to CNN</h2>
    
    <?php if($error): ?>
        <div style="background: #ffcccc; color: #990000; padding: 10px; border-radius: 3px; margin-bottom: 20px;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; color: #666;">Email Address</label>
            <input type="email" name="email" required 
                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px; font-size: 14px;"
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; color: #666;">Password</label>
            <input type="password" name="password" required 
                   style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px; font-size: 14px;">
        </div>
        
        <button type="submit" style="width: 100%; padding: 12px; background: #cc0000; color: white; border: none; border-radius: 3px; font-size: 16px; cursor: pointer;">
            Log In
        </button>
    </form>
    
    <div style="text-align: center; margin-top: 20px; color: #666;">
        Don't have an account? <a href="register.php" style="color: #cc0000;">Sign Up</a>
    </div>
</div>

<?php include 'footer.php'; ?>
