<?php
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Handle new post
if (isset($_POST['create_post'])) {
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    
    if (!empty($content)) {
        $query = "INSERT INTO posts (user_id, content) VALUES ($user_id, '$content')";
        mysqli_query($conn, $query);
    }
}

// Handle like
if (isset($_POST['like_post'])) {
    $post_id = mysqli_real_escape_string($conn, $_POST['post_id']);
    
    // Check if already liked
    $check_query = "SELECT id FROM likes WHERE user_id = $user_id AND post_id = $post_id";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) == 0) {
        $like_query = "INSERT INTO likes (user_id, post_id) VALUES ($user_id, $post_id)";
        mysqli_query($conn, $like_query);
    } else {
        // Unlike if already liked
        $unlike_query = "DELETE FROM likes WHERE user_id = $user_id AND post_id = $post_id";
        mysqli_query($conn, $unlike_query);
    }
}

// Get all posts with like counts
$posts_query = "
    SELECT 
        p.*,
        u.username,
        (SELECT COUNT(*) FROM likes WHERE post_id = p.id) as like_count,
        (SELECT COUNT(*) FROM likes WHERE post_id = p.id AND user_id = $user_id) as user_liked
    FROM posts p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC
";
$posts_result = mysqli_query($conn, $posts_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard </title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <h1 class="logo">Tee's Social</h1>
            <div class="nav-links">
                <span class="welcome-user">Welcome, <?php echo htmlspecialchars($username); ?>!</span>
                <a href="dashboard.php" class="active">Home</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Create Post Section -->
        <div class="create-post-card">
            <h2>Create Post</h2>
            <form method="POST" class="post-form">
                <textarea name="content" placeholder="What's on your mind?" rows="3" required></textarea>
                <button type="submit" name="create_post" class="btn-post">Share Post</button>
            </form>
        </div>

        <!-- Posts Feed -->
        <div class="posts-feed">
            <h2>Recent Posts</h2>
            
            <?php if (mysqli_num_rows($posts_result) > 0): ?>
                <?php while ($post = mysqli_fetch_assoc($posts_result)): ?>
                    <div class="post-card">
                        <div class="post-header">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($post['username'], 0, 1)); ?>
                            </div>
                            <div class="post-info">
                                <h3><?php echo htmlspecialchars($post['username']); ?></h3>
                                <span class="post-time">
                                    <?php echo date('M j, Y g:i A', strtotime($post['created_at'])); ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="post-content">
                            <p><?php echo htmlspecialchars($post['content']); ?></p>
                        </div>
                        
                        <div class="post-actions">
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <button type="submit" name="like_post" class="btn-like <?php echo $post['user_liked'] ? 'liked' : ''; ?>">
                                    <?php echo $post['user_liked'] ? '❤️' : '🤍'; ?> 
                                    Like (<?php echo $post['like_count']; ?>)
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-posts">No posts yet. Be the first to post!</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>