<?php
include './lib/loader.php';

$connection = new \PhpAmqpLib\Connection\AMQPStreamConnection(
	'localhost', 5672, 'guest', 'guest'
);

$channel = $connection->channel();
$channel->queue_declare('hello', false, false, false, false);

$callback = function($msg) {
	echo " [x] Received ", $msg->body, "\n";
};

$channel->basic_consume('hello', '', false, true, false, false, $callback);
while(count($channel->callbacks))
{
	$channel->wait();
}

$channel->close();
$connection->close();
