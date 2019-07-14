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

$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

$carrier = array_map(function ($header) {
    return $header[0];
}, $request->headers->all());

/* Extracts the context from the HTTP headers */
$extractor = $tracing->getPropagation()->getExtractor(new \Zipkin\Propagation\Map());
$extractedContext = $extractor($carrier);

$tracer = $tracing->getTracer();
$span = $tracer->nextSpan($extractedContext);
$span->start();
$span->setKind(Zipkin\Kind\SERVER);
$span->setName(basename(__FILE__) . ':' . __LINE__);


/**
 * 注入header
 */
$headers = [];
$injector = $tracing->getPropagation()->getInjector(new \Zipkin\Propagation\Map());
//$injector($childSpan->getContext(), $headers);
$injector($span->getContext(), $headers);
/**
 * 请求另一个页面，同时发送第二个span的header
 */
$httpClient = new \GuzzleHttp\Client();
echo "[" . posix_getpid() . "]TraceId:" . $span->getContext()->getTraceId() . PHP_EOL;
echo "[" . posix_getpid() . "]ParentId:" . $span->getContext()->getParentId() . PHP_EOL;
echo "[" . posix_getpid() . "]SpanId:" . $span->getContext()->getSpanId() . PHP_EOL;
echo "请求'http://localhost:8013'" . PHP_EOL;
$request = new \GuzzleHttp\Psr7\Request('GET', 'http://localhost:8013', $headers);
//$childSpan->annotate('request_started', \Zipkin\Timestamp\now());
$span->annotate('request_started', \Zipkin\Timestamp\now());
$response = $httpClient->send($request);
echo $response->getBody()->getContents() . PHP_EOL;
//$childSpan->annotate('request_finished', \Zipkin\Timestamp\now());
$span->annotate('request_finished', \Zipkin\Timestamp\now());

//$childSpan->finish();

$span->finish();


echo "[" . posix_getpid() . "]" . basename(__FILE__) . ' finished' . PHP_EOL;
echo '</pre>' . PHP_EOL;
/* Sends the trace to zipkin once the response is served */
register_shutdown_function(function () use ($tracer) {
    $tracer->flush();
});
