<?php
class Post {
    private $conn;
    private $table = 'posts';

    public $id;
    public $title;
    public $slug;
    public $excerpt;
    public $content;
    public $featured_image;
    public $created_at;
    public $updated_at;
    public $views;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getPublishedPosts($page = 1) {
        $limit = defined('POSTS_PER_PAGE') ? POSTS_PER_PAGE : 6;
        $offset = ($page - 1) * $limit;

        $query = "SELECT * FROM " . $this->table . " 
                 WHERE status = 'published' 
                 ORDER BY created_at DESC 
                 LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt;
    }

    public function getBySlug($slug) {
        $query = "SELECT * FROM " . $this->table . " 
                 WHERE slug = ? AND status = 'published' 
                 LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$slug]);

        return $stmt;
    }

    public function incrementViews() {
        $query = "UPDATE " . $this->table . " SET views = views + 1 WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$this->id]);
    }

    public function getRecentPosts($limit = 3) {
        $query = "SELECT id, title, slug, featured_image, created_at 
                 FROM " . $this->table . " 
                 WHERE status = 'published' 
                 ORDER BY created_at DESC 
                 LIMIT ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>