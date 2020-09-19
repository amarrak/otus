<?php
include './lib/loader.php';

$request = new \Kernel\Request();
$response = new \Kernel\Response($request);

$db = new \Kernel\Database($_ENV["DB_HOST"], $_ENV["DB_NAME"], $_ENV["DB_USER"], $_ENV["DB_PWD"]);

$router = new \Kernel\Router();

$router->post("create", static function() use ($request, $db) {

	$billing = new Billing($db);

	if ($billingId = $billing->add($request->getInput()))
	{
		return [
			"200 OK",
			["id" => $billingId]
		];
	}

	return [
		"400 Bad Request",
		["code" => $billing->getLastErrorCode(), "message" => $billing->getLastErrorMessage()]
	];
}
);

$router->post("deposit/{userId}", static function($userId) use ($request, $db) {

	$billing = new Billing($db);
	$processedMessage = new ProcessedMessage($db);

	$requestData = $request->getInput();

	$db->beginTransaction();

	if (!$billing->applyTransaction($userId, $requestData['sum']))
	{
		$db->rollBack();
		return ["400 Bad Request"];
	}

	if (!$processedMessage->add(['message_id' => $requestData['message_id']]))
	{
		$db->rollBack();
		return ["200 OK"];
	}

	$db->commit();
	return ["200 OK"];
}
);

$router->post("withdraw/{userId}", static function($userId) use ($request, $db) {

	$billing = new Billing($db);
	$processedMessage = new ProcessedMessage($db);

	$requestData = $request->getInput();

	$db->beginTransaction();

	if (!$billing->applyTransaction($userId, -$requestData['sum']))
	{
		$db->rollBack();
		return ["400 Bad Request"];
	}

	if (!$processedMessage->add(['message_id' => $requestData['message_id']]))
	{
		$db->rollBack();
		return ["200 OK"];
	}

	$db->commit();
	return ["200 OK"];
}
);

$router->get("balance/{userId}", static function($userId) use ($request, $db) {

	$billing = new Billing($db);

	if ($data = $billing->getByUserId($userId))
	{
		return [
			"200 OK",
			$data["balance"]
		];
	}

	return [
		"404 Not Found",
		["code" => $billing->getLastErrorCode(), "message" => $billing->getLastErrorMessage()]
	];
});

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
