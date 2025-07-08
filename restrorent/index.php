<?php
require_once 'config/config.php';
require_once 'classes/Post.php';
require_once 'classes/Category.php';
require_once 'classes/MenuItem.php';
require_once 'classes/MenuCategory.php';

$database = new Database();
$db = $database->getConnection();

$post = new Post($db);
$category = new Category($db);
$menuItem = new MenuItem($db);
$menuCategory = new MenuCategory($db);

// Get current page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Get posts
$stmt = $post->getPublishedPosts($page);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for navigation
$categories_stmt = $menuCategory->getAll();
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total posts for pagination
$total_posts = $post->getTotalCount("WHERE status = 'published'");
$total_pages = ceil($total_posts / POSTS_PER_PAGE);

// Get featured items
$featured_stmt = $menuItem->getFeaturedItems(6);
$featured_items = $featured_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Fine Dining Experience</title>
    <meta name="description" content="Experience the finest dining at <?php echo SITE_NAME; ?>. Fresh ingredients, exceptional service, and unforgettable flavors.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#d97706',
                        secondary: '#92400e',
                        accent: '#fbbf24',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg fixed w-full top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="text-2xl font-bold text-primary"><?php echo SITE_NAME; ?></a>
                </div>
                
                <div class="hidden md:flex items-center space-x-8">
                    <a href="/" class="text-gray-700 hover:text-primary transition-colors">Home</a>
                    <a href="/menu.php" class="text-gray-700 hover:text-primary transition-colors">Menu</a>
                    <a href="/order.php" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary transition-colors">Order Online</a>
                    <a href="/contact.php" class="text-gray-700 hover:text-primary transition-colors">Contact</a>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-btn" class="text-gray-700 hover:text-primary">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="md:hidden hidden bg-white border-t">
            <div class="px-4 py-2 space-y-2">
                <a href="/" class="block py-2 text-gray-700 hover:text-primary">Home</a>
                <a href="/menu.php" class="block py-2 text-gray-700 hover:text-primary">Menu</a>
                <a href="/order.php" class="block py-2 text-primary font-medium">Order Online</a>
                <a href="/contact.php" class="block py-2 text-gray-700 hover:text-primary">Contact</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative h-screen flex items-center justify-center bg-gradient-to-r from-black/70 to-black/50" style="background-image: url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80'); background-size: cover; background-position: center; background-blend-mode: overlay;">
        <div class="text-center text-white px-4 sm:px-6 lg:px-8">
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold mb-6">Welcome to<br><span class="text-accent"><?php echo SITE_NAME; ?></span></h1>
            <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto opacity-90">Experience culinary excellence with fresh ingredients, exceptional service, and unforgettable flavors in an elegant atmosphere.</p>
            <div class="space-x-4">
                <a href="/menu.php" class="bg-primary text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-secondary transition-colors inline-flex items-center">
                    <i class="fas fa-utensils mr-2"></i>
                    View Menu
                </a>
                <a href="/order.php" class="bg-transparent border-2 border-white text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-white hover:text-primary transition-colors inline-flex items-center">
                    <i class="fas fa-shopping-cart mr-2"></i>
                    Order Now
                </a>
            </div>
        </div>
        
        <!-- Scroll indicator -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
            <i class="fas fa-chevron-down text-white text-2xl"></i>
        </div>
    </section>

    <!-- About Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-4xl font-bold text-gray-800 mb-6">Our Story</h2>
                    <p class="text-lg text-gray-600 mb-6 leading-relaxed">
                        At <?php echo SITE_NAME; ?>, we believe that great food brings people together. Since our founding, we've been committed to serving exceptional dishes made with the finest ingredients, prepared by our talented chefs who are passionate about culinary excellence.
                    </p>
                    <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                        Our menu features a perfect blend of classic favorites and innovative creations, all designed to provide you with an unforgettable dining experience. Whether you're joining us for a casual lunch, romantic dinner, or special celebration, we're here to make every moment memorable.
                    </p>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="text-center">
                            <div class="bg-primary text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-award text-2xl"></i>
                            </div>
                            <h3 class="font-semibold text-gray-800 mb-1">Award Winning</h3>
                            <p class="text-gray-600 text-sm">Recognized for excellence</p>
                        </div>
                        <div class="text-center">
                            <div class="bg-primary text-white w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-leaf text-2xl"></i>
                            </div>
                            <h3 class="font-semibold text-gray-800 mb-1">Fresh Ingredients</h3>
                            <p class="text-gray-600 text-sm">Locally sourced daily</p>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1414235077428-338989a2e8c0?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Restaurant Interior" class="rounded-lg shadow-xl">
                    <div class="absolute -bottom-6 -left-6 bg-primary text-white p-6 rounded-lg shadow-lg">
                        <div class="text-center">
                            <div class="text-3xl font-bold">15+</div>
                            <div class="text-sm opacity-90">Years of Excellence</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Menu Items -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Featured Dishes</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Discover our chef's special selections, crafted with passion and the finest ingredients</p>
            </div>

            <?php if(!empty($featured_items)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach($featured_items as $item): ?>
                        <div class="bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                            <div class="relative">
                                <?php if($item['image_url']): ?>
                                    <img src="<?php echo $item['image_url']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-full h-48 object-cover">
                                <?php else: ?>
                                    <div class="w-full h-48 bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                                        <i class="fas fa-utensils text-white text-4xl opacity-50"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="absolute top-4 right-4 bg-primary text-white px-3 py-1 rounded-full text-sm font-semibold">
                                    <?php echo formatPrice($item['price']); ?>
                                </div>
                            </div>
                            
                            <div class="p-6">
                                <div class="flex items-center mb-2">
                                    <span class="bg-accent text-gray-800 px-2 py-1 rounded-full text-xs font-medium">
                                        <?php echo $item['category_name']; ?>
                                    </span>
                                </div>
                                
                                <h3 class="text-xl font-bold text-gray-800 mb-2">
                                    <?php echo htmlspecialchars($item['name']); ?>
                                </h3>
                                
                                <p class="text-gray-600 mb-4 leading-relaxed">
                                    <?php echo htmlspecialchars($item['description']); ?>
                                </p>
                                
                                <?php if($item['allergens']): ?>
                                    <div class="mb-4">
                                        <span class="text-xs text-gray-500">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Contains: <?php echo htmlspecialchars($item['allergens']); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <button onclick="addToOrder(<?php echo $item['id']; ?>, '<?php echo htmlspecialchars($item['name']); ?>', <?php echo $item['price']; ?>)" 
                                        class="w-full bg-primary text-white py-3 rounded-lg font-semibold hover:bg-secondary transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Add to Order
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-12">
                    <i class="fas fa-utensils text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No featured items available</h3>
                    <p class="text-gray-500">Check back soon for our chef's special selections!</p>
                </div>
            <?php endif; ?>

            <div class="text-center mt-12">
                <a href="/menu.php" class="bg-primary text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-secondary transition-colors inline-flex items-center">
                    <i class="fas fa-eye mr-2"></i>
                    View Full Menu
                </a>
            </div>
        </div>
    </section>

    <!-- Categories Preview -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Menu Categories</h2>
                <p class="text-xl text-gray-600">Explore our diverse selection of culinary delights</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach($categories as $category): ?>
                    <a href="/menu.php?category=<?php echo $category['slug']; ?>" class="group bg-gray-50 hover:bg-primary transition-all duration-300 rounded-lg p-8 text-center">
                        <div class="mb-4">
                            <?php
                            $icons = [
                                'starters' => 'fas fa-seedling',
                                'main-course' => 'fas fa-drumstick-bite',
                                'desserts' => 'fas fa-ice-cream',
                                'drinks' => 'fas fa-cocktail'
                            ];
                            $icon = $icons[$category['slug']] ?? 'fas fa-utensils';
                            ?>
                            <i class="<?php echo $icon; ?> text-4xl text-primary group-hover:text-white transition-colors"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800 group-hover:text-white transition-colors mb-2">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </h3>
                        <p class="text-gray-600 group-hover:text-white/90 transition-colors">
                            <?php echo htmlspecialchars($category['description']); ?>
                        </p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Blog Posts -->
    <section id="posts" class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center mb-12 text-gray-800">Latest Posts</h2>
            
            <?php if(empty($posts)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-file-alt text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No posts found</h3>
                    <p class="text-gray-500">Check back later for new content!</p>
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
                                    <span class="bg-primary text-white px-3 py-1 rounded-full text-sm font-medium">
                                        <?php echo $post_item['category_name']; ?>
                                    </span>
                                    <span class="text-gray-500 text-sm ml-auto">
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
                                <a href="?page=<?php echo $page - 1; ?>" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>

                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=<?php echo $i; ?>" class="px-4 py-2 <?php echo $i == $page ? 'bg-primary text-white' : 'bg-white border border-gray-300 hover:bg-gray-50'; ?> rounded-lg transition-colors">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?>" class="px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-20 bg-gradient-to-r from-primary to-secondary text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl font-bold mb-6">Ready to Experience Excellence?</h2>
            <p class="text-xl mb-8 opacity-90 max-w-3xl mx-auto">
                Join us for an unforgettable dining experience. Order online for delivery or pickup, or visit us in person.
            </p>
            <div class="space-x-4">
                <a href="/order.php" class="bg-white text-primary px-8 py-4 rounded-lg text-lg font-semibold hover:bg-gray-100 transition-colors inline-flex items-center">
                    <i class="fas fa-shopping-cart mr-2"></i>
                    Order Online
                </a>
                <a href="/contact.php" class="bg-transparent border-2 border-white text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-white hover:text-primary transition-colors inline-flex items-center">
                    <i class="fas fa-map-marker-alt mr-2"></i>
                    Visit Us
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="col-span-1 md:col-span-2">
                    <h3 class="text-2xl font-bold mb-4"><?php echo SITE_NAME; ?></h3>
                    <p class="text-gray-300 mb-4">Experience culinary excellence with fresh ingredients, exceptional service, and unforgettable flavors in an elegant atmosphere.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-300 hover:text-white transition-colors">
                            <i class="fab fa-facebook text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-white transition-colors">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-white transition-colors">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-white transition-colors">
                            <i class="fab fa-yelp text-xl"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="/" class="text-gray-300 hover:text-white transition-colors">Home</a></li>
                        <li><a href="/menu.php" class="text-gray-300 hover:text-white transition-colors">Menu</a></li>
                        <li><a href="/order.php" class="text-gray-300 hover:text-white transition-colors">Order Online</a></li>
                        <li><a href="/contact.php" class="text-gray-300 hover:text-white transition-colors">Contact</a></li>
                        <li><a href="/admin" class="text-gray-300 hover:text-white transition-colors">Admin</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Contact Info</h4>
                    <ul class="space-y-2 text-gray-300">
                        <li><i class="fas fa-phone mr-2"></i>+1 (555) 123-4567</li>
                        <li><i class="fas fa-envelope mr-2"></i>info@deliciousbites.com</li>
                        <li><i class="fas fa-map-marker-alt mr-2"></i>123 Food Street<br>Culinary City, CC 12345</li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center">
                <p class="text-gray-300">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });

        // Add to order function
        function addToOrder(itemId, itemName, itemPrice) {
            // Store in localStorage for order page
            let cart = JSON.parse(localStorage.getItem('cart') || '[]');
            
            // Check if item already exists
            const existingItem = cart.find(item => item.id === itemId);
            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    id: itemId,
                    name: itemName,
                    price: itemPrice,
                    quantity: 1
                });
            }
            
            localStorage.setItem('cart', JSON.stringify(cart));
            
            // Show success message
            alert(`${itemName} added to your order!`);
        }

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Navbar background on scroll
        window.addEventListener('scroll', function() {
            const nav = document.querySelector('nav');
            if (window.scrollY > 100) {
                nav.classList.add('bg-white/95', 'backdrop-blur-sm');
            } else {
                nav.classList.remove('bg-white/95', 'backdrop-blur-sm');
            }
        });
    </script>
</body>
</html>
