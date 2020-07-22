<?php
include './lib/loader.php';

$request = new Request();
$response = new Response($request);

$db = new Database("", "", "", "");

$router = new Router();

$router->put("user/{id}", static function($id) use ($request, $db) {
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

$router->delete("user/{id}", static function($id) use ($db) {
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

$router->get("user/{id}", static function($id) use ($db) {
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

	if ($user->add($request->getInput()))
	{
		return ["201 Created"];
	}

	return [
		"400 Bad Request",
		["code" => $user->getLastErrorCode(), "message" => $user->getLastErrorMessage()]
	];
}
);

list($status, $data) = $router->dispatch($request->getRequestMethod(), $request->getRequestUri());

$response->render($status, $data);
