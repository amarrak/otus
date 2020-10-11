<?php
include './lib/loader.php';

use Kernel as core;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$db = new core\Database($_ENV["DB_HOST"], $_ENV["DB_NAME"], $_ENV["DB_USER"], $_ENV["DB_PWD"]);

$connection = new AMQPStreamConnection('rab-rabbitmq.rabbit.svc.cluster.local', 5672, 'user', 'MqWRoyxCU4');
$channel = $connection->channel();

$channel->queue_declare('VacancyApproved', false, false, false, false);

$callback = function (AMQPMessage $msg) use ($db)
{
	$pm = new ProcessedMessage($db);
	$pm->add(['message_id' => $msg->body]);

	$resultFields = json_decode($msg->body, true);

	$vacancy = new Vacancy($db);
	$vacancy->update($resultFields["id"], ["active" => $resultFields["success"]]);
};

$channel->basic_consume('VacancyApproved', '', false, true, false, false, $callback);

while ($channel->is_consuming())
{
	$channel->wait();
}
