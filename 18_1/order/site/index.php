<?php
include './lib/loader.php';

$request = new \Kernel\Request();
$response = new \Kernel\Response($request);

$db = new \Kernel\Database($_ENV["DB_HOST"], $_ENV["DB_NAME"], $_ENV["DB_USER"], $_ENV["DB_PWD"]);

$router = new \Kernel\Router();

$router->post("order", static function() use ($request, $db) {

	$order = new Order($db);

	if ($orderId = $order->add($request->getInput()))
	{
		$connection = new \PhpAmqpLib\Connection\AMQPStreamConnection(
			'localhost', 5672, 'guest', 'guest'
		);

		$channel = $connection->channel();
		$channel->queue_declare('hello', false, false, false, false);

		$msg = new \PhpAmqpLib\Message\AMQPMessage('Hello World!');
		$channel->basic_publish($msg, '', 'hello');

		$channel->close();
		$connection->close();

		return [
			"200 OK",
			["id" => $orderId]
		];
	}

	return [
		"400 Bad Request",
		["code" => $order->getLastErrorCode(), "message" => $order->getLastErrorMessage()]
	];
}
);

$router->get("order/{id}", static function($id) use ($request, $db) {

	$order = new Order($db);

	if ($data = $order->getById($id))
	{
		return [
			"200 OK",
			$data
		];
	}

	return [
		"404 Not Found",
		["code" => $order->getLastErrorCode(), "message" => $order->getLastErrorMessage()]
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
