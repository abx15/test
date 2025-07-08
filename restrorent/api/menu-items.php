<?php
header('Content-Type: application/json');
require_once '../config/config.php';
require_once '../classes/MenuItem.php';
require_once '../classes/MenuCategory.php';

$database = new Database();
$db = $database->getConnection();

$menuItem = new MenuItem($db);

try {
    $stmt = $menuItem->getByCategory();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($items);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load menu items']);
}
?>
