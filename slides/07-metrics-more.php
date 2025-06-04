<?php
/** @noinspection AutoloadingIssuesInspection */

use function OpenTelemetry\Instrumentation\hook;

require_once '../symfony1/vendor/autoload.php';

function callExternalApiExample(): void
{
    print 1;
    sleep(rand(1,5));
    // some long-running operation: query, external api call, file processing...
}




// part under the hood in the framework:



$tracker = new MetricsTracker();
hook(null,'callExternalApiExample',
    pre: function ($objectInstance, array $params) use ($tracker) {
        $meter = \OpenTelemetry\API\Globals::meterProvider()->getMeter('demo');
        $context = \OpenTelemetry\Context\Context::getCurrent();
        $meter->createCounter('demo.counter')->add(1, context: $context);
        $meter->createUpDownCounter('demo.upDownCounter')->add(1, context: $context);
        $tracker->start(md5(json_encode($params, JSON_THROW_ON_ERROR)));
    },
    post: function ($objectInstance, array $params) use ($tracker) {
        $meter = \OpenTelemetry\API\Globals::meterProvider()->getMeter('demo');
        $context = \OpenTelemetry\Context\Context::getCurrent();
        $duration = $tracker->duration(md5(json_encode($params, JSON_THROW_ON_ERROR)));
        $meter->createHistogram('demo.histogram', 'ms'/*milliseconds*/)->record(
            $duration,
            context: $context
        );
        $meter->createUpDownCounter('demo.upDownCounter')->add(-1, context: $context);
    }
);

callExternalApiExample();



class MetricsTracker
{
    public function __construct(
        protected array $timers = []
    ) {
    }

    public function start($id): void
    {
        $this->timers[$id] = microtime(true);
    }

    public function duration($id): float
    {
        return (microtime(true) - $this->timers[$id])*1000;
    }
}