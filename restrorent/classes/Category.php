<?php
class Category {
    private $conn;
    private $table_name = "categories";

    public $id;
    public $name;
    public $slug;
    public $description;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all categories
    public function getAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get category by slug
    public function getBySlug($slug) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE slug = :slug";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':slug', $slug);
        $stmt->execute();
        return $stmt;
    }

    // Create category
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET name=:name, slug=:slug, description=:description";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':slug', $this->slug);
        $stmt->bindParam(':description', $this->description);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
