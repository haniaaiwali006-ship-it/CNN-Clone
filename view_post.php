<?php
require_once 'config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$post_id = intval($_GET['id']);

// Fetch post with author info
$sql = "SELECT p.*, u.username, u.email as author_email 
        FROM posts p 
        JOIN users u ON p.author_id = u.id 
        WHERE p.id = ? AND p.status = 'published'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit();
}

$post = $result->fetch_assoc();

// Update view count
$update_sql = "UPDATE posts SET views = views + 1 WHERE id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("i", $post_id);
$update_stmt->execute();

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    if (!isset($_SESSION['user_id'])) {
        $error = 'Please login to comment.';
    } else {
        $comment = trim($_POST['comment_content']);
        if (!empty($comment)) {
            $user_id = $_SESSION['user_id'];
            $comment_sql = "INSERT INTO comments (post_id, user_id, comment, created_at) VALUES (?, ?, ?, NOW())";
            $comment_stmt = $conn->prepare($comment_sql);
            $comment_stmt->bind_param("iis", $post_id, $user_id, $comment);
            $comment_stmt->execute();
            
            // Clear comment input
            unset($_POST['comment_content']);
        }
    }
}

// Fetch comments
$comments_sql = "SELECT c.*, u.username 
                 FROM comments c 
                 JOIN users u ON c.user_id = u.id 
                 WHERE c.post_id = ? 
                 ORDER BY c.created_at DESC";
