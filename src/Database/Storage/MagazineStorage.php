<?php

namespace App\Database\Storage;

class MagazineStorage {

    private \PDO $db;

    public function __construct(\PDO $db){
        $this->db = $db;
    }

    public function idByTitle ($title) : int
    {
        $stmt = $this->db->prepare('SELECT magazine_id FROM PublishingHouse.magazines WHERE magazine_title = :title LIMIT 1');
        $stmt->execute([
          'title' => $title
        ]);
        return $stmt->fetchAll()[0]['magazine_id'];
    }
}