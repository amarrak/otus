<?php
include './lib/loader.php';

session_start();

$request = new Request();
$response = new Response($request);

$db = new Database($_ENV["DB_HOST"], $_ENV["DB_NAME"], $_ENV["DB_USER"], $_ENV["DB_PWD"]);

$router = new Router();

function log2($data)
{
	global $db;

	$sql = 'INSERT INTO ttt(mytext) VALUES(:mytext)';
	$subValues[":mytext"] = $data;

	$conn = $db->getConnection();
	$statement = $conn->prepare($sql);
	$statement->execute($subValues);
}

$router->post("login", static function() use ($request, $db) {

log2("login!");

	$requestData = $request->getInput();

	$login = $requestData['login'];
	$password = $requestData['password'];

log2("login: ".$login.":".$password);

	$user = new User($db);

	if ($data = $user->getByCredentials($login, $password))
	{
log2("login: id=".$data["id"]);

		$_SESSION["USER_INFO"] = $data;
		return ["200 OK"];
	}

log2("login: 401!!!");

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
log2("auth: ".$_SESSION["USER_INFO"]['id'].":".$_SESSION["USER_INFO"]['login']);
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

log2("auth: 401!!!");
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

log2("a... RequestMethod=".$request->getRequestMethod().", RequestUri=".$request->getRequestUri());
log2("a... "."HOSTNAME=".$_SERVER["HOSTNAME"].", ".
"REQUEST_URI=".$_SERVER["REQUEST_URI"].", ".
"HTTP_X_AUTH_REQUEST_REDIRECT=".$_SERVER["HTTP_X_AUTH_REQUEST_REDIRECT"].", ".
"HTTP_X_ORIGINAL_URL=".$_SERVER["HTTP_X_ORIGINAL_URL"].", ".
"HTTP_X_ORIGINAL_METHOD=".$_SERVER["HTTP_X_ORIGINAL_METHOD"].", ".
"HTTP_HOST=".$_SERVER["HTTP_HOST"].", ".
"REQUEST_METHOD=".$_SERVER["REQUEST_METHOD"]);

list($status, $data, $headers) = $router->dispatch($request->getRequestMethod(), $request->getRequestUri());

$response->render($status, $data, $headers);
