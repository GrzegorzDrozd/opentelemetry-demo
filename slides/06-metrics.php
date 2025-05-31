<?php
require_once '../symfony1/vendor/autoload.php';

function callExternalApiExample()
{
    $context = \OpenTelemetry\Context\Context::getCurrent();
    $meter = \OpenTelemetry\API\Globals::meterProvider()->getMeter('demo');
    // This always goes up
    $meter->createCounter('demo.counter')->add(1, context: $context);
    // Let's measure how many of the things are happening right now, we add 1
    $upDownCounter = $meter->createUpDownCounter('demo.upDownCounter');
    $upDownCounter->add(1, context: $context);
    $start = microtime(true);
    // some long-running operation: query, external api call, file processing...
    sleep(rand(1,5));
    $duration = microtime(true) - $start;
    // we are "done" with the processing, so we need to subtract 1
    $upDownCounter->add(-1, context: $context);
    $meter->createHistogram('demo.histogram', 'ms'/*milliseconds*/)
        ->record($duration*1000, context: $context);
}
