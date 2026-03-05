<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Handle post deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_post'])) {
    $post_id = intval($_POST['post_id']);
    
    // Verify user owns the post
    $check_sql = "SELECT id FROM posts WHERE id = ? AND author_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $post_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 1) {
        // Delete post
        $delete_sql = "DELETE FROM posts WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $post_id);
        
        if ($delete_stmt->execute()) {
            $success = "Post deleted successfully!";
        } else {
            $error = "Error deleting post.";
        }
    } else {
        $error = "Post not found or you don't have permission to delete it.";
    }
}

// Get user's posts
$sql = "SELECT * FROM posts WHERE author_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$posts = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
}

// Get user stats
$stats_sql = "SELECT 
              COUNT(*) as total_posts,
              SUM(views) as total_views,
              SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_posts
              FROM posts WHERE author_id = ?";
$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param("i", $user_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
?>

<?php include 'header.php'; ?>

<div style="max-width: 1000px; margin: 30px auto;">
    <h1 style="color: #cc0000; margin-bottom: 30px;">Your CNN Dashboard</h1>
    
    <?php if(isset($success)): ?>
        <div style="background: #ccffcc; color: #006600; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    
    <?php if(isset($error)): ?>
        <div style="background: #ffcccc; color: #990000; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <!-- Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center;">
            <div style="font-size: 32px; color: #cc0000; font-weight: bold;"><?php echo $stats['total_posts'] ?? 0; ?></div>
            <div style="color: #666;">Total Posts</div>
        </div>
        
        <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center;">
            <div style="font-size: 32px; color: #cc0000; font-weight: bold;"><?php echo $stats['published_posts'] ?? 0; ?></div>
            <div style="color: #666;">Published</div>
        </div>
        
        <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); text-align: center;">
            <div style="font-size: 32px; color: #cc0000; font-weight: bold;"><?php echo $stats['total_views'] ?? 0; ?></div>
            <div style="color: #666;">Total Views</div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div style="background: white; padding: 20px; border-radius: 5px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <h3 style="color: #cc0000; margin-bottom: 15px;">Quick Actions</h3>
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <a href="create_post.php" class="btn" style="padding: 10px 20px;">Create New Post</a>
            <a href="index.php" class="btn-outline" style="padding: 10px 20px;">View Homepage</a>
            <a href="logout.php" style="padding: 10px 20px; background: #666; color: white; text-decoration: none; border-radius: 3px;">Logout</a>
        </div>
    </div>
    
    <!-- User's Posts -->
    <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="color: #cc0000;">Your Articles</h3>
            <a href="create_post.php" class="btn" style="padding: 8px 16px;">+ New Article</a>
        </div>
        
        <?php if(empty($posts)): ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                <p>You haven't created any articles yet.</p>
                <a href="create_post.php" class="btn" style="margin-top: 10px;">Create Your First Article</a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f8f8f8;">
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Title</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Category</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Status</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Views</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Date</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($posts as $post): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px;">
                                    <a href="view_post.php?id=<?php echo $post['id']; ?>" style="color: #0c0c0c; text-decoration: none;">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </a>
                                </td>
                                <td style="padding: 12px;">
                                    <span style="background: #f0f0f0; padding: 3px 8px; border-radius: 3px; font-size: 12px;">
                                        <?php echo htmlspecialchars($categories[$post['category']] ?? $post['category']); ?>
                                    </span>
                                </td>
                                <td style="padding: 12px;">
                                    <span style="padding: 3px 8px; border-radius: 3px; font-size: 12px; 
                                          background: <?php echo $post['status'] == 'published' ? '#ccffcc' : '#ffffcc'; ?>;
                                          color: <?php echo $post['status'] == 'published' ? '#006600' : '#666600'; ?>;">
                                        <?php echo ucfirst($post['status']); ?>
                                    </span>
                                </td>
                                <td style="padding: 12px;"><?php echo $post['views']; ?></td>
                                <td style="padding: 12px;"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></td>
                                <td style="padding: 12px;">
                                    <div style="display: flex; gap: 10px;">
                                        <a href="create_post.php?edit=<?php echo $post['id']; ?>" 
                                           style="color: #cc0000; text-decoration: none;">Edit</a>
                                        <a href="#" onclick="confirmDelete(<?php echo $post['id']; ?>)" 
                                           style="color: #dc3545; text-decoration: none;">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function confirmDelete(postId) {
        const modal = document.getElementById('deleteModal');
        const form = document.getElementById('deleteForm');
        
        // Set up the form action
        form.action = 'dashboard.php';
        form.innerHTML = `
            <input type="hidden" name="post_id" value="${postId}">
            <input type="hidden" name="delete_post" value="1">
            <h3>Confirm Delete</h3>
            <p>Are you sure you want to delete this post? This action cannot be undone.</p>
            <div class="modal-actions">
                <button type="submit" class="btn btn-danger">Delete</button>
                <button type="button" class="btn btn-outline" onclick="closeModal()">Cancel</button>
            </div>
        `;
        
        // Show modal
        modal.style.display = 'flex';
    }

    function closeModal() {
        const modal = document.getElementById('deleteModal');
        modal.style.display = 'none';
    }

    // Close modal when clicking outside
    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
</script>

<?php include 'footer.php'; ?>
