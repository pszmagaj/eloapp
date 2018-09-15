<?php
use Elo\Controller\AuthCtrl;
use Elo\middleware\IsLoggedMiddleware;
use PSR7Sessions\Storageless\Http\SessionMiddleware;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

require 'config.php';
require 'vendor/autoload.php';
$container = require 'container.php';

$app = new \Slim\App($container);

$app->add(SessionMiddleware::fromSymmetricKeyDefaults(
	'mBC51OKVvdie2cjfdSBenu59nfNfhwkedkJVNabosTw=',
	1200// 20 minutes
));

$app->get('/auth/isLogged', function (Request $request, Response $response) {
	$session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
	$isLogged = AuthCtrl::isLogged($session);
	return $response->withJson(['isLogged' => $isLogged]);
});

$app->post('/auth/login', function (Request $request, Response $response) {
	$authCtrl = new AuthCtrl();
	$json = $request->getBody();
	$payload = json_decode($json, true);
	$session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
	$logged = $authCtrl->login($payload['password'], $session);
	return $response->withJson(['logged' => $logged]);
});

$app->group('', function () use ($app) { //group of routes which require user to be logged in

	$app->get('/users', function (Request $request, Response $response) {
		$usersCtrl = new UsersCtrl($this->em);
		$respArray = $usersCtrl->getUsers();
		return $response->withJson($respArray);
	});

	$app->post('/users', function (Request $request, Response $response) {
		$json = $request->getBody();
		$user = json_decode($json, true);

		$usersCtrl = new UsersCtrl($this->em);
		$respArray = $usersCtrl->addUser($user);
		return $response->withJson([]);
	});

	$app->put('/users/update_ratings', function (Request $request, Response $response) {
		$json = $request->getBody();
		$usersCodes = json_decode($json, true);
		$winnerUserNid = $usersCodes['winnerUserNid'];
		$looserUserNid = $usersCodes['looserUserNid'];

		$usersCtrl = new UsersCtrl($this->em);
		$usersCtrl->updateRatings($winnerUserNid, $looserUserNid);
		return $response->withJson([]);
	});

	$app->get('/ratings_history/{userNid}', function (Request $request, Response $response, array $args) {
		$userNid = (int) $args['userNid'];
		$ratingsHistoryCtrl = new RatingsHistoryCtrl($this->em);
		$respArray = $ratingsHistoryCtrl->getRatingsHistory($userNid);
		return $response->withJson($respArray);
	});

})->add(new IsLoggedMiddleware());

$app->run();
