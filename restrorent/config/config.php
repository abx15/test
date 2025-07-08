<?php
// Site configuration
define('SITE_NAME', 'Delicious Bites');
define('SITE_URL', 'http://localhost');
define('ADMIN_EMAIL', 'admin@restaurant.com');
define('POSTS_PER_PAGE', 6); // Added for blog functionality

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'restaurant_website');
define('DB_USER', 'root');
define('DB_PASS', '');

// Upload configuration
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Order configuration
define('DELIVERY_FEE', 5.99);
define('MINIMUM_ORDER', 25.00);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Database connection class
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->dbname, $this->user, $this->pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            error_log("Connection error: " . $exception->getMessage());
            throw $exception;
        }
        return $this->conn;
    }
}

// Helper functions
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generateSlug($string) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
}

function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /admin/login.php');
        exit();
    }
}

function formatPrice($price) {
    return '$' . number_format($price, 2);
}

function formatDate($date, $format = 'M j, Y g:i A') {
    return date($format, strtotime($date));
}

function generateOrderNumber() {
    return 'ORD' . date('Ymd') . rand(1000, 9999);
}

function sendEmail($to, $subject, $message) {
    $headers = "From: " . ADMIN_EMAIL . "\r\n";
    $headers .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Restaurant-specific functions
function getMenuCategories($conn) {
    $stmt = $conn->prepare("SELECT * FROM menu_categories WHERE is_active = 1 ORDER BY display_order");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPopularItems($conn, $limit = 4) {
    $stmt = $conn->prepare("SELECT * FROM menu_items WHERE is_popular = 1 AND is_active = 1 LIMIT ?");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>