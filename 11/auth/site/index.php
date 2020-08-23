<?php
include './lib/loader.php';

session_start();

$request = new Request();
$response = new Response($request);

$db = new Database($_ENV["DB_HOST"], $_ENV["DB_NAME"], $_ENV["DB_USER"], $_ENV["DB_PWD"]);

$router = new Router();

$router->post("login", static function() use ($request, $db) {

	$requestData = $request->getInput();

	$login = $requestData['login'];
	$password = $requestData['password'];

	$user = new User($db);

	if ($data = $user->getByCredentials($login, $password))
	{
		$_SESSION["USER_INFO"] = $data;
		return ["200 OK"];
	}

	return ["401 Unauthorized"];
}
);

$router->get("login", static function() use ($request, $db) {

	$login = $_REQUEST['login'];
	$password = $_REQUEST['password'];

	$user = new User($db);

	if ($data = $user->getByCredentials($login, $password))
	{
		$_SESSION["USER_INFO"] = $data;
		return ["200 OK"];
	}

	return ["401 Unauthorized"];
}
);

$router->get("auth", static function() use ($request) {

	if (isset($_SESSION["USER_INFO"]))
	{
		return [
			"200 OK",
			[],
			[
				'X-UserId' => $_SESSION["USER_INFO"]['id'],
				'X-User' => $_SESSION["USER_INFO"]['login'],
				'X-Email' => $_SESSION["USER_INFO"]['email'],
				'X-First-Name' => $_SESSION["USER_INFO"]['firstName'],
				'X-Last-Name' => $_SESSION["USER_INFO"]['lastName'],
				'X-Phone' => $_SESSION["USER_INFO"]['phone'],
			]
		];
	}

	return ["401 Unauthorized"];
}
);

$router->post("register", static function() use ($request, $db) {

	$user = new User($db);

	if ($userId = $user->add($request->getInput()))
	{
		return [
			"200 OK",
			["id" => $userId]
		];
	}

	return [
		"400 Bad Request",
		["code" => $user->getLastErrorCode(), "message" => $user->getLastErrorMessage()]
	];
}
);

$router->get("signin", static function() use ($request) {
	return [
		"200 OK",
		["status" => "Pleace, login"]
	];
}
);

$router->get("logout", static function() use ($request) {

	unset($_SESSION["USER_INFO"]);

	return ["401 Unauthorized"];
}
);

$router->get("health", static function() {
	return [
		"200 OK",
		["status" => "OK"]
	];
}
);

$router->get("info", static function() {
	phpinfo();
	return [
		"200 OK"
	];
}
);

$router->get("", static function() {
	return [
		"200 OK",
		["status" => "OK"]
	];
}
);

list($status, $data, $headers) = $router->dispatch($request->getRequestMethod(), $request->getRequestUri());

$response->render($status, $data, $headers);
