<?php
require_once 'config/config.php';
require_once 'classes/Post.php';
require_once 'classes/Category.php';

if (!isset($_GET['slug']) || empty($_GET['slug'])) {
    header('Location: /');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$post = new Post($db);
$category = new Category($db);

// Get category by slug
$category_stmt = $category->getBySlug($_GET['slug']);
$category_data = $category_stmt->fetch(PDO::FETCH_ASSOC);

if (!$category_data) {
    header('HTTP/1.0 404 Not Found');
    include '404.php';
    exit();
}

// Get current page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Get posts by category
$stmt = $post->getByCategory($_GET['slug'], $page);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all categories for navigation
$categories_stmt = $category->getAll();
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total posts for pagination
$total_posts = $post->getTotalCount("LEFT JOIN categories c ON posts.category_id = c.id WHERE posts.status = 'published' AND c.slug = '{$_GET['slug']}'");
$total_pages = ceil($total_posts / POSTS_PER_PAGE);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($category_data['name']); ?> - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($category_data['description']); ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                        secondary: '#1e40af',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <!-- Navigation (same as other pages) -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="text-2xl font-bold text-primary"><?php echo SITE_NAME; ?></a>
                </div>
                
                <div class="hidden md:flex items-center space-x-8">
                    <a href="/" class="text-gray-700 hover:text-primary transition-colors">Home</a>
                    <div class="relative group">
                        <button class="text-gray-700 hover:text-primary transition-colors flex items-center">
                            Categories <i class="fas fa-chevron-down ml-1 text-xs"></i>
                        </button>
                        <div class="absolute top-full left-0 bg-white shadow-lg rounded-lg py-2 w-48 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                            <?php foreach($categories as $cat): ?>
                                <a href="/category.php?slug=<?php echo $cat['slug']; ?>" class="block px-4 py-2 text-gray-700 hover:bg-gray-100 <?php echo $cat['slug'] === $_GET['slug'] ? 'bg-gray-100' : ''; ?>">
                                    <?php echo $cat['name']; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <a href="/contact.php" class="text-gray-700 hover:text-primary transition-colors">Contact</a>
                </div>

                <div class="flex items-center">
                    <form action="/search.php" method="GET" class="relative">
                        <input type="text" name="q" placeholder="Search..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Category Header -->
    <section class="bg-gradient-to-r from-primary to-secondary text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4"><?php echo htmlspecialchars($category_data['name']); ?></h1>
            <p class="text-xl opacity-90 mb-6"><?php echo htmlspecialchars($category_data['description']); ?></p>
            <div class="text-lg opacity-75">
                <i class="fas fa-file-alt mr-2"></i>
                <?php echo $total_posts; ?> <?php echo $total_posts === 1 ? 'post' : 'posts'; ?>
            </div>
        </div>
    </section>

    <!-- Posts -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <?php if(empty($posts)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-file-alt text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No posts found in this category</h3>
                    <p class="text-gray-500 mb-6">Check back later for new content!</p>
                    <a href="/" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-secondary transition-colors">
                        Browse All Posts
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach($posts as $post_item): ?>
                        <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300">
                            <?php if($post_item['featured_image']): ?>
                                <img src="<?php echo $post_item['featured_image']; ?>" alt="<?php echo htmlspecialchars($post_item['title']); ?>" class="w-full h-48 object-cover">
                            <?php else: ?>
                                <div class="w-full h-48 bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                                    <i class="fas fa-image text-white text-4xl opacity-50"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="p-6">
                                <div class="flex items-center mb-3">
                                    <span class="text-gray-500 text-sm">
                                        <?php echo formatDate($post_item['created_at']); ?>
                                    </span>
                                </div>
                                
                                <h3 class="text-xl font-bold mb-3 text-gray-800 hover:text-primary transition-colors">
                                    <a href="/post.php?slug=<?php echo $post_item['slug']; ?>">
                                        <?php echo htmlspecialchars($post_item['title']); ?>
                                    </a>
                                </h3>
                                
                                <p class="text-gray-600 mb-4 leading-relaxed">
                                    <?php echo truncateText($post_item['excerpt'] ?: strip_tags($post_item['content'])); ?>
                                </p>
                                
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center text-sm text-gray-500">
                                        <i class="fas fa-user mr-1"></i>
                                        <?php echo $post_item['author_name']; ?>
                                    </div>
                                    <a href="/post.php?slug=<?php echo $post_item['slug']; ?>" class="text-primary hover:text-secondary font-medium inline-flex items-center">
                                        Read More <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if($total_pages > 1): ?>
                    <div class="flex justify-center mt-12">
                        <nav class="flex space-x-2">
                            <?php if($page > 1): ?>
                                <a href="?slug=<?php echo $_GET['slug']; ?>&page=<?php echo $page - 1; ?>" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>

                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?slug=<?php echo $_GET['slug']; ?>&page=<?php echo $i; ?>" class="px-4 py-2 <?php echo $i == $page ? 'bg-primary text-white' : 'bg-white border border-gray-300 hover:bg-gray-50'; ?> rounded-lg transition-colors">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if($page < $total_pages): ?>
                                <a href="?slug=<?php echo $_GET['slug']; ?>&page=<?php echo $page + 1; ?>" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="col-span-1 md:col-span-2">
                    <h3 class="text-2xl font-bold mb-4"><?php echo SITE_NAME; ?></h3>
                    <p class="text-gray-300 mb-4">A modern blog platform sharing insights, stories, and ideas across various topics.</p>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Categories</h4>
                    <ul class="space-y-2">
                        <?php foreach(array_slice($categories, 0, 5) as $cat): ?>
                            <li>
                                <a href="/category.php?slug=<?php echo $cat['slug']; ?>" class="text-gray-300 hover:text-white transition-colors">
                                    <?php echo $cat['name']; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="/" class="text-gray-300 hover:text-white transition-colors">Home</a></li>
                        <li><a href="/contact.php" class="text-gray-300 hover:text-white transition-colors">Contact</a></li>
                        <li><a href="/admin" class="text-gray-300 hover:text-white transition-colors">Admin</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center">
                <p class="text-gray-300">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
