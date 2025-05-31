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
$meter = \OpenTelemetry\API\Globals::meterProvider()->getMeter('demo');
hook(null,'callExternalApiExample',
    pre: function () use ($meter, $tracker) {
        $context = \OpenTelemetry\Context\Context::getCurrent();
        $meter->createCounter('demo.counter')->add(1, context: $context);
        $meter->createUpDownCounter('demo.upDownCounter')->add(1, context: $context);
        $tracker->start(1/*some id, for example, from params*/);
    },
    post: function () use ($meter,$tracker) {
        $context = \OpenTelemetry\Context\Context::getCurrent();
        $duration = $tracker->duration(1/*some id, for example, from params*/);
        $meter->createHistogram('demo.histogram', 'ms'/*milliseconds*/)->record($duration, context: $context);
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