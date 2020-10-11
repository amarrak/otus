<?php
include './lib/loader.php';

use Kernel as core;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$request = new core\Request();
$response = new core\Response($request);

$db = new core\Database($_ENV["DB_HOST"], $_ENV["DB_NAME"], $_ENV["DB_USER"], $_ENV["DB_PWD"]);

$router = new core\Router();

$router->post("create", static function() use ($request, $db) {

	$vacancy = new Vacancy($db);

	if ($vacancyId = $vacancy->add($request->getInput()))
	{
		$connection = new AMQPStreamConnection('rab-rabbitmq.rabbit.svc.', 5672, 'user', 'MqWRoyxCU4');
		$channel = $connection->channel();

		$channel->queue_declare('VacancyCreated', false, false, false, false);

		$msg = new AMQPMessage($vacancyId, $request->getInput());
		$channel->basic_publish($msg, '', 'VacancyCreated');

		$channel->close();
		$connection->close();

		return [
			"200 OK",
			["id" => $vacancyId]
		];
	}

	return [
		"400 Bad Request",
		["code" => $vacancy->getLastErrorCode(), "message" => $vacancy->getLastErrorMessage()]
	];
}
);

$router->put("vacancy/{id}", static function($id) use ($request, $db) {

	$vacancy = new Vacancy($db);

	if ($vacancyId = $vacancy->update($id, $request->getInput()))
	{
		return [
			"200 OK",
			["id" => $vacancyId]
		];
	}

	return [
		"400 Bad Request",
		["code" => $vacancy->getLastErrorCode(), "message" => $vacancy->getLastErrorMessage()]
	];
}
);

$router->delete("vacancy/{id}", static function($id) use ($request, $db) {

	$vacancy = new Vacancy($db);

	if ($vacancy->delete($id))
	{
		return [
			"200 OK"
		];
	}

	return [
		"400 Bad Request",
		["code" => $vacancy->getLastErrorCode(), "message" => $vacancy->getLastErrorMessage()]
	];
}
);

$router->get("vacancy/{id}", static function($id) use ($db) {

	$vacancy = new Vacancy($db);

	if ($data = $vacancy->getById($id))
	{
		return [
			"200 OK",
			$data["balance"]
		];
	}

	return [
		"404 Not Found",
		["code" => $vacancy->getLastErrorCode(), "message" => $vacancy->getLastErrorMessage()]
	];
});


$router->post("close/{id}", static function($id) use ($request, $db) {

	$vacancy = new Vacancy($db);

	if ($vacancyId = $vacancy->update($id, ["active" => "N"]))
	{
		return [
			"200 OK",
			["id" => $vacancyId]
		];
	}

	return [
		"400 Bad Request",
		["code" => $vacancy->getLastErrorCode(), "message" => $vacancy->getLastErrorMessage()]
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
