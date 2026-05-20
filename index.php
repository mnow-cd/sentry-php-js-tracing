<?php

use OpenTelemetry\SDK\Propagation\PropagatorFactory;
use Sentry\Client;
use Sentry\Util\Http;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require_once __DIR__ . '/vendor/autoload.php';

$dsn = \Sentry\Dsn::createFromString(getenv('SENTRY_DSN'));
$endpoint = $dsn->getOtlpTracesEndpointUrl();
$headers['X-Sentry-Auth'] = Http::getSentryAuthHeader($dsn, Client::SDK_IDENTIFIER, Client::SDK_VERSION);

$transport = (new \OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory())->create(
    $endpoint,
    \OpenTelemetry\Contrib\Otlp\ContentTypes::PROTOBUF,
    $headers
);
$spanExporter = new \OpenTelemetry\Contrib\Otlp\SpanExporter($transport);
$batchSpanProcessor = new \OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor(
    $spanExporter,
    \OpenTelemetry\API\Common\Time\Clock::getDefault()
);

(new \OpenTelemetry\SDK\SdkBuilder())
    ->setTracerProvider(new \OpenTelemetry\SDK\Trace\TracerProvider($batchSpanProcessor))
    ->setPropagator((new PropagatorFactory())->create())
    ->setAutoShutdown(true)
    ->buildAndRegisterGlobal();

\Sentry\init([
    'integrations' => [new \Sentry\Integration\OTLPIntegration(false)]
]);

$app = AppFactory::create();

$app->get('/', function (Request $request, Response $response) {
    $client = new GuzzleHttp\Client();
    $res = $client->request('GET', 'http://node:3000');

    $response->getBody()->write('NodeJS Response');
    $response->getBody()->write('Status: ' . $res->getStatusCode().'<br>');
    $response->getBody()->write('Body: '.$res->getBody());

    return $response;
});

$app->run();