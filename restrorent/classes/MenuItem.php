<?php
class MenuItem {
    private $conn;
    private $table_name = "menu_items";

    public $id;
    public $name;
    public $description;
    public $price;
    public $image_url;
    public $category_id;
    public $is_available;
    public $is_featured;
    public $ingredients;
    public $allergens;
    public $display_order;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all menu items by category
    public function getByCategory($category_id = null, $available_only = true) {
        $query = "SELECT m.*, c.name as category_name, c.slug as category_slug 
                  FROM " . $this->table_name . " m 
                  LEFT JOIN menu_categories c ON m.category_id = c.id 
                  WHERE 1=1";
        
        if ($category_id) {
            $query .= " AND m.category_id = :category_id";
        }
        
        if ($available_only) {
            $query .= " AND m.is_available = 1 AND c.is_active = 1";
        }
        
        $query .= " ORDER BY c.display_order ASC, m.display_order ASC, m.name ASC";

        $stmt = $this->conn->prepare($query);
        
        if ($category_id) {
            $stmt->bindParam(':category_id', $category_id);
        }
        
        $stmt->execute();
        return $stmt;
    }

    // Get featured items
    public function getFeaturedItems($limit = 6) {
        $query = "SELECT m.*, c.name as category_name 
                  FROM " . $this->table_name . " m 
                  LEFT JOIN menu_categories c ON m.category_id = c.id 
                  WHERE m.is_featured = 1 AND m.is_available = 1 AND c.is_active = 1
                  ORDER BY m.display_order ASC, m.name ASC 
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    // Get single item by ID
    public function getById($id) {
        $query = "SELECT m.*, c.name as category_name 
                  FROM " . $this->table_name . " m 
                  LEFT JOIN menu_categories c ON m.category_id = c.id 
                  WHERE m.id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt;
    }

    // Create new menu item
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET name=:name, description=:description, price=:price, 
                      image_url=:image_url, category_id=:category_id, 
                      is_available=:is_available, is_featured=:is_featured,
                      ingredients=:ingredients, allergens=:allergens, 
                      display_order=:display_order";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':image_url', $this->image_url);
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':is_available', $this->is_available);
        $stmt->bindParam(':is_featured', $this->is_featured);
        $stmt->bindParam(':ingredients', $this->ingredients);
        $stmt->bindParam(':allergens', $this->allergens);
        $stmt->bindParam(':display_order', $this->display_order);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Update menu item
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name=:name, description=:description, price=:price, 
                      image_url=:image_url, category_id=:category_id, 
                      is_available=:is_available, is_featured=:is_featured,
                      ingredients=:ingredients, allergens=:allergens, 
                      display_order=:display_order, updated_at=CURRENT_TIMESTAMP
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':image_url', $this->image_url);
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':is_available', $this->is_available);
        $stmt->bindParam(':is_featured', $this->is_featured);
        $stmt->bindParam(':ingredients', $this->ingredients);
        $stmt->bindParam(':allergens', $this->allergens);
        $stmt->bindParam(':display_order', $this->display_order);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete menu item
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Search menu items
    public function search($keyword) {
        $query = "SELECT m.*, c.name as category_name 
                  FROM " . $this->table_name . " m 
                  LEFT JOIN menu_categories c ON m.category_id = c.id 
                  WHERE m.is_available = 1 AND c.is_active = 1
                  AND (m.name LIKE :keyword OR m.description LIKE :keyword OR m.ingredients LIKE :keyword)
                  ORDER BY m.name ASC";

        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(':keyword', $keyword);
        $stmt->execute();
        return $stmt;
    }
}
?>
