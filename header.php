<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CNN Clone - Breaking News, Latest News and Videos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }

        body {
            background-color: #f4f4f4;
            color: #333;
            line-height: 1.6;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Header Styles */
        .top-bar {
            background: #0c0c0c;
            color: white;
            padding: 8px 0;
            font-size: 12px;
            border-bottom: 3px solid #cc0000;
        }

        .user-welcome {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-options {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .user-options a {
            color: white;
            text-decoration: none;
            padding: 5px 10px;
            transition: color 0.3s;
        }

        .user-options a:hover {
            color: #cc0000;
        }

        .main-header {
            background: white;
            border-bottom: 1px solid #ddd;
            padding: 15px 0;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo {
            color: #cc0000;
            font-size: 32px;
            font-weight: bold;
            text-decoration: none;
            letter-spacing: -1px;
        }

        .logo span {
            color: #0c0c0c;
        }

        .nav-primary {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 10px;
            flex-wrap: wrap;
        }

        .nav-primary a {
            color: #0c0c0c;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
            padding: 5px 10px;
            transition: color 0.3s;
        }

        .nav-primary a:hover {
            color: #cc0000;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: #cc0000;
            color: white;
            text-decoration: none;
            border-radius: 3px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
        }

        .btn:hover {
            background: #990000;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #cc0000;
            color: #cc0000;
        }

        .btn-outline:hover {
            background: #cc0000;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        /* Search Bar */
        .search-container {
            display: flex;
            justify-content: center;
            margin: 15px 0;
        }

        .search-form {
            display: flex;
            width: 100%;
            max-width: 600px;
        }

        .search-input {
            flex: 1;
            padding: 10px 15px;
            border: 2px solid #ddd;
            border-right: none;
            font-size: 14px;
        }

        .search-btn {
            padding: 10px 20px;
            background: #cc0000;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }

        /* Breaking News Banner */
        .breaking-news {
            background: #cc0000;
            color: white;
            padding: 10px;
            margin: 15px 0;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .breaking-news-label {
            background: white;
            color: #cc0000;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-primary {
                gap: 10px;
            }
            
            .nav-primary a {
                font-size: 12px;
                padding: 3px 5px;
            }
            
            .logo {
                font-size: 24px;
            }
            
            .user-welcome {
                flex-direction: column;
                gap: 10px;
            }
            
            .user-options {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .nav-primary {
                flex-direction: column;
                align-items: center;
                gap: 5px;
            }
            
            .user-options {
                flex-wrap: wrap;
                justify-content: center;
            }
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal {
            background: white;
            padding: 30px;
            border-radius: 5px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .modal h3 {
            color: #cc0000;
            margin-bottom: 15px;
        }

        .modal p {
            margin-bottom: 20px;
            color: #666;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .modal-actions button {
            padding: 10px 20px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="container">
            <div class="user-welcome">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <div class="user-options">
                        <a href="dashboard.php">Dashboard</a>
                        <a href="create_post.php">Create Post</a>
                        <a href="index.php">Home</a>
                        <a href="logout.php">Logout</a>
                    </div>
                <?php else: ?>
                    <div class="user-options">
                        <a href="login.php">Log In</a>
                        <a href="register.php">Sign Up</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Header -->
    <header class="main-header">
        <div class="container">
            <div class="logo-container">
                <a href="index.php" class="logo">CNN<span>.com</span></a>
            </div>
            
            <!-- Primary Navigation -->
            <nav class="nav-primary">
                <a href="index.php">Home</a>
                <a href="category.php?cat=politics">Politics</a>
                <a href="category.php?cat=business">Business</a>
                <a href="category.php?cat=technology">Technology</a>
                <a href="category.php?cat=health">Health</a>
                <a href="category.php?cat=entertainment">Entertainment</a>
                <a href="category.php?cat=sports">Sports</a>
                <a href="category.php?cat=world">World</a>
            </nav>
            
            <!-- Search Bar -->
            <div class="search-container">
                <form action="search.php" method="GET" class="search-form">
                    <input type="text" name="q" class="search-input" placeholder="Search CNN..." required>
                    <button type="submit" class="search-btn">Search</button>
                </form>
            </div>
        </div>
    </header>

    <!-- Breaking News Banner -->
    <div class="container">
        <div class="breaking-news">
            <span class="breaking-news-label">BREAKING NEWS</span>
            <span class="breaking-news-text" id="breakingNewsText">Healthcare Breakthrough Announced</span>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal-overlay">
        <div class="modal">
            <h3>Confirm Delete</h3>
            <p>Are you sure you want to delete this post? This action cannot be undone.</p>
            <form id="deleteForm" method="POST">
                <div class="modal-actions">
                    <button type="submit" class="btn btn-danger">Delete</button>
                    <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <main class="container">
