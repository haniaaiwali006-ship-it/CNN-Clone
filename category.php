<?php
require_once 'config.php';

if (!isset($_GET['cat']) || !array_key_exists($_GET['cat'], $categories)) {
    header('Location: index.php');
    exit();
}

$category = $_GET['cat'];
$category_name = $categories[$category];

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Get total posts for pagination
$count_sql = "SELECT COUNT(*) as total FROM posts WHERE category = ? AND status = 'published'";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("s", $category);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_posts = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_posts / $limit);

// Get posts for current page
$sql = "SELECT p.*, u.username 
        FROM posts p 
        JOIN users u ON p.author_id = u.id 
        WHERE p.category = ? AND p.status = 'published' 
        ORDER BY p.created_at DESC 
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $category, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
$posts = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
}
?>

<?php include 'header.php'; ?>

<div style="margin: 30px 0;">
    <h1 style="color: #cc0000; margin-bottom: 10px; font-size: 32px;">
        <?php echo htmlspecialchars($category_name); ?>
    </h1>
    <p style="color: #666; margin-bottom: 30px;">Latest news and articles in <?php echo htmlspecialchars($category_name); ?></p>
    
    <?php if(empty($posts)): ?>
        <div style="text-align: center; padding: 50px; background: white; border-radius: 5px;">
            <h3 style="color: #666; margin-bottom: 15px;">No articles found in this category.</h3>
            <p style="color: #888;">Check back later for new content.</p>
        </div>
    <?php else: ?>
        <!-- Featured Post (first post) -->
        <?php if(!empty($posts)): ?>
            <div style="margin-bottom: 40px;">
                <?php $featured = $posts[0]; ?>
                <article style="border: 1px solid #ddd; border-radius: 5px; overflow: hidden; background: white;">
                    <?php if($featured['featured_image']): ?>
                        <img src="<?php echo htmlspecialchars($featured['featured_image']); ?>" 
                             alt="<?php echo htmlspecialchars($featured['title']); ?>"
                             style="width: 100%; height: 400px; object-fit: cover;">
                    <?php endif; ?>
                    <div style="padding: 30px;">
                        <h2 style="margin-bottom: 15px; font-size: 28px;">
                            <a href="view_post.php?id=<?php echo $featured['id']; ?>" 
                               style="color: #0c0c0c; text-decoration: none;">
                                <?php echo htmlspecialchars($featured['title']); ?>
                            </a>
                        </h2>
                        <p style="color: #666; margin-bottom: 20px; font-size: 16px; line-height: 1.6;">
                            <?php echo substr(strip_tags($featured['content']), 0, 300); ?>...
                        </p>
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 14px; color: #888;">
                            <span>By <?php echo htmlspecialchars($featured['username']); ?></span>
                            <span><?php echo date('F j, Y', strtotime($featured['created_at'])); ?></span>
                        </div>
                    </div>
                </article>
            </div>
        <?php endif; ?>
        
        <!-- Grid of Posts -->
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; margin-bottom: 40px;">
            <?php for($i = 1; $i < count($posts); $i++): ?>
                <?php $post = $posts[$i]; ?>
                <article style="background: white; border-radius: 5px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                    <?php if($post['featured_image']): ?>
                        <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" 
                             alt="<?php echo htmlspecialchars($post['title']); ?>"
                             style="width: 100%; height: 200px; object-fit: cover;">
                    <?php endif; ?>
                    <div style="padding: 20px;">
                        <h3 style="margin-bottom: 10px; font-size: 18px;">
                            <a href="view_post.php?id=<?php echo $post['id']; ?>" 
                               style="color: #0c0c0c; text-decoration: none; line-height: 1.4;">
                                <?php echo htmlspecialchars($post['title']); ?>
                            </a>
                        </h3>
                        <p style="color: #666; margin-bottom: 15px; font-size: 14px;">
                            <?php echo substr(strip_tags($post['content']), 0, 150); ?>...
                        </p>
                        <div style="font-size: 12px; color: #888;">
                            <span>By <?php echo htmlspecialchars($post['username']); ?></span>
                            <span style="float: right;"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                        </div>
                    </div>
                </article>
            <?php endfor; ?>
        </div>
        
        <!-- Pagination -->
        <?php if($total_pages > 1): ?>
            <div style="display: flex; justify-content: center; gap: 10px; margin-top: 40px;">
                <?php if($page > 1): ?>
                    <a href="category.php?cat=<?php echo $category; ?>&page=<?php echo $page - 1; ?>"
                       style="padding: 8px 15px; background: white; color: #0c0c0c; text-decoration: none; border: 1px solid #ddd; border-radius: 3px;">
                        Previous
                    </a>
                <?php endif; ?>
                
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="category.php?cat=<?php echo $category; ?>&page=<?php echo $i; ?>"
                       style="padding: 8px 12px; background: <?php echo $i == $page ? '#cc0000' : 'white'; ?>; 
                              color: <?php echo $i == $page ? 'white' : '#0c0c0c'; ?>; 
                              text-decoration: none; border: 1px solid #ddd; border-radius: 3px;">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if($page < $total_pages): ?>
                    <a href="category.php?cat=<?php echo $category; ?>&page=<?php echo $page + 1; ?>"
                       style="padding: 8px 15px; background: white; color: #0c0c0c; text-decoration: none; border: 1px solid #ddd; border-radius: 3px;">
                        Next
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
