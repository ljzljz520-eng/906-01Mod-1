<?php

class Favorite {
    private $conn;
    private $table_name = "favorites";

    public $id;
    public $name;
    public $magnet;
    public $size;
    public $seeders;
    public $leechers;
    public $category;
    public $source;
    public $source_name;
    public $maintainer;
    public $authorization;
    public $mirror_health;
    public $last_checked_at;
    public $credibility;
    public $status;
    public $delisted_reason;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET name=:name, magnet=:magnet, size=:size, seeders=:seeders, leechers=:leechers, 
                      category=:category, source=:source, source_name=:source_name, maintainer=:maintainer,
                      authorization=:authorization, mirror_health=:mirror_health, last_checked_at=:last_checked_at,
                      credibility=:credibility, status=:status, delisted_reason=:delisted_reason";
        
        $stmt = $this->conn->prepare($query);

        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->magnet = htmlspecialchars(strip_tags($this->magnet));
        $this->size = htmlspecialchars(strip_tags($this->size));
        $this->seeders = htmlspecialchars(strip_tags($this->seeders));
        $this->leechers = htmlspecialchars(strip_tags($this->leechers));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->source = htmlspecialchars(strip_tags($this->source));
        $this->source_name = htmlspecialchars(strip_tags($this->source_name ?? $this->source));
        $this->maintainer = htmlspecialchars(strip_tags($this->maintainer ?? ''));
        $this->authorization = htmlspecialchars(strip_tags($this->authorization ?? ''));
        $this->mirror_health = htmlspecialchars(strip_tags($this->mirror_health ?? 'unknown'));
        $this->last_checked_at = htmlspecialchars(strip_tags($this->last_checked_at ?? null));
        $this->credibility = htmlspecialchars(strip_tags($this->credibility ?? 'normal'));
        $this->status = htmlspecialchars(strip_tags($this->status ?? 'active'));
        $this->delisted_reason = htmlspecialchars(strip_tags($this->delisted_reason ?? ''));

        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":magnet", $this->magnet);
        $stmt->bindParam(":size", $this->size);
        $stmt->bindParam(":seeders", $this->seeders);
        $stmt->bindParam(":leechers", $this->leechers);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":source", $this->source);
        $stmt->bindParam(":source_name", $this->source_name);
        $stmt->bindParam(":maintainer", $this->maintainer);
        $stmt->bindParam(":authorization", $this->authorization);
        $stmt->bindParam(":mirror_health", $this->mirror_health);
        $stmt->bindParam(":last_checked_at", $this->last_checked_at);
        $stmt->bindParam(":credibility", $this->credibility);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":delisted_reason", $this->delisted_reason);

        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function findOneByMagnet($magnet) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE magnet = :magnet LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":magnet", $magnet);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findAll() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY 
                  CASE credibility 
                    WHEN 'trusted' THEN 1 
                    WHEN 'normal' THEN 2 
                    WHEN 'pending' THEN 3 
                  END,
                  created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        
        if($stmt->execute()) {
            if($stmt->rowCount() > 0) {
                return true;
            }
        }
        return false;
    }
}
