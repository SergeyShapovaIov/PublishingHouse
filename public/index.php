<?php

use App\Controllers\ArticleController;
use App\Controllers\AuthorizationController;
use App\Controllers\ViewController;
use App\Controllers\Session;
use App\Database\Storage\ArticleStorage;
use App\Database\Storage\MagazineStorage;
use App\Database\Storage\UserStorage;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use DI\Container as Container;
use Slim\Factory\AppFactory;
use Slim\Http\Response;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once dirname(__DIR__).'/vendor/autoload.php';
require_once dirname(__DIR__).'/Config.php';

/* Отключаем предупреждения об устаревших функциях */
error_reporting(E_ALL & ~E_DEPRECATED);

if(!file_exists(dirname(__DIR__) . '/Config.php')){
    print_r("The configuration file in the root directory does not exist");
    throw new RuntimeException();
};
if(!isset($generalConfig['MYSQL_PATH'])){
    print_r("Configuration file is not set up correctly");
    throw new RuntimeException();
};

/*Устанавливаем подключение к базе данны*/
$db = new \PDO($generalConfig['MYSQL_PATH'], 'root', 'root');

/* Используем twig*/
$loader = new FilesystemLoader ('templates');
$view = new Environment ($loader);

$container = new Container();

$articleStorage = new ArticleStorage($db);
$userStorage = new UserStorage($db);
$magazineStorage = new MagazineStorage($db);

$session = new Session();
$sessionMiddleware = function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($session){
    $session->start();
    $response = $handler->handle($request);
    $session->save();

    return $response;
};

$container->set('view', $view);
$container->set('db', $db);
$container->set('session', $session);
$container->set('articleStorage', $articleStorage);
$container->set('userStorage', $userStorage);
$container->set('magazineStorage', $magazineStorage);


AppFactory::setContainer($container);
$app = AppFactory::create();

$container = $app->getContainer();

$app->add($sessionMiddleware);
$authorizationController = new AuthorizationController($container);
$articleController = new ArticleController($container);
$container->set('articleController', $articleController);
$viewController = new ViewController($container);

$app->get('/', ViewController::class . ':index');
$app->get('/login', ViewController::class . ':login');
$app->get('/registration', ViewController::class . ':registration');
$app->get('/article-add', ViewController::class . ':addArticle');
$app->get('/article-edit', ViewController::class . ':editArticle');
$app->get('/article-change-picture', ViewController::class . ':changePicture');

$app->post('/api/login', AuthorizationController::class . ':login');
$app->post('/api/registration', AuthorizationController::class . ':registration');
$app->post('/api/logout', AuthorizationController::class . ':logout');

$app->get('/api/article/deleteById/', ArticleController::class . ':deleteById');
$app->post('/api/article/create', ArticleController::class . ':create');
$app->post('/api/article/update', ArticleController::class . ':update');
$app->post('/api/article/edit', ArticleController::class . ':edit');
$app->post('/api/article/changePictureById', ArticleController::class . ':changePictureById');

$app->run();

