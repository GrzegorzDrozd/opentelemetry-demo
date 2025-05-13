<?php
putenv('OTEL_TRACES_EXPORTER=memory');
putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
putenv('OTEL_TRACES_SAMPLER=always_on');

use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Context\Context;
use OpenTelemetry\SDK\Common\Export\InMemoryStorageManager;

require_once '../symfony2/vendor/autoload.php';

$instrumentation = new CachedInstrumentation(
    'otel.demo',
    null,
    'https://opentelemetry.io/schemas/1.30.0',
);

$builder = $instrumentation->tracer()->spanBuilder('demo')
    ->setSpanKind(SpanKind::KIND_SERVER)
    ->setAttribute('http.router', '/show/product/:id')
;
$span = $builder->startSpan();
$parent = Context::getCurrent();
Context::storage()->attach($span->storeInContext($parent));

//do stuff
sleep(2);

$span->end();

// debug only
$provider = \OpenTelemetry\API\Globals::tracerProvider();
if ($provider instanceof \OpenTelemetry\SDK\Trace\TracerProvider) {
    $provider->shutdown();
}
var_dump(InMemoryStorageManager::spans()->getArrayCopy()[0]);

