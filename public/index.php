<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use Nyholm\Psr7\UploadedFile;

require __DIR__ . '/../inc/config.php';
require __DIR__ . '/../model/UserModel.php';

require __DIR__ . '/../vendor/autoload.php';

/**
 * Instantiate App
 *
 * In order for the factory to work you need to ensure you have installed
 * a supported PSR-7 implementation of your choice e.g.: Slim PSR-7 and a supported
 * ServerRequest creator (included with Slim PSR-7)
 */
$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$app->options('/{routes:.+}', function ($request, $response, $args) {
  return $response;
});

$app->add(function ($req, $handler) {
  $response = $handler->handle($req);
  return $response
    ->withHeader('Access-Control-Allow-Origin', '*')
    ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH');
});


$app->get('/users', function (Request $request, Response $response, $args) {
  $user = new UserModel();

  $data = $user->getAll();

  $response->withHeader('Content-Type', 'application/json');
  $response->getBody()->write(json_encode($data));

  return $response;
});

$app->get('/users/{id}', function (Request $request, Response $response, $args) {

  try {
    $user = new UserModel();

    $data = $user->get($args['id']);

    $response->withHeader('Content-Type', 'application/json');
    $response->getBody()->write(json_encode($data));
    return $response;
  } catch (Exception $e) {
    $response = $response->withStatus(500);
    $response->getBody()->write(json_encode($e));
    return $response;
  }
});

$app->post('/api/login', function (Request $request, Response $response, $args) {
  $data = $request->getParsedBody();
  $username = $data['username'];
  $password = $data['password'];

  try {
    $user = new UserModel();

    $data = $user->verify($username, $password);

    if ($data !== null) {
      $response = $response->withStatus(200);
      $response->getBody()->write(json_encode($data));
      return $response;
    } else {
      $response = $response->withStatus(401);
      $response->getBody()->write("{\"error\": \"Invalid username or password\"}");
      return $response;
    }
  } catch (Exception $e) {
    $response = $response->withStatus(500);
    $response->getBody()->write("{\"error\": \"" . $e->getMessage() . "\"}");
    return $response;
  }
});

$app->post('/api/signup', function (Request $request, Response $response, $args) {
  $data = $request->getParsedBody();
  $username = $data['username'];
  $password = $data['password'];
  $email = $data['email'];
  $fullName = $data['fullName'];

  try {
    $user = new UserModel();

    $data = $user->getByUsername($username);

    if ($data) {
      $response = $response->withStatus(400);
      $response->getBody()->write(json_encode([
        'success' => false,
        'message' => 'Username already exists'
      ]));
      return $response;
    } else {
      $user->create($username, $password, $email, $fullName);

      $response = $response->withStatus(200);
      $response->getBody()->write(json_encode([
        'success' => true,
        'message' => 'User created successfully'
      ]));
      return $response;
    }
  } catch (Exception $e) {
    $response = $response->withStatus(500);
    $response->getBody()->write("{\"error\": \"" . $e->getMessage() . "\"}");
    return $response;
  }
});


// Run app
$app->run();
