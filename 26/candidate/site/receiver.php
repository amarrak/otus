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
//	$processedMessage = new ProcessedMessage($db);
//	$processedMessage->add(['message_id' => "get"]);
//	$processedMessage->add(['message_id' => $msg->body]);

	$vacancyFields = json_decode($msg->body, true);

//	$processedMessage->add(['message_id' => print_r($vacancyFields, 1)]);

	$vacancy = new Vacancy($db);
	$vacancy->add($vacancyFields);
};

$channel->basic_consume('VacancyCreated', '', false, true, false, false, $callback);

//$processedMessage = new ProcessedMessage($db);
//$processedMessage->add(['message_id' => "start"]);

while ($channel->is_consuming())
{
	$channel->wait();
}