$comments_stmt = $conn->prepare($comments_sql);
$comments_stmt->bind_param("i", $post_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();
$comments = [];
while($comment = $comments_result->fetch_assoc()) {
    $comments[] = $comment;
}

// Fetch related posts
$related_sql = "SELECT p.*, u.username 
                FROM posts p 
                JOIN users u ON p.author_id = u.id 
                WHERE p.category = ? AND p.id != ? AND p.status = 'published' 
                ORDER BY p.created_at DESC 
                LIMIT 3";
$related_stmt = $conn->prepare($related_sql);
$related_stmt->bind_param("si", $post['category'], $post_id);
$related_stmt->execute();
$related_result = $related_stmt->get_result();
$related_posts = [];
while($related = $related_result->fetch_assoc()) {
    $related_posts[] = $related;
}
?>

<?php include 'header.php'; ?>

<article style="max-width: 800px; margin: 30px auto;">
    <!-- Post Header -->
    <header style="margin-bottom: 30px;">
        <span style="background: #cc0000; color: white; padding: 5px 10px; border-radius: 3px; font-size: 14px; display: inline-block; margin-bottom: 15px;">
            <?php echo htmlspecialchars($categories[$post['category']] ?? $post['category']); ?>
        </span>
        
        <h1 style="font-size: 36px; line-height: 1.2; margin-bottom: 20px; color: #0c0c0c;">
            <?php echo htmlspecialchars($post['title']); ?>
        </h1>
        
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px; color: #666; font-size: 14px;">
            <span>By <?php echo htmlspecialchars($post['username']); ?></span>
            <span>•</span>
            <span><?php echo date('F j, Y, g:i a', strtotime($post['created_at'])); ?></span>
            <span>•</span>
            <span><?php echo $post['views']; ?> views</span>
            
            <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['author_id']): ?>
                <span>•</span>
                <a href="create_post.php?edit=<?php echo $post['id']; ?>" style="color: #cc0000;">Edit</a>
            <?php endif; ?>
        </div>
        
        <?php if($post['featured_image']): ?>
            <div style="margin-bottom: 30px;">
                <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" 
                     alt="<?php echo htmlspecialchars($post['title']); ?>"
                     style="width: 100%; height: auto; border-radius: 5px;">
            </div>
        <?php endif; ?>
        
        <?php if($post['excerpt']): ?>
            <div style="background: #f8f8f8; padding: 20px; border-left: 4px solid #cc0000; margin-bottom: 30px; font-size: 18px; line-height: 1.6; color: #333;">
                <?php echo htmlspecialchars($post['excerpt']); ?>
            </div>
        <?php endif; ?>
    </header>
    
    <!-- Post Content -->
    <div style="font-size: 18px; line-height: 1.8; color: #333; margin-bottom: 50px;">
        <?php
        // Simple markdown-like formatting
        $content = htmlspecialchars($post['content']);
        
        // Convert **text** to <strong>text</strong>
        $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content);
        
        // Convert *text* to <em>text</em>
        $content = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $content);
        
        // Convert # Heading to <h3>
        $content = preg_replace('/^# (.*?)$/m', '<h3 style="margin: 20px 0 10px 0; color: #0c0c0c;">$1</h3>', $content);
        
        // Convert ## Heading to <h4>
        $content = preg_replace('/^## (.*?)$/m', '<h4 style="margin: 15px 0 8px 0; color: #0c0c0c;">$1</h4>', $content);
        
        // Convert - item to <li>
        $content = preg_replace('/^- (.*?)$/m', '<li>$1</li>', $content);
        $content = preg_replace('/(<li>.*<\/li>)/s', '<ul style="margin: 15px 0; padding-left: 20px;">$1</ul>', $content);
        
        // Convert paragraphs
        $content = preg_replace('/(.+)(?:\n\n|$)/m', '<p style="margin-bottom: 20px;">$1</p>', $content);
        
        // Convert URLs to links
        $content = preg_replace('/(https?:\/\/[^\s]+)/', '<a href="$1" style="color: #cc0000;">$1</a>', $content);
        
        // Convert [text](url) to links
        $content = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2" style="color: #cc0000;">$1</a>', $content);
        
        echo nl2br($content);
        ?>
    </div>
    
    <!-- Author Bio -->
    <div style="background: #f8f8f8; padding: 20px; border-radius: 5px; margin-bottom: 40px; border-top: 3px solid #cc0000;">
        <h3 style="color: #cc0000; margin-bottom: 10px;">About the Author</h3>
        <p style="color: #666; margin-bottom: 10px;">
            <strong><?php echo htmlspecialchars($post['username']); ?></strong> is a contributor to CNN Clone.
        </p>
        <p style="color: #666;">All articles published by <?php echo htmlspecialchars($post['username']); ?> represent their own views and opinions.</p>
    </div>
    
    <!-- Related Articles -->
    <?php if(!empty($related_posts)): ?>
        <div style="margin-bottom: 40px;">
            <h3 style="color: #cc0000; border-bottom: 2px solid #cc0000; padding-bottom: 10px; margin-bottom: 20px;">RELATED ARTICLES</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <?php foreach($related_posts as $related): ?>
                    <article style="background: white; border-radius: 5px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                        <?php if($related['featured_image']): ?>
                            <img src="<?php echo htmlspecialchars($related['featured_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($related['title']); ?>"
                                 style="width: 100%; height: 150px; object-fit: cover;">
                        <?php endif; ?>
                        <div style="padding: 15px;">
                            <span style="background: #f0f0f0; color: #666; padding: 2px 6px; border-radius: 3px; font-size: 11px; display: inline-block; margin-bottom: 8px;">
                                <?php echo htmlspecialchars($categories[$related['category']] ?? $related['category']); ?>
                            </span>
                            <h4 style="margin-bottom: 10px;">
                                <a href="view_post.php?id=<?php echo $related['id']; ?>" 
                                   style="color: #0c0c0c; text-decoration: none; font-size: 16px; line-height: 1.4;">
                                    <?php echo htmlspecialchars($related['title']); ?>
                                </a>
                            </h4>
                            <div style="font-size: 12px; color: #888;">
                                <?php echo date('M j, Y', strtotime($related['created_at'])); ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Comments Section -->
    <div id="comments" style="margin-bottom: 40px;">
        <h3 style="color: #cc0000; border-bottom: 2px solid #cc0000; padding-bottom: 10px; margin-bottom: 20px;">
            COMMENTS (<?php echo count($comments); ?>)
        </h3>
        
        <!-- Comment Form -->
        <?php if(isset($_SESSION['user_id'])): ?>
            <div style="background: white; padding: 20px; border-radius: 5px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                <h4 style="margin-bottom: 15px; color: #0c0c0c;">Add a Comment</h4>
                <form method="POST" action="">
                    <textarea name="comment_content" required 
                             style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 3px; font-size: 14px; height: 100px; margin-bottom: 15px;"
                             placeholder="Share your thoughts..."><?php echo isset($_POST['comment_content']) ? htmlspecialchars($_POST['comment_content']) : ''; ?></textarea>
                    <button type="submit" name="comment" class="btn" style="padding: 10px 20px;">Post Comment</button>
                </form>
            </div>
        <?php else: ?>
            <div style="background: #f8f8f8; padding: 20px; border-radius: 5px; margin-bottom: 30px; text-align: center;">
                <p style="color: #666; margin-bottom: 15px;">Please <a href="login.php" style="color: #cc0000;">log in</a> to leave a comment.</p>
                <a href="login.php" class="btn" style="padding: 8px 16px;">Log In</a>
            </div>
        <?php endif; ?>
        
        <!-- Comments List -->
        <div>
            <?php if(empty($comments)): ?>
                <div style="text-align: center; padding: 30px; color: #666; background: white; border-radius: 5px;">
                    No comments yet. Be the first to comment!
                </div>
            <?php else: ?>
                <?php foreach($comments as $comment): ?>
                    <div style="background: white; padding: 20px; border-radius: 5px; margin-bottom: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <strong style="color: #0c0c0c;"><?php echo htmlspecialchars($comment['username']); ?></strong>
                            <span style="color: #888; font-size: 12px;">
                                <?php echo date('M j, Y g:i a', strtotime($comment['created_at'])); ?>
                            </span>
                        </div>
                        <p style="color: #333; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</article>

<?php include 'footer.php'; ?>

<!-- Author Bio -->
<div style="background: #f8f8f8; padding: 20px; border-radius: 5px; margin-bottom: 40px; border-top: 3px solid #cc0000;">
    <h3 style="color: #cc0000; margin-bottom: 10px;">About the Author</h3>
    <p style="color: #666; margin-bottom: 10px;">
        <strong><?php echo htmlspecialchars($post['username']); ?></strong> is a contributor to CNN Clone.
    </p>
    <p style="color: #666;">All articles published by <?php echo htmlspecialchars($post['username']); ?> represent their own views and opinions.</p>
    
    <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $post['author_id']): ?>
        <div style="margin-top: 15px; display: flex; gap: 10px;">
            <a href="create_post.php?edit=<?php echo $post['id']; ?>" class="btn" style="padding: 8px 16px; font-size: 14px;">Edit Post</a>
            <form method="POST" action="dashboard.php" style="display: inline;">
                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                <input type="hidden" name="delete_post" value="1">
                <button type="submit" class="btn btn-danger" style="padding: 8px 16px; font-size: 14px;" 
                        onclick="return confirm('Are you sure you want to delete this post? This action cannot be undone.');">
                    Delete Post
                </button>
            </form>
        </div>
    <?php endif; ?>
</div>
