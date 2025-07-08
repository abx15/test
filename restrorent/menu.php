<?php
require_once 'config/config.php';
require_once 'classes/MenuItem.php';
require_once 'classes/MenuCategory.php';

$database = new Database();
$db = $database->getConnection();

$menuItem = new MenuItem($db);
$menuCategory = new MenuCategory($db);

// Get selected category
$selected_category = isset($_GET['category']) ? $_GET['category'] : null;
$category_data = null;

if ($selected_category) {
    $category_stmt = $menuCategory->getBySlug($selected_category);
    $category_data = $category_stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all categories
$categories_stmt = $menuCategory->getAll();
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get menu items
if ($selected_category && $category_data) {
    $items_stmt = $menuItem->getByCategory($category_data['id']);
} else {
    $items_stmt = $menuItem->getByCategory();
}
$menu_items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

// Group items by category
$grouped_items = [];
foreach ($menu_items as $item) {
    $cat_name = $item['category_name'] ?: 'Other';
    if (!isset($grouped_items[$cat_name])) {
        $grouped_items[$cat_name] = [];
    }
    $grouped_items[$cat_name][] = $item;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Explore our delicious menu featuring fresh ingredients and exceptional flavors.">
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
                    <a href="/menu.php" class="text-primary font-medium transition-colors">Menu</a>
                    <a href="/order.php" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-secondary transition-colors">Order Online</a>
                    <a href="/contact.php" class="text-gray-700 hover:text-primary transition-colors">Contact</a>
                </div>

                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-btn" class="text-gray-700 hover:text-primary">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <div id="mobile-menu" class="md:hidden hidden bg-white border-t">
            <div class="px-4 py-2 space-y-2">
                <a href="/" class="block py-2 text-gray-700 hover:text-primary">Home</a>
                <a href="/menu.php" class="block py-2 text-primary font-medium">Menu</a>
                <a href="/order.php" class="block py-2 text-gray-700 hover:text-primary">Order Online</a>
                <a href="/contact.php" class="block py-2 text-gray-700 hover:text-primary">Contact</a>
            </div>
        </div>
    </nav>

    <!-- Menu Header -->
    <section class="pt-20 pb-12 bg-gradient-to-r from-primary to-secondary text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                <?php echo $category_data ? htmlspecialchars($category_data['name']) : 'Our Menu'; ?>
            </h1>
            <p class="text-xl opacity-90">
                <?php echo $category_data ? htmlspecialchars($category_data['description']) : 'Discover our delicious selection of carefully crafted dishes'; ?>
            </p>
        </div>
    </section>

    <!-- Category Filter -->
    <section class="py-8 bg-white border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap justify-center gap-4">
                <a href="/menu.php" class="px-6 py-3 rounded-lg font-medium transition-colors <?php echo !$selected_category ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                    All Items
                </a>
                <?php foreach($categories as $category): ?>
                    <a href="/menu.php?category=<?php echo $category['slug']; ?>" 
                       class="px-6 py-3 rounded-lg font-medium transition-colors <?php echo $selected_category === $category['slug'] ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'; ?>">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Menu Items -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <?php if(empty($grouped_items)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-utensils text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-600 mb-2">No menu items found</h3>
                    <p class="text-gray-500 mb-6">Check back soon for delicious new additions!</p>
                    <a href="/menu.php" class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-secondary transition-colors">
                        View All Items
                    </a>
                </div>
            <?php else: ?>
                <?php foreach($grouped_items as $category_name => $items): ?>
                    <div class="mb-16">
                        <h2 class="text-3xl font-bold text-gray-800 mb-8 text-center"><?php echo htmlspecialchars($category_name); ?></h2>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <?php foreach($items as $item): ?>
                                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 overflow-hidden">
                                    <div class="md:flex">
                                        <div class="md:w-1/3">
                                            <?php if($item['image_url']): ?>
                                                <img src="<?php echo $item['image_url']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="w-full h-48 md:h-full object-cover">
                                            <?php else: ?>
                                                <div class="w-full h-48 md:h-full bg-gradient-to-r from-primary to-secondary flex items-center justify-center">
                                                    <i class="fas fa-utensils text-white text-4xl opacity-50"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="md:w-2/3 p-6">
                                            <div class="flex justify-between items-start mb-3">
                                                <h3 class="text-xl font-bold text-gray-800">
                                                    <?php echo htmlspecialchars($item['name']); ?>
                                                </h3>
                                                <span class="text-2xl font-bold text-primary">
                                                    <?php echo formatPrice($item['price']); ?>
                                                </span>
                                            </div>
                                            
                                            <p class="text-gray-600 mb-4 leading-relaxed">
                                                <?php echo htmlspecialchars($item['description']); ?>
                                            </p>
                                            
                                            <?php if($item['ingredients']): ?>
                                                <div class="mb-3">
                                                    <span class="text-sm text-gray-500">
                                                        <i class="fas fa-list mr-1"></i>
                                                        <strong>Ingredients:</strong> <?php echo htmlspecialchars($item['ingredients']); ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if($item['allergens']): ?>
                                                <div class="mb-4">
                                                    <span class="text-xs text-red-600 bg-red-50 px-2 py-1 rounded">
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
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-16 bg-primary text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold mb-4">Ready to Order?</h2>
            <p class="text-xl mb-8 opacity-90">Experience these amazing flavors delivered to your door or ready for pickup</p>
            <a href="/order.php" class="bg-white text-primary px-8 py-4 rounded-lg text-lg font-semibold hover:bg-gray-100 transition-colors inline-flex items-center">
                <i class="fas fa-shopping-cart mr-2"></i>
                Start Your Order
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="col-span-1 md:col-span-2">
                    <h3 class="text-2xl font-bold mb-4"><?php echo SITE_NAME; ?></h3>
                    <p class="text-gray-300 mb-4">Experience culinary excellence with fresh ingredients, exceptional service, and unforgettable flavors.</p>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="/" class="text-gray-300 hover:text-white transition-colors">Home</a></li>
                        <li><a href="/menu.php" class="text-gray-300 hover:text-white transition-colors">Menu</a></li>
                        <li><a href="/order.php" class="text-gray-300 hover:text-white transition-colors">Order Online</a></li>
                        <li><a href="/contact.php" class="text-gray-300 hover:text-white transition-colors">Contact</a></li>
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
            let cart = JSON.parse(localStorage.getItem('cart') || '[]');
            
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
            alert(`${itemName} added to your order!`);
        }
    </script>
</body>
</html>
