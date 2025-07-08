<?php
require_once 'config/config.php';
require_once 'classes/Post.php';

$database = new Database();
$db = $database->getConnection();

$post = new Post($db);

if (!isset($_GET['slug']) || empty($_GET['slug'])) {
    header('Location: /blog');
    exit();
}

// Get post by slug
$stmt = $post->getBySlug($_GET['slug']);
$post_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post_data) {
    header('HTTP/1.0 404 Not Found');
    include '404.php';
    exit();
}

// Increment view count
$post->id = $post_data['id'];
$post->incrementViews();

// Get recent posts for sidebar
$recent_posts = $post->getRecentPosts(3);

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8">
            <article class="blog-post">
                <h1 class="display-5 fw-bold mb-4"><?= htmlspecialchars($post_data['title']) ?></h1>
                
                <div class="mb-4 text-muted">
                    <span class="me-3">
                        <i class="far fa-calendar-alt me-1"></i>
                        <?= formatDate($post_data['created_at'], 'F j, Y') ?>
                    </span>
                    <span>
                        <i class="far fa-eye me-1"></i>
                        <?= number_format($post_data['views'] + 1) ?> views
                    </span>
                </div>
                
                <?php if($post_data['featured_image']): ?>
                    <img src="<?= $post_data['featured_image'] ?>" 
                         alt="<?= htmlspecialchars($post_data['title']) ?>" 
                         class="img-fluid rounded mb-4">
                <?php endif; ?>
                
                <div class="blog-content">
                    <?= $post_data['content'] ?>
                </div>
            </article>
        </div>
        
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    Recent Posts
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach($recent_posts as $recent): ?>
                        <a href="/post.php?slug=<?= $recent['slug'] ?>" 
                           class="list-group-item list-group-item-action">
                            <?= htmlspecialchars($recent['title']) ?>
                            <small class="d-block text-muted">
                                <?= formatDate($recent['created_at'], 'M j, Y') ?>
                            </small>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>