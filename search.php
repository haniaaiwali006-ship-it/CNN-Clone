<?php
require_once 'config.php';

if (!isset($_GET['q']) || empty(trim($_GET['q']))) {
    header('Location: index.php');
    exit();
}

$search_query = trim($_GET['q']);
$search_terms = "%" . $search_query . "%";

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Get total results for pagination
$count_sql = "SELECT COUNT(*) as total 
              FROM posts p 
              JOIN users u ON p.author_id = u.id 
              WHERE p.status = 'published' 
              AND (p.title LIKE ? OR p.content LIKE ? OR p.excerpt LIKE ? OR u.username LIKE ?)";
$count_stmt = $conn->prepare($count_sql);
$count_stmt->bind_param("ssss", $search_terms, $search_terms, $search_terms, $search_terms);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_results = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_results / $limit);

// Get search results
$sql = "SELECT p.*, u.username 
        FROM posts p 
        JOIN users u ON p.author_id = u.id 
        WHERE p.status = 'published' 
        AND (p.title LIKE ? OR p.content LIKE ? OR p.excerpt LIKE ? OR u.username LIKE ?) 
        ORDER BY p.created_at DESC 
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssii", $search_terms, $search_terms, $search_terms, $search_terms, $limit, $offset);
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
    <h1 style="color: #cc0000; margin-bottom: 10px;">Search Results</h1>
    <p style="color: #666; margin-bottom: 30px;">
        Found <?php echo $total_results; ?> result<?php echo $total_results != 1 ? 's' : ''; ?> for "<?php echo htmlspecialchars($search_query); ?>"
    </p>
    
    <?php if(empty($posts)): ?>
        <div style="text-align: center; padding: 50px; background: white; border-radius: 5px;">
            <h3 style="color: #666; margin-bottom: 15px;">No results found for "<?php echo htmlspecialchars($search_query); ?>"</h3>
            <p style="color: #888; margin-bottom: 20px;">Try different keywords or check out our categories.</p>
            <a href="index.php" class="btn">Back to Home</a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px; margin-bottom: 40px;">
            <?php foreach($posts as $post): ?>
                <article style="background: white; border-radius: 5px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                    <?php if($post['featured_image']): ?>
                        <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" 
                             alt="<?php echo htmlspecialchars($post['title']); ?>"
                             style="width: 100%; height: 200px; object-fit: cover;">
                    <?php endif; ?>
                    <div style="padding: 20px;">
                        <span style="background: #f0f0f0; color: #666; padding: 2px 6px; border-radius: 3px; font-size: 11px; display: inline-block; margin-bottom: 8px;">
                            <?php echo htmlspecialchars($categories[$post['category']] ?? $post['category']); ?>
                        </span>
                        <h3 style="margin-bottom: 10px; font-size: 18px;">
                            <a href="view_post.php?id=<?php echo $post['id']; ?>" 
                               style="color: #0c0c0c; text-decoration: none; line-height: 1.4;">
                                <?php echo highlight_text($post['title'], $search_query); ?>
                            </a>
                        </h3>
                        <p style="color: #666; margin-bottom: 15px; font-size: 14px;">
                            <?php echo highlight_text(substr(strip_tags($post['content']), 0, 150), $search_query); ?>...
                        </p>
                        <div style="font-size: 12px; color: #888;">
                            <span>By <?php echo htmlspecialchars($post['username']); ?></span>
                            <span style="float: right;"><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if($total_pages > 1): ?>
            <div style="display: flex; justify-content: center; gap: 10px; margin-top: 40px;">
                <?php if($page > 1): ?>
                    <a href="search.php?q=<?php echo urlencode($search_query); ?>&page=<?php echo $page - 1; ?>"
                       style="padding: 8px 15px; background: white; color: #0c0c0c; text-decoration: none; border: 1px solid #ddd; border-radius: 3px;">
                        Previous
                    </a>
                <?php endif; ?>
                
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="search.php?q=<?php echo urlencode($search_query); ?>&page=<?php echo $i; ?>"
                       style="padding: 8px 12px; background: <?php echo $i == $page ? '#cc0000' : 'white'; ?>; 
                              color: <?php echo $i == $page ? 'white' : '#0c0c0c'; ?>; 
                              text-decoration: none; border: 1px solid #ddd; border-radius: 3px;">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if($page < $total_pages): ?>
                    <a href="search.php?q=<?php echo urlencode($search_query); ?>&page=<?php echo $page + 1; ?>"
                       style="padding: 8px 15px; background: white; color: #0c0c0c; text-decoration: none; border: 1px solid #ddd; border-radius: 3px;">
                        Next
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php
// Helper function to highlight search terms
function highlight_text($text, $search_query) {
    $words = explode(' ', $search_query);
    foreach($words as $word) {
        if(strlen(trim($word)) > 2) {
            $text = preg_replace("/($word)/i", '<mark style="background: yellow; padding: 2px;">$1</mark>', $text);
        }
    }
    return $text;
}
?>

<?php include 'footer.php'; ?>
