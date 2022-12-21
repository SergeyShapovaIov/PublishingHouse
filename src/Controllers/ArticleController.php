<?php

namespace App\Controllers;

use App\Database\Storage\MagazineStorage;
use App\Exceptions\CreateArticleException;
use App\Exceptions\UploadPictureException;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \App\Database\Storage\ArticleStorage as ArticleStorage;
use Slim\Psr7\UploadedFile;
use Twig\Environment;
use Exception;

class ArticleController {

    private ArticleStorage $articleStorage;
    private MagazineStorage $magazineStorage;
    private Session $session;

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function __construct(Container $container)
    {
        $this->articleStorage = $container->get('articleStorage');
        $this->session = $container->get('session');
        $this->magazineStorage = $container->get('magazineStorage');
    }

    public function getAllArticles() : array
    {
        $articleStorage = new ArticleStorage();
        return $articleStorage->getAllArticles();
    }

    public function create(Request $request, Response $response, array $args): Response
    {
      $params = (array) $request->getParsedBody();

        try {
            $params['id_magazine'] = $this->magazineStorage->idByTitle($params['magazine']);
            $this->articleStorage->createNewArticle($params);
            return $response->withHeader('Location', '/')->withStatus(302);
        } catch (CreateArticleException|Exception $exception){
            $this->session->setData('message', $exception->getMessage());
            return $response->withHeader('Location', '/article-add')->withStatus(302);
        }

      $response->withStatus(200);
        return $response;
    }

    public function deleteById(Request $request, Response $response, array $args) : Response
    {
      $id = $request->getQueryParams()['id'];
      try {
        $this->articleStorage->deleteById($id);
        return $response->withHeader('Location', '/')->withStatus(302);
      } catch (Exception $exception) {
        $this->session->setData('message', $exception->getMessage());
        return $response->withStatus(501);
      }
    }

    public function edit(Request $request, Response $response, array $args) : Response
    {
      $id=$request->getQueryParams()['id'];
      $params = (array) $request->getParsedBody();
      $params['id_magazine'] = $this->magazineStorage->idByTitle($params['magazine_title']);
      try {
        $this->articleStorage->editArticle($params);
        return $response->withHeader('Location', '/')->withStatus(302);
      } catch (Exception $exception) {
        $this->session->setData('message', $exception->getMessage());
        return $response->withHeader('Location', '/article-edit?id='.$id)->withStatus(302);
      }
    }

    public function getAllArticlesByFilter($paramsQuery): array
    {
        $params = array(
            'title' => "%",
            'author' => "%",
            'magazine' => "%",
            'year_release' => "%"
        );
        if(array_key_exists('title', $paramsQuery)) {
            if($paramsQuery['title'] !== "") {
                $params['title'] = $paramsQuery['title'];
            }
        }
        if(array_key_exists('author', $paramsQuery)){
            if($paramsQuery['author'] !== ""){
                $params['author'] = $paramsQuery['author'];
            }
        }
        if(array_key_exists('magazine', $paramsQuery)){
            if($paramsQuery['magazine'] !== ""){
                $params['magazine'] = $paramsQuery['magazine'];
            }
        }
        if(array_key_exists('year_release', $paramsQuery)){
            if($paramsQuery['year_release'] !== ""){
                $params['year_release'] = $paramsQuery['year_release'];
            }
        }
        return $this->articleStorage->getAllArticlesByFilter($params);
    }

  /**
   * @throws UploadPictureException
   */
  public function changePictureById(Request $request, Response $response, array $args) : Response
    {
      $id = $request->getQueryParams()['id'];
      $directory = "templates/images/";
      $uploadedFiles = $request->getUploadedFiles();

      $uploadedFile = $uploadedFiles['picture'];

      try {
        $this->checkCorrectUploadPicture($uploadedFile);
        $filename = $this->moveUploadedFile($directory, $uploadedFile);
        $this->articleStorage->changePictureById($filename, $id);
        return $response->withHeader('Location', '/')->withStatus(302);
      } catch (UploadPictureException|Exception $exception) {
        $this->session->setData('message', $exception->getMessage());
        return $response->withHeader('Location', '/article-change-picture/?id='.$id )->withStatus(302);
      }
    }

  private function moveUploadedFile($directory, UploadedFile $uploadedFile) : string
  {
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
    $filename = sprintf('%s.%0.8s', $basename, $extension);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
  }

  /**
   * @throws UploadPictureException
   */
  private function checkCorrectUploadPicture($uploadedFile) : void
  {
    if($uploadedFile->getError() !== UPLOAD_ERR_OK) {
      throw new UploadPictureException("Error upload picture");
    }
  }
}