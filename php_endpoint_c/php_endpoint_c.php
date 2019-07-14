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


$span->finish();

echo "[" . posix_getpid() . "]" . basename(__FILE__) . ' finished' . PHP_EOL;
echo '</pre>' . PHP_EOL;
/* Sends the trace to zipkin once the response is served */
register_shutdown_function(function () use ($tracer) {
    $tracer->flush();
});
