<?php
class MenuCategory {
    private $conn;
    private $table_name = "menu_categories";

    public $id;
    public $name;
    public $slug;
    public $description;
    public $display_order;
    public $is_active;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all active categories
    public function getAll($active_only = true) {
        $query = "SELECT * FROM " . $this->table_name;
        
        if ($active_only) {
            $query .= " WHERE is_active = 1";
        }
        
        $query .= " ORDER BY display_order ASC, name ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get category by slug
    public function getBySlug($slug) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE slug = :slug AND is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        return $stmt;
    }

    // Get category by ID
    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt;
    }

    // Create category
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET name=:name, slug=:slug, description=:description, 
                      display_order=:display_order, is_active=:is_active";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':slug', $this->slug);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':display_order', $this->display_order);
        $stmt->bindParam(':is_active', $this->is_active);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Update category
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET name=:name, slug=:slug, description=:description, 
                      display_order=:display_order, is_active=:is_active,
                      updated_at=CURRENT_TIMESTAMP
                  WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':slug', $this->slug);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':display_order', $this->display_order);
        $stmt->bindParam(':is_active', $this->is_active);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete category
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
