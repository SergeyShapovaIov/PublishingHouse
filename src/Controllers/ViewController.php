<?php

namespace App\Controllers;

use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use \App\Database\Storage\ArticleStorage as ArticleStorage;
use \App\Controllers\ArticleController as ArticleController;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;


class ViewController{

    private Environment $view;
    private Session $session;
    private ArticleStorage $articleStorage;
    private ArticleController $articleController;

    /**
     * @throws NotFoundException
     * @throws DependencyException
     */
    public function __construct(Container $container){
        $this->view = $container->get('view');
        $this->session = $container->get('session');
        $this->articleStorage = $container->get('articleStorage');
        $this->articleController = $container->get('articleController');
    }


    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function index(Request $request, Response $response)
    {
        $paramsQuery = $request->getQueryParams();
        $articles = $this->articleController->getAllArticlesByFilter($paramsQuery);
        if(!array_key_exists('title', $paramsQuery )){
            $params = array(
                'title' => "%",
                'author' => "%",
                'magazine' => "%",
                'year_release' => "%"
            );
            $articles = $this->articleController->getAllArticlesByFilter($params);
        } else {
            $articles = $this->articleController->getAllArticlesByFilter($paramsQuery);
        }
        $body = $this->view->render('home.twig', [
            'articles' => $articles,
            'user' => $this->session->getData('user')
        ]);
        $response->getBody()->write($body);
        return $response;
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function login (Request $request, Response $response, array $args){
        $body = $this->view->render('login.twig', [
            'message'=>$this->session->flush('message')
        ]);
        $response->getBody()->write($body);
        return $response;
    }

    public function registration (
        Request $request,
        Response $response,
        array $args
    ): Response
    {
        $body = $this->view->render('registration.twig',[
            'message'=>$this->session->flush('message')
        ]);
           $response->getBody()->write($body);
        return $response;

    }

    public function editArticle (
        Request $request,
        Response $response,
        array $args
    ): Response
    {
        $id = $request->getQueryParams()['id'];
        $article = $this->articleStorage->getArticleById($id);
        $magazines = $this->articleStorage->getAllMagazines();
        $body = $this->view->render('edit-article.twig',[
            'message'=>$this->session->flush('message'),
            'article'=>$article,
            'magazines'=>$magazines,
            'user' => $this->session->getData('user')
        ]);
        $response->getBody()->write($body);
        return $response;
    }

    public function addArticle (
        Request $request,
        Response $response,
        array $args
    ): Response
    {
        $magazines = $this->articleStorage->getAllMagazines();
        $body = $this->view->render('add-article.twig',[
            'message'=>$this->session->flush('message'),
            'magazines'=>$magazines,
            'user' => $this->session->getData('user')
        ]);
        $response->getBody()->write($body);
        return $response;
    }

    public function changePicture (
        Request $request,
        Response $response,
        array $args
    ): Response
    {
      $id = $request->getQueryParams()['id'];
      $body = $this->view->render('change-picture.twig',[
        'message'=>$this->session->flush('message'),
        'id' => $id,
        'user' => $this->session->getData('user')
      ]);

      $response->getBody()->write($body);
      return $response;
    }

}