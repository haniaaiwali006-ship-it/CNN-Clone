<?php
require_once 'config.php';

// Get latest posts
$sql = "SELECT p.*, u.username, u.email as author_email 
        FROM posts p 
        JOIN users u ON p.author_id = u.id 
        WHERE p.status = 'published' 
        ORDER BY p.created_at DESC 
        LIMIT 12";

$result = $conn->query($sql);
$posts = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
}

// Get featured posts (marked as featured)
$featured_sql = "SELECT p.*, u.username 
                 FROM posts p 
                 JOIN users u ON p.author_id = u.id 
                 WHERE p.status = 'published' AND p.is_featured = 1 
                 ORDER BY p.created_at DESC 
                 LIMIT 3";
$featured_result = $conn->query($featured_sql);
$featured_posts = [];
if ($featured_result->num_rows > 0) {
    while($row = $featured_result->fetch_assoc()) {
        $featured_posts[] = $row;
    }
}
?>

<?php include 'header.php'; ?>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin: 30px 0;">
    <!-- Main Content -->
    <div>
        <!-- Featured Section -->
        <?php if(!empty($featured_posts)): ?>
            <div style="margin-bottom: 40px;">
                <h2 style="color: #cc0000; border-bottom: 2px solid #cc0000; padding-bottom: 10px; margin-bottom: 20px;">FEATURED</h2>
                <div style="display: grid; gap: 20px;">
                    <?php foreach($featured_posts as $post): ?>
                        <article style="border: 1px solid #ddd; border-radius: 5px; overflow: hidden; background: white;">
                            <?php if($post['featured_image']): ?>
                                <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" 
                                     alt="<?php echo htmlspecialchars($post['title']); ?>"
                                     style="width: 100%; height: 300px; object-fit: cover;">
                            <?php endif; ?>
                            <div style="padding: 20px;">
                                <span style="background: #cc0000; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px; display: inline-block; margin-bottom: 10px;">
                                    <?php echo htmlspecialchars($categories[$post['category']] ?? $post['category']); ?>
                                </span>
                                <h3 style="margin-bottom: 10px;">
                                    <a href="view_post.php?id=<?php echo $post['id']; ?>" 
                                       style="color: #0c0c0c; text-decoration: none; font-size: 22px; line-height: 1.3;">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </a>
                                </h3>
                                <p style="color: #666; margin-bottom: 15px;">
                                    <?php echo substr(strip_tags($post['content']), 0, 200); ?>...
                                </p>
                                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 14px; color: #888;">
                                    <span>By <?php echo htmlspecialchars($post['username']); ?></span>
                                    <span><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Latest News -->
        <div>
            <h2 style="color: #cc0000; border-bottom: 2px solid #cc0000; padding-bottom: 10px; margin-bottom: 20px;">LATEST NEWS</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <?php foreach($posts as $post): ?>
                    <article style="background: white; border-radius: 5px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                        <?php if($post['featured_image']): ?>
                            <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" 
                                 alt="<?php echo htmlspecialchars($post['title']); ?>"
                                 style="width: 100%; height: 180px; object-fit: cover;">
                        <?php endif; ?>
                        <div style="padding: 15px;">
                            <span style="background: #f0f0f0; color: #666; padding: 2px 6px; border-radius: 3px; font-size: 11px; display: inline-block; margin-bottom: 8px;">
                                <?php echo htmlspecialchars($categories[$post['category']] ?? $post['category']); ?>
                            </span>
                            <h4 style="margin-bottom: 10px;">
                                <a href="view_post.php?id=<?php echo $post['id']; ?>" 
                                   style="color: #0c0c0c; text-decoration: none; font-size: 16px; line-height: 1.4;">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </a>
                            </h4>
                            <p style="color: #666; font-size: 14px; margin-bottom: 10px;">
                                <?php echo substr(strip_tags($post['content']), 0, 100); ?>...
                            </p>
                            <div style="font-size: 12px; color: #888;">
                                <span>By <?php echo htmlspecialchars($post['username']); ?></span>
                                <span style="float: right;"><?php echo date('M j', strtotime($post['created_at'])); ?></span>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div>
        <!-- Categories Widget -->
        <div style="background: white; padding: 20px; border-radius: 5px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h3 style="color: #cc0000; margin-bottom: 15px;">CATEGORIES</h3>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <?php foreach($categories as $key => $name): ?>
                    <a href="category.php?cat=<?php echo $key; ?>" 
                       style="display: block; padding: 10px; background: #f8f8f8; color: #0c0c0c; text-decoration: none; border-radius: 3px; transition: background 0.3s;">
                        <?php echo htmlspecialchars($name); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Trending Posts -->
        <div style="background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
            <h3 style="color: #cc0000; margin-bottom: 15px;">TRENDING NOW</h3>
            <?php
            $trending_sql = "SELECT p.*, u.username 
                           FROM posts p 
                           JOIN users u ON p.author_id = u.id 
                           WHERE p.status = 'published' 
                           ORDER BY p.views DESC 
                           LIMIT 5";
            $trending_result = $conn->query($trending_sql);
            if ($trending_result->num_rows > 0):
                while($trending = $trending_result->fetch_assoc()):
            ?>
                <div style="padding: 10px 0; border-bottom: 1px solid #eee;">
                    <a href="view_post.php?id=<?php echo $trending['id']; ?>" 
                       style="color: #0c0c0c; text-decoration: none; font-size: 14px; line-height: 1.4;">
                        <?php echo htmlspecialchars($trending['title']); ?>
                    </a>
                    <div style="font-size: 12px; color: #888; margin-top: 5px;">
                        <?php echo date('M j', strtotime($trending['created_at'])); ?>
                    </div>
                </div>
            <?php 
                endwhile;
            endif;
            ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
