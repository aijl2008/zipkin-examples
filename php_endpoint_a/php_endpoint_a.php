<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../functions.php';
echo '<pre>' . PHP_EOL;
echo "[" . posix_getpid() . "]" . basename(__FILE__) . ' started' . PHP_EOL;
echo "[" . posix_getpid() . "]" . "收到的header" . PHP_EOL;
foreach ($_SERVER as $name => $value) {
    if (strpos($name, 'HTTP') === 0) {
        echo "[" . posix_getpid() . "]" . $name . ":" . $value . PHP_EOL;
    }
}

$tracing = create_tracing(basename(__FILE__), '127.0.0.1');
$tracer = $tracing->getTracer();
$defaultSamplingFlags = \Zipkin\Propagation\DefaultSamplingFlags::createAsSampled();

/**
 * 创建主span
 */
$span = $tracer->newTrace($defaultSamplingFlags);
$span->start(\Zipkin\Timestamp\now());
$span->setName(basename(__FILE__) . ':' . __LINE__);
$span->setKind(\Zipkin\Kind\SERVER);

usleep(100 * mt_rand(1, 3));

/**
 * 注入header
 */
$headers = [];
$injector = $tracing->getPropagation()->getInjector(new \Zipkin\Propagation\Map());
$injector($span->getContext(), $headers);

/**
 * 请求另一个页面，同时发送第二个span的header
 */
$httpClient = new \GuzzleHttp\Client();
$nextUrl = $_GET["nextUrl"] ?: 'http://localhost:8012';
echo "[" . posix_getpid() . "]TraceId:" . $span->getContext()->getTraceId() . PHP_EOL;
echo "[" . posix_getpid() . "]ParentId:" . $span->getContext()->getParentId() . PHP_EOL;
echo "[" . posix_getpid() . "]SpanId:" . $span->getContext()->getSpanId() . PHP_EOL;
echo "[" . posix_getpid() . "]" . "请求'{$nextUrl}'" . PHP_EOL;

$request = new \GuzzleHttp\Psr7\Request('GET', $nextUrl, $headers);
$span->annotate('request_started', \Zipkin\Timestamp\now());
$response = $httpClient->send($request);
echo $response->getBody()->getContents() . PHP_EOL;
$span->annotate('request_finished', \Zipkin\Timestamp\now());

$span->finish();

echo "[" . posix_getpid() . "]" . basename(__FILE__) . ' finished' . PHP_EOL;
/* Sends the trace to zipkin once the response is served */
echo '</pre>' . PHP_EOL;
register_shutdown_function(function () use ($tracer) {
    $tracer->flush();
});
