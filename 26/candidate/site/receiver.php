<?php
include './lib/loader.php';

use Kernel as core;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$db = new core\Database($_ENV["DB_HOST"], $_ENV["DB_NAME"], $_ENV["DB_USER"], $_ENV["DB_PWD"]);

$connection = new AMQPStreamConnection('rab-rabbitmq.rabbit.svc.cluster.local', 5672, 'user', 'MqWRoyxCU4');
$channel = $connection->channel();

$channel->queue_declare('VacancyCreated', false, false, false, false);

$callback = function (AMQPMessage $msg) use ($db)
{
	$vacancyFields = json_decode($msg->body, true);

	$vacancy = new Vacancy($db);
	if ($vacancy->add($vacancyFields))
	{
		$result = ["id" => $vacancyFields["id"], "success" => "Y"];
	}
	else
	{
		$result = ["id" => $vacancyFields["id"], "success" => "N"];
	}

	$pm = new ProcessedMessage($db);
	$pm->add(['message_id' => print_r($result, 1)]);

	$connection = new AMQPStreamConnection('rab-rabbitmq.rabbit.svc.cluster.local', 5672, 'user', 'MqWRoyxCU4');
	$channel = $connection->channel();

	$channel->queue_declare('VacancyApproved', false, false, false, false);

	$msg = new AMQPMessage(json_encode($result));
	$channel->basic_publish($msg, '', 'VacancyApproved');

	$channel->close();
	$connection->close();
};

$channel->basic_consume('VacancyCreated', '', false, true, false, false, $callback);

//$processedMessage = new ProcessedMessage($db);
//$processedMessage->add(['message_id' => "start"]);

while ($channel->is_consuming())
{
	$channel->wait();
}
