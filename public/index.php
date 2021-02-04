<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <style>
        * { font-family: sans-serif; }
        body { padding: 2rem; }
        .content { max-width: 40rem; margin: 0 auto; }
    </style>
    <title>PHP Routing Benchmark</title>
  </head>
  <body>
  <div class="content">
<?php

require_once __DIR__ . '/../vendor/autoload.php';

$iterations = 10;

$paths = [
    '/F6kiIkhRvFXNWg/MHzKw6DyJA09CA/n3bMz2zLya4hTB' => 'No match',
    '/languages/de/back' => 'First route (param)',
    '/offline-payments/settings' => 'First route (static)',
    '/purchases/vendors/abcdefghi/enable' => 'Middle route (param)',
    '/purchases/vendors/export' => 'Middle route (static)',
    '/signed/invoices/123456789/confirm' => 'Last route (param)',
    '/portal/logout' => 'Last route (static)',
];

$modes = [
    'FastRoute',
    'Symfony',
];

$mode = $_GET['mode'] ?? null;
$path = $_GET['path'] ?? null;

$isValidMode = in_array($mode, $modes, true);
$isValidPath = array_key_exists($path, $paths);

?>
<h1>PHP Routing Benchmark</h1>
<hr>
<?php

if (!$isValidMode || !$isValidPath) {
    ?>
    <h2>Tests:</h2>
    <ul>
        <?php foreach ($paths as $path => $name): ?>
            <li><?php echo htmlspecialchars($name); ?>
            <?php foreach ($modes as $mode): ?>
                &nbsp;&nbsp;<a href="/?<?php echo htmlspecialchars(http_build_query(['path' => $path, 'mode' => $mode])); ?>"><?php echo htmlspecialchars($mode); ?></a>
            <?php endforeach; ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
} else {
        function get_callable($mode)
        {
            $routePathDataset = require(__DIR__ . '/../routes/akaunting.php');
    
            if ($mode === 'Symfony') {
                // Initialize Symfony
                $routes = new \Symfony\Component\Routing\RouteCollection();
                $i = 0;
                foreach ($routePathDataset as $path) {
                    $route = new \Symfony\Component\Routing\Route("/$path");
                    $routes->add("route_$i", $route);
                    $i++;
                }
                $context = new \Symfony\Component\Routing\RequestContext();
                $symfonyUrlMatcher = new \Symfony\Component\Routing\Matcher\UrlMatcher($routes, $context);
                return function ($path) use ($symfonyUrlMatcher) {
                    try {
                        return $symfonyUrlMatcher->match($path);
                    } catch (\Throwable $t) {
                        return null;
                    }
                };
            } elseif ($mode === 'FastRoute') {
                $fastRouteDispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) use ($routePathDataset) {
                    $i = 0;
                    foreach ($routePathDataset as $path) {
                        $r->addRoute('GET', "/$path", "route_$i");
                        $i++;
                    }
                });
                return function ($path) use ($fastRouteDispatcher) {
                    return $fastRouteDispatcher->dispatch('GET', $path);
                };
            } else {
                return null;
            }
        }
        $callable = get_callable($mode);
        ; ?>
    
    <h2><?php echo htmlspecialchars("$paths[$path]: $mode"); ?></h2>
    
    <ol>
     
    <?php
    $result = null;
        for ($i = 1; $i <= $iterations; $i++):
        $begin = microtime(true);
        $result = $callable($path);
        $end = microtime(true);
        $millis = ($end - $begin) * 1000; ?>
        <li><?php echo htmlspecialchars(number_format($millis, 5, '.', null) . ' ms'); ?></li>
    <?php endfor; ?>
    </ol>
    <h3>Result:</h3>
    <pre><?php echo htmlspecialchars(var_export($result, true)); ?></pre>
    <?php
    }

$opcacheStatus = 'disabled';
if (function_exists('opcache_get_status')) {
    $opcacheStatus = 'loaded';
    $opcacheStatus .=  is_array(opcache_get_status()) ? ' (active)' : ' (inactive)';
}
$xdebugStatus = 'disabled';
if (function_exists('xdebug_is_debugger_active')) {
    $xdebugStatus = 'loaded';
    $xdebugStatus .= xdebug_is_debugger_active() ? ' (active)' : ' (inactive)';
}
?>
<hr>
<p>
Opcache: <?php echo htmlentities($opcacheStatus); ?><br>
XDebug: <?php echo htmlentities($xdebugStatus); ?><br>
</p>
</div>
</body>
</html>