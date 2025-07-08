<?php
require_once 'config/config.php';
require_once 'classes/MenuItem.php';
require_once 'classes/MenuCategory.php';
require_once 'classes/Order.php';

$database = new Database();
$db = $database->getConnection();

$menuItem = new MenuItem($db);
$menuCategory = new MenuCategory($db);
$order = new Order($db);

$success_message = '';
$error_message = '';

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = sanitize($_POST['customer_name'] ?? '');
    $customer_email = sanitize($_POST['customer_email'] ?? '');
    $customer_phone = sanitize($_POST['customer_phone'] ?? '');
    $customer_address = sanitize($_POST['customer_address'] ?? '');
    $order_type = sanitize($_POST['order_type'] ?? 'delivery');
    $special_instructions = sanitize($_POST['special_instructions'] ?? '');
    $delivery_time = sanitize($_POST['delivery_time'] ?? '');
    $cart_items = json_decode($_POST['cart_items'] ?? '[]', true);
    
    // Validation
    if (empty($customer_name) || empty($customer_email) || empty($customer_phone)) {
        $error_message = 'Please fill in all required fields.';
    } elseif (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } elseif (empty($cart_items)) {
        $error_message = 'Your cart is empty. Please add items before placing an order.';
    } else {
        // Calculate total
        $subtotal = 0;
        $order_items = [];
        
        foreach ($cart_items as $cart_item) {
            $item_stmt = $menuItem->getById($cart_item['id']);
            $item_data = $item_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($item_data && $item_data['is_available']) {
                $item_subtotal = $item_data['price'] * $cart_item['quantity'];
                $subtotal += $item_subtotal;
                
                $order_items[] = [
                    'menu_item_id' => $item_data['id'],
                    'item_name' => $item_data['name'],
                    'item_price' => $item_data['price'],
                    'quantity' => $cart_item['quantity'],
                    'subtotal' => $item_subtotal,
                    'special_notes' => $cart_item['notes'] ?? ''
                ];
            }
        }
        
        // Add delivery fee if applicable
        $delivery_fee = ($order_type === 'delivery') ? DELIVERY_FEE : 0;
        $total_amount = $subtotal + $delivery_fee;
        
        // Check minimum order
        if ($order_type === 'delivery' && $subtotal < MINIMUM_ORDER) {
            $error_message = 'Minimum order for delivery is ' . formatPrice(MINIMUM_ORDER);
        } else {
            // Create order
            $order->order_number = generateOrderNumber();
            $order->customer_name = $customer_name;
            $order->customer_email = $customer_email;
            $order->customer_phone = $customer_phone;
            $order->customer_address = $customer_address;
            $order->order_type = $order_type;
            $order->total_amount = $total_amount;
            $order->status = 'pending';
            $order->special_instructions = $special_instructions;
            $order->delivery_time = $delivery_time ? date('Y-m-d H:i:s', strtotime($delivery_time)) : null;
            
            if ($order->create() && $order->addItems($order_items)) {
                $success_message = 'Order placed successfully! Order number: ' . $order->order_number;
                
                // Send confirmation email (optional)
                $email_subject = 'Order Confirmation - ' . $order->order_number;
                $email_message = "Thank you for your order!\n\nOrder Number: " . $order->order_number . "\nTotal: " . formatPrice($total_amount);
                // sendEmail($customer_email, $email_subject, $email_message);
                
                // Clear form data
                $customer_name = $customer_email = $customer_phone = $customer_address = $special_instructions = $delivery_time = '';
            } else {
                $error_message = 'Sorry, there was an error processing your order. Please try again.';
            }
        }
    }
}

