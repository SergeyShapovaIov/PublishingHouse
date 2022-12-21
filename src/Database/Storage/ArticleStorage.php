<?php

namespace App\Database\Storage;

use App\Exceptions\CreateArticleException;
use http\Params;

class ArticleStorage
{

  private \PDO $db;

  public function __construct(\PDO $db)
  {
    $this->db = $db;
  }

  public function getAllArticles()
  {

    $resultArticlesList = array();

    $stmt = $this->db->query("SELECT  id,article_announcement_picture , 
        articles.title, articles.articles_text, author,year_release , 
        magazines.magazine_title AS 'magazine_title' FROM PublishingHouse.articles 
        INNER JOIN PublishingHouse.magazines ON id_magazine = magazines.magazine_id");

    while ($row = $stmt->fetch()) {
      array_push($resultArticlesList, $row);
    }

    return $resultArticlesList;

  }

  public function getAllArticlesByFilter($params): array
  {

    $resultArticlesList = array();

    $stmt = $this->db->prepare('SELECT  id,article_announcement_picture , 
        articles.title, articles.articles_text, author,year_release , magazines.magazine_title AS magazine_title 
        FROM PublishingHouse.articles INNER JOIN PublishingHouse.magazines 
        ON id_magazine = magazines.magazine_id WHERE articles.`title` LIKE (:title) 
        AND magazines.magazine_title LIKE (:magazine) AND articles.author LIKE (:author) AND articles.year_release LIKE (:year_release)');

    $stmt->bindValue(':title', $params['title'], \PDO::PARAM_STR);
    $stmt->bindValue(':magazine', $params['magazine'], \PDO::PARAM_STR);
    $stmt->bindValue(':author', $params['author'], \PDO::PARAM_STR);
    $stmt->bindValue(':year_release', $params['year_release'], \PDO::PARAM_STR);
    $stmt->execute();
    while ($row = $stmt->fetch()) {
      $resultArticlesList[] = $row;
    }
    return $resultArticlesList;
  }

  public function getAllMagazines(): array
  {
    $magazines = array();
    $stmt = $this->db->query('SELECT * FROM PublishingHouse.magazines');
    while ($row = $stmt->fetch()) {
      $magazines[] = $row;
    }
    return $magazines;
  }

  /**
   * @throws CreateArticleException
   */
  public function createNewArticle($params): void
  {

    $this->throwIfEmpty($params, 'title');
    $this->throwIfEmpty($params, 'text');
    $this->throwIfEmpty($params, 'id_magazine');
    $this->throwIfEmpty($params, 'author');
    $this->throwIfEmpty($params, 'year_release');
    $stmt = $this->db->prepare('
        INSERT INTO PublishingHouse.articles (articles.article_announcement_picture, articles.title, articles.id_magazine, articles.articles_text, articles.author, articles.year_release) 
        VALUES (?, ?, ?, ?, ?, ?);      
    ');
    $stmt->execute([
      "templates/images/default-card-picture.png",
      $params['title'],
      intval($params['id_magazine']),
      $params['text'],
      $params['author'],
      $params['year_release']
    ]);
  }

  public function deleteById($id) : void
  {
    $stmt = $this->db->prepare('DELETE FROM PublishingHouse.articles WHERE id = :id');
    $stmt->bindValue(':id', $id);
    $stmt->execute();
  }

  public function getArticleById($id) : array
  {
    $stmt = $this->db->prepare('SELECT  id,article_announcement_picture , 
        articles.title, articles.articles_text, author,year_release , magazines.magazine_title AS magazine_title 
        FROM PublishingHouse.articles INNER JOIN PublishingHouse.magazines 
        ON id_magazine = magazines.magazine_id WHERE id = ?');
    $stmt->execute([
      $id
    ]);
    return $stmt->fetch();
  }

  /**
   * @throws CreateArticleException
   */
  public function editArticle($params) : void
  {
    $this->throwIfEmpty($params, 'title');
    $this->throwIfEmpty($params, 'text');
    $this->throwIfEmpty($params, 'id_magazine');
    $this->throwIfEmpty($params, 'author');
    $this->throwIfEmpty($params, 'year_release');

    $stmt = $this->db->prepare('
                UPDATE PublishingHouse.articles 
                SET title = :title, id_magazine = :id_magazine, articles_text = :articles_text, author = :author, year_release = :year_release
                WHERE id = :id
    ');
    $stmt->execute([
      'title' => $params['title'],
      'id_magazine' => $params['id_magazine'],
      'articles_text' => $params['articles_text'],
      'author' => $params['author'],
      'year_release' => $params['year_release'],
      'id' => $params['id']
    ]);
  }

  public function changePictureById ($picture, $id) : void
  {
    $stmt = $this->db->prepare('
    UPDATE PublishingHouse.articles 
    SET article_announcement_picture = :picture
    WHERE id = :id
    ');
    $stmt->execute([
      'picture' => "templates/images/".$picture,
      'id' => $id
    ]);
  }

  /**
   * @throws CreateArticleException
   */
  private function throwIfEmpty(array $array, string $key): void
  {
    if (empty($array[$key])) {
      throw new CreateArticleException("Field: " . $key . " must not be empty");
    }
  }

}

