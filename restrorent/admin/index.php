<?php
require_once '../config/config.php';
require_once '../classes/MenuItem.php';
require_once '../classes/MenuCategory.php';
require_once '../classes/Order.php';

requireLogin();

$database = new Database();
$db = $database->getConnection();

$menuItem = new MenuItem($db);
$menuCategory = new MenuCategory($db);
$order = new Order($db);

// Get statistics
$order_stats_stmt = $order->getStats();
$order_stats = $order_stats_stmt->fetch(PDO::FETCH_ASSOC);

$menu_stats_query = "SELECT 
    (SELECT COUNT(*) FROM menu_items WHERE is_available = 1) as available_items,
    (SELECT COUNT(*) FROM menu_items WHERE is_available = 0) as unavailable_items,
    (SELECT COUNT(*) FROM menu_categories WHERE is_active = 1) as active_categories,
    (SELECT COUNT(*) FROM contact_messages WHERE status = 'unread') as unread_messages";
$menu_stats_stmt = $db->prepare($menu_stats_query);
$menu_stats_stmt->execute();
$menu_stats = $menu_stats_stmt->fetch(PDO::FETCH_ASSOC);

// Get recent orders
$recent_orders_stmt = $order->getAll(null, 5);
$recent_orders = $recent_orders_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent messages
$recent_messages_query = "SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5";
$recent_messages_stmt = $db->prepare($recent_messages_query);
$recent_messages_stmt->execute();
$recent_messages = $recent_messages_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
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
<body class="bg-gray-100">
    <!-- Admin Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/admin/" class="text-2xl font-bold text-primary"><?php echo SITE_NAME; ?> Admin</a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="/" target="_blank" class="text-gray-700 hover:text-primary transition-colors">
                        <i class="fas fa-external-link-alt mr-1"></i>
                        View Site
                    </a>
                    <div class="relative group">
                        <button class="flex items-center text-gray-700 hover:text-primary transition-colors">
                            <i class="fas fa-user-circle text-xl mr-2"></i>
                            <?php echo $_SESSION['admin_username']; ?>
                            <i class="fas fa-chevron-down ml-1 text-xs"></i>
                        </button>
                        <div class="absolute right-0 top-full bg-white shadow-lg rounded-lg py-2 w-48 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                            <a href="/admin/logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-lg min-h-screen">
            <nav class="mt-8">
                <div class="px-4 space-y-2">
                    <a href="/admin/" class="bg-primary text-white flex items-center px-4 py-3 rounded-lg">
                        <i class="fas fa-tachometer-alt mr-3"></i>
                        Dashboard
                    </a>
                    <a href="/admin/menu-items.php" class="text-gray-700 hover:bg-gray-100 flex items-center px-4 py-3 rounded-lg transition-colors">
                        <i class="fas fa-utensils mr-3"></i>
                        Menu Items
                    </a>
                    <a href="/admin/categories.php" class="text-gray-700 hover:bg-gray-100 flex items-center px-4 py-3 rounded-lg transition-colors">
                        <i class="fas fa-folder mr-3"></i>
                        Categories
                    </a>
                    <a href="/admin/orders.php" class="text-gray-700 hover:bg-gray-100 flex items-center px-4 py-3 rounded-lg transition-colors">
                        <i class="fas fa-shopping-cart mr-3"></i>
                        Orders
                        <?php if($order_stats['pending_orders'] > 0): ?>
                            <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full ml-auto">
                                <?php echo $order_stats['pending_orders']; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <a href="/admin/messages.php" class="text-gray-700 hover:bg-gray-100 flex items-center px-4 py-3 rounded-lg transition-colors">
                        <i class="fas fa-envelope mr-3"></i>
                        Messages
                        <?php if($menu_stats['unread_messages'] > 0): ?>
                            <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full ml-auto">
                                <?php echo $menu_stats['unread_messages']; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
                <p class="text-gray-600 mt-2">Welcome back, <?php echo $_SESSION['admin_username']; ?>!</p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="bg-green-100 p-3 rounded-lg">
                            <i class="fas fa-shopping-cart text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo $order_stats['total_orders']; ?></h3>
                            <p class="text-gray-600">Total Orders</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="bg-yellow-100 p-3 rounded-lg">
                            <i class="fas fa-clock text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo $order_stats['pending_orders']; ?></h3>
                            <p class="text-gray-600">Pending Orders</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="bg-blue-100 p-3 rounded-lg">
                            <i class="fas fa-utensils text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo $menu_stats['available_items']; ?></h3>
                            <p class="text-gray-600">Menu Items</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center">
                        <div class="bg-purple-100 p-3 rounded-lg">
                            <i class="fas fa-dollar-sign text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold text-gray-800"><?php echo formatPrice($order_stats['total_revenue'] ?? 0); ?></h3>
                            <p class="text-gray-600">Total Revenue</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Orders -->
                <div class="bg-white rounded-lg shadow-md">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-bold text-gray-800">Recent Orders</h2>
                            <a href="/admin/orders.php" class="text-primary hover:text-secondary transition-colors">
                                View All
                            </a>
                        </div>
                    </div>
                    <div class="p-6">
                        <?php if(empty($recent_orders)): ?>
                            <p class="text-gray-500 text-center py-4">No orders yet</p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach($recent_orders as $order_item): ?>
                                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                        <div>
                                            <h3 class="font-semibold text-gray-800 mb-1">
                                                Order #<?php echo htmlspecialchars($order_item['order_number']); ?>
                                            </h3>
                                            <div class="flex items-center text-sm text-gray-500 space-x-4">
                                                <span><?php echo htmlspecialchars($order_item['customer_name']); ?></span>
                                                <span><?php echo formatPrice($order_item['total_amount']); ?></span>
                                                <span class="px-2 py-1 rounded-full text-xs <?php 
                                                    echo match($order_item['status']) {
                                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                                        'confirmed' => 'bg-blue-100 text-blue-800',
                                                        'preparing' => 'bg-orange-100 text-orange-800',
                                                        'ready' => 'bg-purple-100 text-purple-800',
                                                        'delivered' => 'bg-green-100 text-green-800',
                                                        'cancelled' => 'bg-red-100 text-red-800',
                                                        default => 'bg-gray-100 text-gray-800'
                                                    };
                                                ?>">
                                                    <?php echo ucfirst($order_item['status']); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <a href="/admin/orders.php?view=<?php echo $order_item['id']; ?>" class="text-primary hover:text-secondary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Messages -->
                <div class="bg-white rounded-lg shadow-md">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h2 class="text-xl font-bold text-gray-800">Recent Messages</h2>
                            <a href="/admin/messages.php" class="text-primary hover:text-secondary transition-colors">
                                View All
                            </a>
                        </div>
                    </div>
                    <div class="p-6">
                        <?php if(empty($recent_messages)): ?>
                            <p class="text-gray-500 text-center py-4">No messages yet</p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach($recent_messages as $message): ?>
                                    <div class="p-4 bg-gray-50 rounded-lg">
                                        <div class="flex items-center justify-between mb-2">
                                            <h3 class="font-semibold text-gray-800">
                                                <?php echo htmlspecialchars($message['name']); ?>
                                            </h3>
                                            <span class="text-xs text-gray-500">
                                                <?php echo formatDate($message['created_at']); ?>
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 mb-2">
                                            <?php echo htmlspecialchars($message['subject']); ?>
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars(substr($message['message'], 0, 100)) . (strlen($message['message']) > 100 ? '...' : ''); ?>
                                        </p>
                                        <?php if($message['status'] === 'unread'): ?>
                                            <span class="inline-block mt-2 px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full">
                                                Unread
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="mt-8">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Actions</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <a href="/admin/menu-items.php?action=create" class="bg-primary text-white p-6 rounded-lg hover:bg-secondary transition-colors text-center">
                        <i class="fas fa-plus text-2xl mb-2"></i>
                        <h3 class="font-semibold">Add Menu Item</h3>
                    </a>
                    <a href="/admin/categories.php?action=create" class="bg-green-600 text-white p-6 rounded-lg hover:bg-green-700 transition-colors text-center">
                        <i class="fas fa-folder-plus text-2xl mb-2"></i>
                        <h3 class="font-semibold">Add Category</h3>
                    </a>
                    <a href="/admin/orders.php" class="bg-blue-600 text-white p-6 rounded-lg hover:bg-blue-700 transition-colors text-center">
                        <i class="fas fa-shopping-cart text-2xl mb-2"></i>
                        <h3 class="font-semibold">View Orders</h3>
                    </a>
                    <a href="/admin/messages.php" class="bg-purple-600 text-white p-6 rounded-lg hover:bg-purple-700 transition-colors text-center">
                        <i class="fas fa-envelope-open text-2xl mb-2"></i>
                        <h3 class="font-semibold">View Messages</h3>
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
