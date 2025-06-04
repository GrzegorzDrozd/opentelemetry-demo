<?php
putenv('OTEL_TRACES_EXPORTER=memory');
putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
putenv('OTEL_TRACES_SAMPLER=always_on');

use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Context\Context;

use function OpenTelemetry\Instrumentation\hook;

require_once '../symfony2/vendor/autoload.php';

$instrumentation = new CachedInstrumentation('otel.demo',null,'https://opentelemetry.io/schemas/1.30.0');

$tracker = new Tracker();

hook(
    SomeService::class,
    'testMethod',
    pre: function ($serviceInstance, array $params = []) use ($instrumentation, $tracker) {
        $builder = $instrumentation->tracer()->spanBuilder('demo')
            ->setSpanKind(SpanKind::KIND_SERVER)
            ->setAttribute('http.route', '/show/product/:id')
        ;
        $span = $builder->startSpan();
        // if we need to store something for "post" action we need to save it in external storage
        $tracker->saveAttribute(
            'some-attribute-important-only-on-success',
            $serviceInstance->getSomethig()
        );
        $parent = Context::getCurrent();
        Context::storage()->attach($span->storeInContext($parent));
    },



    
    post: function (
        $obj, array $params = [], mixed $ret = null, ?Throwable $throwable = null
    ) use ($tracker) {
        $scope = Context::storage()->scope();
        if (!$scope) {
            return;
        }
        $scope->detach();
        $span = \OpenTelemetry\API\Trace\Span::fromContext($scope->context());
        if ($throwable){
            $span->setStatus(
                \OpenTelemetry\API\Trace\StatusCode::STATUS_ERROR,
                $throwable->getMessage());
        } else {
            $span->setAttribute('some-attribute',
                $tracker->getAttribute('some-attribute-important-only-on-success'));
        }
        $span->end();
    }
);

function testFunc() {
    //do stuff
    sleep(2);
}

