<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$is_edit = isset($_GET['edit']);
$post_id = $is_edit ? intval($_GET['edit']) : 0;
$post = null;
$success = '';
$error = '';

// If editing, fetch post data
if ($is_edit && $post_id > 0) {
    $sql = "SELECT * FROM posts WHERE id = ? AND author_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $post = $result->fetch_assoc();
    } else {
        $error = "Post not found or you don't have permission to edit it.";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category = $_POST['category'];
    $status = $_POST['status'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $featured_image = trim($_POST['featured_image']);
    $excerpt = trim($_POST['excerpt']);
    
    // Validation
    if (empty($title) || empty($content) || empty($category)) {
        $error = 'Please fill in all required fields.';
    } else {
        if ($is_edit && $post_id > 0) {
            // Update existing post
            $sql = "UPDATE posts SET 
                    title = ?, 
                    content = ?, 
                    category = ?, 
                    status = ?, 
                    is_featured = ?, 
                    featured_image = ?, 
                    excerpt = ?,
                    updated_at = NOW() 
                    WHERE id = ? AND author_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssissii", $title, $content, $category, $status, $is_featured, $featured_image, $excerpt, $post_id, $user_id);
        } else {
            // Insert new post
            $sql = "INSERT INTO posts (title, content, category, status, is_featured, featured_image, excerpt, author_id, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssissi", $title, $content, $category, $status, $is_featured, $featured_image, $excerpt, $user_id);
        }
        
        if ($stmt->execute()) {
            $success = $is_edit ? 'Post updated successfully!' : 'Post created successfully!';
            $post_id = $is_edit ? $post_id : $stmt->insert_id;
            
            // Redirect after 2 seconds
            echo '<script>
                    setTimeout(function() {
                        window.location.href = "view_post.php?id=' . $post_id . '";
                    }, 2000);
                  </script>';
        } else {
            $error = 'Error saving post. Please try again.';
        }
    }
}
?>

<?php include 'header.php'; ?>

