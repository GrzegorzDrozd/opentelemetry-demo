<?php

use function OpenTelemetry\Instrumentation\hook;

require_once '../symfony1/vendor/autoload.php';

// alternative to \OpenTelemetry\API\Globals::meterProvider()
// we need to create it manually because we need a `reader` instance
$factory = new \OpenTelemetry\Contrib\Otlp\MetricExporterFactory();
$reader = new \OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader(
    $factory->create()
);
$meterProvider = \OpenTelemetry\SDK\Metrics\MeterProvider::builder()
    ->addReader($reader)
    ->build();

$meter = $meterProvider->getMeter('demo');

// defined somewhere in the system, for example, in boostrap
$observableCounter = $meter->createObservableCounter('demo.observableCounter', '1'/*unit of 1*/);
$observableCounter->observe(static function (\OpenTelemetry\API\Metrics\ObserverInterface $observer) {
    // get the number of messages in the queue, cpu usage, files generated, etc.
    // "state of the system"
    $observer->observe(10);
    // one note: there is no context option, you can set attributes on observe() call but this value is not
    // related to "current" request
});

// for example, in swoole/frankenphp/RoadRunner/etc after each request or in Symfony Messenger after the message event:
$reader->collect();

