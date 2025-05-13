<?php /** @noinspection AutoloadingIssuesInspection */

putenv('OTEL_EXPORTER_OTLP_ENDPOINT=http://localhost:4317');
putenv('OTEL_EXPORTER_OTLP_PROTOCOL=grpc');
putenv('OTEL_METRICS_EXPORTER=otlp');
putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
putenv('OTEL_TRACES_SAMPLER=always_on');
putenv('OTEL_SERVICE_NAME=symfony2');
putenv('OTEL_PHP_DISABLED_INSTRUMENTATIONS=psr18');

use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\Propagation\ArrayAccessGetterSetter;
use OpenTelemetry\Context\Propagation\PropagationSetterInterface;

use function OpenTelemetry\Instrumentation\hook;

require_once '../symfony2/vendor/autoload.php';

$instrumentation = new CachedInstrumentation(
    'otel.demo',
    null,
    'https://opentelemetry.io/schemas/1.30.0',
);

$builder = $instrumentation->tracer()->spanBuilder('demo')
    ->setSpanKind(SpanKind::KIND_SERVER)
    ->setAttribute('http.router', '/show/product/:id');
$span = $builder->startSpan();
$parent = Context::getCurrent();
Context::storage()->attach($span->storeInContext($parent));

class HeaderPropagator implements PropagationSetterInterface
{

    /**
     * @param \Nyholm\Psr7\Request $carrier
     * @param string $key
     * @param string $value
     * @return void
     */
    public function set(&$carrier, string $key, string $value): void
    {
        $carrier = $carrier->withAddedHeader($key, $value);
    }
}

hook(
    \Symfony\Contracts\HttpClient\HttpClientInterface::class,
    'request',
    pre: static function (
        \Symfony\Contracts\HttpClient\HttpClientInterface $client,
        array $params,
        string $class,
        string $function,
        ?string $filename,
        ?int $lineno
    ) use ($instrumentation): ?array {
        $parent = Context::getCurrent();

        $span = $instrumentation
            ->tracer()
            ->spanBuilder(\sprintf('%s', $params[0]))
            ->setSpanKind(SpanKind::KIND_CLIENT)
            ->setParent($parent)
            ->startSpan();

        $requestOptions = $params[2] ?? [];
        if (!isset($requestOptions['headers'])) {
            $requestOptions['headers'] = [];
        }
        $propagator = Globals::propagator();
        $context = $span->storeInContext($parent);
        $propagator->inject($requestOptions['headers'], ArrayAccessGetterSetter::getInstance(), $context);

        Context::storage()->attach($context);

        $params[2] = $requestOptions;

        return $params;
    }
);

//do stuff

$client = new \Symfony\Component\HttpClient\CurlHttpClient();
$response = $client->request('GET', 'http://127.0.0.1:9992');
$span->end();


//$provider = \OpenTelemetry\API\Globals::tracerProvider();
//if ($provider instanceof \OpenTelemetry\SDK\Trace\TracerProvider) {
//    $provider->shutdown();
//}
//var_dump(InMemoryStorageManager::spans()->getArrayCopy()[0]);