<div style="max-width: 1000px; margin: 30px auto;">
    <h1 style="color: #cc0000; margin-bottom: 30px;">
        <?php echo $is_edit ? 'Edit Article' : 'Create New Article'; ?>
    </h1>
    
    <?php if($error): ?>
        <div style="background: #ffcccc; color: #990000; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div style="background: #ccffcc; color: #006600; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <?php echo htmlspecialchars($success); ?>
            <div style="margin-top: 10px;">Redirecting to post...</div>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="" style="background: white; padding: 30px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; color: #666; font-weight: bold;">Title *</label>
            <input type="text" name="title" required 
                   style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 3px; font-size: 16px;"
                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ($post['title'] ?? ''); ?>"
                   placeholder="Enter article title">
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; color: #666; font-weight: bold;">Excerpt</label>
            <textarea name="excerpt" 
                     style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 3px; font-size: 14px; height: 80px;"
                     placeholder="Brief summary of the article"><?php echo isset($_POST['excerpt']) ? htmlspecialchars($_POST['excerpt']) : ($post['excerpt'] ?? ''); ?></textarea>
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; color: #666; font-weight: bold;">Featured Image URL</label>
            <input type="url" name="featured_image" 
                   style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 3px; font-size: 14px;"
                   value="<?php echo isset($_POST['featured_image']) ? htmlspecialchars($_POST['featured_image']) : ($post['featured_image'] ?? ''); ?>"
                   placeholder="https://example.com/image.jpg">
            <small style="color: #888; margin-top: 5px; display: block;">Enter a direct image URL for the featured image</small>
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 8px; color: #666; font-weight: bold;">Content *</label>
            <textarea name="content" required id="content-editor"
                     style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 3px; font-size: 14px; height: 300px; font-family: monospace;"
                     placeholder="Write your article content here..."><?php echo isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ($post['content'] ?? ''); ?></textarea>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px;">
            <div>
                <label style="display: block; margin-bottom: 8px; color: #666; font-weight: bold;">Category *</label>
                <select name="category" required 
                        style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 3px; font-size: 14px;">
                    <option value="">Select Category</option>
                    <?php foreach($categories as $key => $name): ?>
                        <option value="<?php echo $key; ?>"
                                <?php echo (isset($_POST['category']) && $_POST['category'] == $key) || (isset($post['category']) && $post['category'] == $key) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <label style="display: block; margin-bottom: 8px; color: #666; font-weight: bold;">Status</label>
                <select name="status" 
                        style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 3px; font-size: 14px;">
                    <option value="draft" <?php echo (isset($_POST['status']) && $_POST['status'] == 'draft') || (isset($post['status']) && $post['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                    <option value="published" <?php echo (isset($_POST['status']) && $_POST['status'] == 'published') || (isset($post['status']) && $post['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
                </select>
            </div>
        </div>
        
        <div style="margin-bottom: 25px;">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" name="is_featured" value="1"
                       <?php echo (isset($_POST['is_featured']) || (isset($post['is_featured']) && $post['is_featured'] == 1)) ? 'checked' : ''; ?>
                       style="transform: scale(1.2);">
                <span style="color: #666; font-weight: bold;">Feature this article on homepage</span>
            </label>
        </div>
        
        <div style="display: flex; gap: 15px;">
            <button type="submit" class="btn" style="padding: 12px 30px; font-size: 16px;">
                <?php echo $is_edit ? 'Update Article' : 'Publish Article'; ?>
            </button>
            <a href="dashboard.php" class="btn-outline" style="padding: 12px 30px;">Cancel</a>
        </div>
    </form>
    
    <!-- Rich Text Editor Instructions -->
    <div style="background: #f8f8f8; padding: 20px; border-radius: 5px; margin-top: 30px; border-left: 4px solid #cc0000;">
        <h4 style="color: #cc0000; margin-bottom: 10px;">Formatting Tips:</h4>
        <ul style="color: #666; list-style: none;">
            <li style="margin-bottom: 5px;">• Use <code>**bold**</code> for <strong>bold text</strong></li>
            <li style="margin-bottom: 5px;">• Use <code>*italic*</code> for <em>italic text</em></li>
            <li style="margin-bottom: 5px;">• Use <code># Heading</code> for headings</li>
            <li style="margin-bottom: 5px;">• Use <code>- item</code> for bullet points</li>
            <li style="margin-bottom: 5px;">• Use <code>[link text](URL)</code> for links</li>
        </ul>
    </div>
</div>

<script>
    // Simple text editor enhancements
    document.addEventListener('DOMContentLoaded', function() {
        const editor = document.getElementById('content-editor');
        
        // Add keyboard shortcuts
        editor.addEventListener('keydown', function(e) {
            // Ctrl+B for bold
            if (e.ctrlKey && e.key === 'b') {
                e.preventDefault();
                wrapSelection('**', '**');
            }
            // Ctrl+I for italic
            else if (e.ctrlKey && e.key === 'i') {
                e.preventDefault();
                wrapSelection('*', '*');
            }
        });
        
        function wrapSelection(startTag, endTag) {
            const start = editor.selectionStart;
            const end = editor.selectionEnd;
            const selectedText = editor.value.substring(start, end);
            const newText = startTag + selectedText + endTag;
            
            editor.value = editor.value.substring(0, start) + newText + editor.value.substring(end);
            
            // Set cursor position after the inserted tags
            editor.selectionStart = editor.selectionEnd = end + startTag.length + endTag.length;
            editor.focus();
        }
    });
</script>

<?php include 'footer.php'; ?>

<!-- Delete Button for Existing Posts -->
<?php if($is_edit && $post_id > 0): ?>
    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
        <h3 style="color: #dc3545; margin-bottom: 15px;">Danger Zone</h3>
        <form method="POST" action="dashboard.php" 
              onsubmit="return confirm('Are you absolutely sure? This will permanently delete this post and all its comments.');">
            <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
            <input type="hidden" name="delete_post" value="1">
            <button type="submit" class="btn btn-danger" style="padding: 10px 20px;">
                Delete This Post
            </button>
            <small style="color: #666; display: block; margin-top: 5px;">Once deleted, this post cannot be recovered.</small>
        </form>
    </div>
<?php endif; ?>
