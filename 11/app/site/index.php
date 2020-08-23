<?php
include './lib/loader.php';

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

$router->put("user/{id}", static function($id) use ($request, $db) {

	$headers = $request->getHeaders();

log2("put user/".$id.": ".$headers['X-Userid'].": ".print_r($headers, 1));

	if (!isset($headers['X-Userid']) || ((int)$headers['X-Userid'] !== (int)$id))
	{
		return ["401 Unauthorized"];
	}

	$user = new User($db);

	if ($user->update($id, $request->getInput()))
	{
		return ["200 OK"];
	}

	return [
		"400 Bad Request",
		["code" => $user->getLastErrorCode(), "message" => $user->getLastErrorMessage()]
	];
});

$router->delete("user/{id}", static function($id) use ($request, $db) {

	$headers = $request->getHeaders();
	if (!isset($headers['X-Userid']) || ((int)$headers['X-Userid'] !== (int)$id))
	{
		return ["401 Unauthorized"];
	}

	$user = new User($db);

	if ($user->delete($id))
	{
		return [
			"200 OK"
		];
	}

	return [
		"400 Bad Request",
		["code" => $user->getLastErrorCode(), "message" => $user->getLastErrorMessage()]
	];
});

$router->get("user/{id}", static function($id) use ($request, $db) {

	$headers = $request->getHeaders();

log2("get user/".$id.": ".$headers['X-Userid'].": ".print_r($headers, 1));

	if (!isset($headers['X-Userid']) || ((int)$headers['X-Userid'] !== (int)$id))
	{
		return ["401 Unauthorized"];
	}

	$user = new User($db);

	if ($data = $user->getById($id))
	{
		return [
			"200 OK",
			$data
		];
	}

	return [
		"404 Not Found",
		["code" => $user->getLastErrorCode(), "message" => $user->getLastErrorMessage()]
	];
});

$router->post("user", static function() use ($request, $db) {
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

log2("b... RequestMethod=".$request->getRequestMethod().", RequestUri=".$request->getRequestUri());
log2("b... "."HOSTNAME=".$_SERVER["HOSTNAME"].", ".
"REQUEST_URI=".$_SERVER["REQUEST_URI"].", ".
"HTTP_X_AUTH_REQUEST_REDIRECT=".$_SERVER["HTTP_X_AUTH_REQUEST_REDIRECT"].", ".
"HTTP_X_ORIGINAL_URL=".$_SERVER["HTTP_X_ORIGINAL_URL"].", ".
"HTTP_X_ORIGINAL_METHOD=".$_SERVER["HTTP_X_ORIGINAL_METHOD"].", ".
"HTTP_HOST=".$_SERVER["HTTP_HOST"].", ".
"REQUEST_METHOD=".$_SERVER["REQUEST_METHOD"]);

list($status, $data, $headers) = $router->dispatch($request->getRequestMethod(), $request->getRequestUri());

$response->render($status, $data, $headers);
