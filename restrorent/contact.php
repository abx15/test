<?php
require_once 'config/config.php';

$database = new Database();
$db = $database->getConnection();

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    
    // Validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        // Insert contact message
        $query = "INSERT INTO contact_messages (name, email, phone, subject, message) 
                  VALUES (:name, :email, :phone, :subject, :message)";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':subject' => $subject,
            ':message' => $message
        ])) {
            $success_message = 'Thank you for your message! We\'ll get back to you soon.';
            // Clear form data
            $name = $email = $phone = $subject = $message = '';
        } else {
            $error_message = 'Sorry, there was an error sending your message. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Get in touch with <?php echo SITE_NAME; ?>. We'd love to hear from you!">
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
                    <a href="/contact.php" class="text-primary font-medium transition-colors">Contact</a>
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
                <a href="/order.php" class="block py-2 text-gray-700 hover:text-primary">Order Online</a>
                <a href="/contact.php" class="block py-2 text-primary font-medium">Contact</a>
            </div>
        </div>
    </nav>

    <!-- Contact Header -->
    <section class="pt-20 pb-12 bg-gradient-to-r from-primary to-secondary text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Contact Us</h1>
            <p class="text-xl opacity-90">We'd love to hear from you. Get in touch!</p>
        </div>
    </section>

    <!-- Contact Content -->
    <section class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Contact Form -->
                <div class="bg-white rounded-lg shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-6">Send us a Message</h2>
                    
                    <?php if ($success_message): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                            <i class="fas fa-check-circle mr-2"></i>
                            <?php echo $success_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error_message): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                            <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($subject ?? ''); ?>" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-2">Message *</label>
                            <textarea id="message" name="message" rows="6" required
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                        </div>
                        
                        <button type="submit" class="w-full bg-primary text-white py-3 px-6 rounded-lg font-semibold hover:bg-secondary transition-colors">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Send Message
                        </button>
                    </form>
                </div>
                
                <!-- Contact Information -->
                <div class="space-y-8">
                    <div class="bg-white rounded-lg shadow-lg p-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Get in Touch</h2>
                        
                        <div class="space-y-6">
                            <div class="flex items-start">
                                <div class="bg-primary text-white p-3 rounded-lg mr-4">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-800 mb-1">Address</h3>
                                    <p class="text-gray-600">123 Food Street<br>Culinary City, CC 12345</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="bg-primary text-white p-3 rounded-lg mr-4">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-800 mb-1">Phone</h3>
                                    <p class="text-gray-600">+1 (555) 123-4567</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="bg-primary text-white p-3 rounded-lg mr-4">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-800 mb-1">Email</h3>
                                    <p class="text-gray-600">info@deliciousbites.com</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="bg-primary text-white p-3 rounded-lg mr-4">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-800 mb-1">Hours</h3>
                                    <p class="text-gray-600">
                                        Mon-Thu: 11:00 AM - 10:00 PM<br>
                                        Fri-Sat: 11:00 AM - 11:00 PM<br>
                                        Sun: 12:00 PM - 9:00 PM
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-lg p-8">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6">Follow Us</h2>
                        
                        <div class="flex space-x-4">
                            <a href="#" class="bg-blue-600 text-white p-3 rounded-lg hover:bg-blue-700 transition-colors">
                                <i class="fab fa-facebook text-xl"></i>
                            </a>
                            <a href="#" class="bg-pink-600 text-white p-3 rounded-lg hover:bg-pink-700 transition-colors">
                                <i class="fab fa-instagram text-xl"></i>
                            </a>
                            <a href="#" class="bg-blue-400 text-white p-3 rounded-lg hover:bg-blue-500 transition-colors">
                                <i class="fab fa-twitter text-xl"></i>
                            </a>
                            <a href="#" class="bg-red-600 text-white p-3 rounded-lg hover:bg-red-700 transition-colors">
                                <i class="fab fa-yelp text-xl"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-primary to-secondary text-white rounded-lg p-8">
                        <h2 class="text-2xl font-bold mb-4">Ready to Order?</h2>
                        <p class="mb-4">Experience our delicious food delivered to your door or ready for pickup.</p>
                        <a href="/order.php" class="bg-white text-primary px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition-colors inline-block">
                            Order Online
                        </a>
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
        // Mobile menu toggle
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>
