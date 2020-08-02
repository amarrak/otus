<?php

class Metrics
{
    public static function register()
    {
        register_shutdown_function('Metrics::shutdown');
    }

    public static function shutdown()
    {
        $registry = new \Prometheus\CollectorRegistry(new \Prometheus\Storage\APC());

        $counter = $registry->getOrRegisterCounter('myapp', 'app_request_count', 'Application Request Count', ['method', 'endpoint']);
        $counter->incBy(1, [$_SERVER["REQUEST_METHOD"], $_SERVER["REQUEST_URI"]]);        

        $requestLatency = (microtime(true) - $GLOBALS["startScriptExecutionTime"]) * 1000;
        $histogram = $registry->getOrRegisterHistogram('app', 'app_request_latency_nanoseconds', 'Application Request Lathency', ['method', 'endpoint'], [50, 100, 150, 200, 250, 300, 350, 400, 450, 500, 550, 600, 650]);
        $histogram->observe($requestLatency, [$_SERVER["REQUEST_METHOD"], $_SERVER["REQUEST_URI"]]);
    }
}

