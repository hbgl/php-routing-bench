<?php

require_once __DIR__ . '/vendor/autoload.php';

$routePaths = [
    'akaunting' => require(__DIR__ . '/routes/akaunting.php'),
];

/**
 * @BeforeMethods({"init"})
 */
class RouterBench
{
    private $key;
    private $fastRouteDispatcher;
    private $symfonyUrlMatcher;

    public function init($args)
    {
        $key = $args[0];

        if ($key !== $this->key) {
            $this->key = $key;
        
            global $routePaths;
            $routePathDataset = $routePaths[$key];

            // Initialize FastRoute
            $this->fastRouteDispatcher = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) use ($routePathDataset) {
                $i = 0;
                foreach ($routePathDataset as $path) {
                    $r->addRoute('GET', "/$path", "r$i");
                    $i++;
                }
            });
        
            // Initialize Symfony
            $routes = new \Symfony\Component\Routing\RouteCollection();
            $i = 0;
            foreach ($routePathDataset as $path) {
                $route = new \Symfony\Component\Routing\Route("/$path");
                $routes->add("r$i", $route);
                $i++;
            }
            $context = new \Symfony\Component\Routing\RequestContext();
            $this->symfonyUrlMatcher = new \Symfony\Component\Routing\Matcher\UrlMatcher($routes, $context);
        }
    }

    /**
     * @ParamProviders({"dataProvider"})
     */
    public function benchFastRoute($args)
    {
        $uri = $args[1];
        $result = $this->fastRouteDispatcher->dispatch('GET', $uri);
        return $result;
    }

    /**
     * @ParamProviders({"dataProvider"})
     */
    public function benchSymfony($args)
    {
        $uri = $args[1];
        try {
            $result = $this->symfonyUrlMatcher->match($uri);
            return $result;
        } catch (\Throwable $t) {
            return null;
        }
    }

    public function dataProvider()
    {
        return [
            'akaunting no match' => ['akaunting', '/F6kiIkhRvFXNWg/MHzKw6DyJA09CA/n3bMz2zLya4hTB'],
            'akaunting first param' => ['akaunting', '/languages/de/back'],
            'akaunting first static' => ['akaunting', '/offline-payments/settings'],
            'akaunting middle param' => ['akaunting', '/purchases/vendors/abcdefghi/enable'],
            'akaunting middle static' => ['akaunting', '/purchases/vendors/export'],
            'akaunting last param' => ['akaunting', '/signed/invoices/123456789/confirm'],
            'akaunting last static' => ['akaunting', '/portal/logout'],
        ];
    }
}