// Get categories for menu
$categories_stmt = $menuCategory->getAll();
$categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Online - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Order delicious food online for delivery or pickup from <?php echo SITE_NAME; ?>.">
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
                    <a href="/order.php" class="text-primary font-medium transition-colors">Order Online</a>
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
                <a href="/menu.php" class="block py-2 text-gray-700 hover:text-primary">Menu</a>
                <a href="/order.php" class="block py-2 text-primary font-medium">Order Online</a>
                <a href="/contact.php" class="block py-2 text-gray-700 hover:text-primary">Contact</a>
            </div>
        </div>
    </nav>

    <!-- Order Header -->
    <section class="pt-20 pb-12 bg-gradient-to-r from-primary to-secondary text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Order Online</h1>
            <p class="text-xl opacity-90">Choose your favorite dishes for delivery or pickup</p>
        </div>
    </section>

    <!-- Order Form -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <?php if ($success_message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-lg mb-8">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-2xl mr-3"></i>
                        <div>
                            <h3 class="font-semibold">Order Successful!</h3>
                            <p><?php echo $success_message; ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-lg mb-8">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-2xl mr-3"></i>
                        <div>
                            <h3 class="font-semibold">Error</h3>
                            <p><?php echo $error_message; ?></p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Menu Selection -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Select Items</h2>
                        
                        <!-- Category Tabs -->
                        <div class="flex flex-wrap gap-2 mb-6">
                            <button onclick="showCategory('all')" class="category-btn active px-4 py-2 rounded-lg font-medium transition-colors">
                                All Items
                            </button>
                            <?php foreach($categories as $category): ?>
                                <button onclick="showCategory('<?php echo $category['slug']; ?>')" class="category-btn px-4 py-2 rounded-lg font-medium transition-colors">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>

                        <!-- Menu Items -->
                        <div id="menu-items" class="space-y-4">
                            <!-- Items will be loaded here via JavaScript -->
                        </div>
                    </div>
                </div>

                <!-- Order Summary & Customer Info -->
                <div class="lg:col-span-1">
                    <!-- Cart -->
                    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Your Order</h3>
                        <div id="cart-items" class="space-y-3 mb-4">
                            <p class="text-gray-500 text-center py-4">Your cart is empty</p>
                        </div>
                        <div id="cart-summary" class="border-t pt-4 hidden">
                            <div class="flex justify-between mb-2">
                                <span>Subtotal:</span>
                                <span id="subtotal">$0.00</span>
                            </div>
                            <div id="delivery-fee-row" class="flex justify-between mb-2 hidden">
                                <span>Delivery Fee:</span>
                                <span><?php echo formatPrice(DELIVERY_FEE); ?></span>
                            </div>
                            <div class="flex justify-between font-bold text-lg border-t pt-2">
                                <span>Total:</span>
                                <span id="total">$0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information Form -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Customer Information</h3>
                        
                        <form method="POST" id="order-form">
                            <input type="hidden" name="cart_items" id="cart_items_input">
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                                    <input type="text" id="customer_name" name="customer_name" required
                                           value="<?php echo htmlspecialchars($customer_name ?? ''); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label for="customer_email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                    <input type="email" id="customer_email" name="customer_email" required
                                           value="<?php echo htmlspecialchars($customer_email ?? ''); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                                    <input type="tel" id="customer_phone" name="customer_phone" required
                                           value="<?php echo htmlspecialchars($customer_phone ?? ''); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Order Type *</label>
                                    <div class="space-y-2">
                                        <label class="flex items-center">
                                            <input type="radio" name="order_type" value="delivery" checked onchange="updateOrderType()"
                                                   class="text-primary focus:ring-primary">
                                            <span class="ml-2">Delivery (<?php echo formatPrice(DELIVERY_FEE); ?> fee, min <?php echo formatPrice(MINIMUM_ORDER); ?>)</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" name="order_type" value="pickup" onchange="updateOrderType()"
                                                   class="text-primary focus:ring-primary">
                                            <span class="ml-2">Pickup (No fee)</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <div id="address-field">
                                    <label for="customer_address" class="block text-sm font-medium text-gray-700 mb-1">Delivery Address *</label>
                                    <textarea id="customer_address" name="customer_address" rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"><?php echo htmlspecialchars($customer_address ?? ''); ?></textarea>
                                </div>
                                
                                <div>
                                    <label for="delivery_time" class="block text-sm font-medium text-gray-700 mb-1">Preferred Time (Optional)</label>
                                    <input type="datetime-local" id="delivery_time" name="delivery_time"
                                           value="<?php echo htmlspecialchars($delivery_time ?? ''); ?>"
                                           min="<?php echo date('Y-m-d\TH:i'); ?>"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label for="special_instructions" class="block text-sm font-medium text-gray-700 mb-1">Special Instructions</label>
                                    <textarea id="special_instructions" name="special_instructions" rows="3" placeholder="Any special requests or dietary requirements..."
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"><?php echo htmlspecialchars($special_instructions ?? ''); ?></textarea>
                                </div>
                                
                                <button type="submit" id="place-order-btn" disabled
                                        class="w-full bg-gray-400 text-white py-3 rounded-lg font-semibold cursor-not-allowed">
                                    <i class="fas fa-shopping-cart mr-2"></i>
                                    Place Order
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
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
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        let menuItems = [];
        const DELIVERY_FEE = <?php echo DELIVERY_FEE; ?>;
        const MINIMUM_ORDER = <?php echo MINIMUM_ORDER; ?>;

        // Load menu items
        async function loadMenuItems() {
            try {
                const response = await fetch('/api/menu-items.php');
                menuItems = await response.json();
                showCategory('all');
                updateCart();
            } catch (error) {
                console.error('Error loading menu items:', error);
            }
        }

        // Show category
        function showCategory(categorySlug) {
            const container = document.getElementById('menu-items');
            const buttons = document.querySelectorAll('.category-btn');
            
            // Update active button
            buttons.forEach(btn => {
                btn.classList.remove('active', 'bg-primary', 'text-white');
                btn.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
            });
            
            event.target.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
            event.target.classList.add('active', 'bg-primary', 'text-white');
            
            // Filter items
            const filteredItems = categorySlug === 'all' 
                ? menuItems 
                : menuItems.filter(item => item.category_slug === categorySlug);
            
            // Render items
            container.innerHTML = filteredItems.map(item => `
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="font-semibold text-gray-800">${item.name}</h4>
                        <span class="text-lg font-bold text-primary">$${parseFloat(item.price).toFixed(2)}</span>
                    </div>
                    <p class="text-gray-600 text-sm mb-3">${item.description}</p>
                    ${item.allergens ? `<p class="text-xs text-red-600 mb-3"><i class="fas fa-exclamation-triangle mr-1"></i>Contains: ${item.allergens}</p>` : ''}
                    <button onclick="addToCart(${item.id}, '${item.name}', ${item.price})" 
                            class="w-full bg-primary text-white py-2 rounded-lg font-medium hover:bg-secondary transition-colors">
                        <i class="fas fa-plus mr-1"></i> Add to Cart
                    </button>
                </div>
            `).join('');
        }

        // Add to cart
        function addToCart(itemId, itemName, itemPrice) {
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
            updateCart();
        }

        // Remove from cart
        function removeFromCart(itemId) {
            cart = cart.filter(item => item.id !== itemId);
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCart();
        }

        // Update quantity
        function updateQuantity(itemId, newQuantity) {
            if (newQuantity <= 0) {
                removeFromCart(itemId);
                return;
            }
            
            const item = cart.find(item => item.id === itemId);
            if (item) {
                item.quantity = newQuantity;
                localStorage.setItem('cart', JSON.stringify(cart));
                updateCart();
            }
        }

        // Update cart display
        function updateCart() {
            const cartContainer = document.getElementById('cart-items');
            const cartSummary = document.getElementById('cart-summary');
            const placeOrderBtn = document.getElementById('place-order-btn');
            
            if (cart.length === 0) {
                cartContainer.innerHTML = '<p class="text-gray-500 text-center py-4">Your cart is empty</p>';
                cartSummary.classList.add('hidden');
                placeOrderBtn.disabled = true;
                placeOrderBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
                placeOrderBtn.classList.remove('bg-primary', 'hover:bg-secondary');
                return;
            }
            
            let subtotal = 0;
            cartContainer.innerHTML = cart.map(item => {
                const itemTotal = item.price * item.quantity;
                subtotal += itemTotal;
                
                return `
                    <div class="flex justify-between items-center py-2 border-b">
                        <div class="flex-1">
                            <h4 class="font-medium text-sm">${item.name}</h4>
                            <p class="text-xs text-gray-500">$${parseFloat(item.price).toFixed(2)} each</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="updateQuantity(${item.id}, ${item.quantity - 1})" 
                                    class="w-6 h-6 bg-gray-200 rounded text-xs hover:bg-gray-300">-</button>
                            <span class="text-sm font-medium">${item.quantity}</span>
                            <button onclick="updateQuantity(${item.id}, ${item.quantity + 1})" 
                                    class="w-6 h-6 bg-gray-200 rounded text-xs hover:bg-gray-300">+</button>
                            <button onclick="removeFromCart(${item.id})" 
                                    class="text-red-500 hover:text-red-700 ml-2">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
            
            // Update totals
            const orderType = document.querySelector('input[name="order_type"]:checked').value;
            const deliveryFee = orderType === 'delivery' ? DELIVERY_FEE : 0;
            const total = subtotal + deliveryFee;
            
            document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
            document.getElementById('total').textContent = `$${total.toFixed(2)}`;
            
            const deliveryFeeRow = document.getElementById('delivery-fee-row');
            if (orderType === 'delivery') {
                deliveryFeeRow.classList.remove('hidden');
            } else {
                deliveryFeeRow.classList.add('hidden');
            }
            
            cartSummary.classList.remove('hidden');
            
            // Enable/disable order button
            const canOrder = cart.length > 0 && (orderType === 'pickup' || subtotal >= MINIMUM_ORDER);
            placeOrderBtn.disabled = !canOrder;
            
            if (canOrder) {
                placeOrderBtn.classList.remove('bg-gray-400', 'cursor-not-allowed');
                placeOrderBtn.classList.add('bg-primary', 'hover:bg-secondary');
                placeOrderBtn.innerHTML = '<i class="fas fa-shopping-cart mr-2"></i>Place Order';
            } else {
                placeOrderBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
                placeOrderBtn.classList.remove('bg-primary', 'hover:bg-secondary');
                if (orderType === 'delivery' && subtotal < MINIMUM_ORDER) {
                    placeOrderBtn.innerHTML = `<i class="fas fa-exclamation-triangle mr-2"></i>Minimum $${MINIMUM_ORDER.toFixed(2)} for delivery`;
                } else {
                    placeOrderBtn.innerHTML = '<i class="fas fa-shopping-cart mr-2"></i>Add items to order';
                }
            }
            
            // Update hidden input
            document.getElementById('cart_items_input').value = JSON.stringify(cart);
        }

        // Update order type
        function updateOrderType() {
            const addressField = document.getElementById('address-field');
            const orderType = document.querySelector('input[name="order_type"]:checked').value;
            
            if (orderType === 'delivery') {
                addressField.style.display = 'block';
                document.getElementById('customer_address').required = true;
            } else {
                addressField.style.display = 'none';
                document.getElementById('customer_address').required = false;
            }
            
            updateCart();
        }

        // Mobile menu toggle
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });

        // Form submission
        document.getElementById('order-form').addEventListener('submit', function(e) {
            if (cart.length === 0) {
                e.preventDefault();
                alert('Please add items to your cart before placing an order.');
                return;
            }
            
            const orderType = document.querySelector('input[name="order_type"]:checked').value;
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            
            if (orderType === 'delivery' && subtotal < MINIMUM_ORDER) {
                e.preventDefault();
                alert(`Minimum order for delivery is $${MINIMUM_ORDER.toFixed(2)}`);
                return;
            }
            
            // Clear cart on successful submission
            if (!e.defaultPrevented) {
                localStorage.removeItem('cart');
            }
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadMenuItems();
            updateOrderType();
        });
    </script>
</body>
</html>
