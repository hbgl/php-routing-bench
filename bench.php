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
    const SYMFONY_COMPILED_ROUTES_PATH = __DIR__ . '/.cache/symfony-compiled-routes.php';

    private $key;
    private $fastRouteDispatcherGroupCount;
    private $fastRouteDispatcherMark;
    private $symfonyUrlMatcherDynamic;
    private $symfonyUrlMatcherCompiled;

    public function init($args)
    {
        $key = $args[0];

        if ($key !== $this->key) {
            $this->key = $key;
            $this->initFastRouteGroupCount();
            $this->initFastRouteMark();
            $this->initSymfonyDynamic();
            $this->initSymfonyCompiled();
        }
    }

    private function initFastRouteGroupCount()
    {
        global $routePaths;
        $routePathDataset = $routePaths[$this->key];

        // Initialize FastRoute
        $this->fastRouteDispatcherGroupCount = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) use ($routePathDataset) {
            $i = 0;
            foreach ($routePathDataset as $path) {
                $r->addRoute('GET', "/$path", "r$i");
                $i++;
            }
        });
    }

    private function initFastRouteMark()
    {
        global $routePaths;
        $routePathDataset = $routePaths[$this->key];

        // Initialize FastRoute
        $this->fastRouteDispatcherMark = \FastRoute\simpleDispatcher(function (\FastRoute\RouteCollector $r) use ($routePathDataset) {
            $i = 0;
            foreach ($routePathDataset as $path) {
                $r->addRoute('GET', "/$path", "r$i");
                $i++;
            }
        }, [
            'routeParser' => \FastRoute\RouteParser\Std::class,
            'dataGenerator' => \FastRoute\DataGenerator\MarkBased::class,
            'dispatcher' => \FastRoute\Dispatcher\MarkBased::class,
            'routeCollector' => \FastRoute\RouteCollector::class,
        ]);
    }

    private function initSymfonyDynamic()
    {
        global $routePaths;
        $routePathDataset = $routePaths[$this->key];

        // Initialize Symfony
        $routes = new \Symfony\Component\Routing\RouteCollection();
        $i = 0;
        foreach ($routePathDataset as $path) {
            $route = new \Symfony\Component\Routing\Route("/$path");
            $routes->add("r$i", $route);
            $i++;
        }
        $context = new \Symfony\Component\Routing\RequestContext();
        $this->symfonyUrlMatcherDynamic = new \Symfony\Component\Routing\Matcher\UrlMatcher($routes, $context);
    }

    private function initSymfonyCompiled()
    {
        global $routePaths;
        $routePathDataset = $routePaths[$this->key];
        
        // Initialize Symfony
        $routes = new \Symfony\Component\Routing\RouteCollection();
        $i = 0;
        foreach ($routePathDataset as $path) {
            $route = new \Symfony\Component\Routing\Route("/$path");
            $routes->add("r$i", $route);
            $i++;
        }
        // Compile the url matcher
        $matcherDumper = new \Symfony\Component\Routing\Matcher\Dumper\CompiledUrlMatcherDumper($routes);
        $compiledRoutesCode = $matcherDumper->dump();
        file_put_contents(self::SYMFONY_COMPILED_ROUTES_PATH, $compiledRoutesCode);

        $compiledRoutes = require self::SYMFONY_COMPILED_ROUTES_PATH;
        $context = new \Symfony\Component\Routing\RequestContext();
        $this->symfonyUrlMatcherCompiled = new \Symfony\Component\Routing\Matcher\CompiledUrlMatcher($compiledRoutes, $context);
    }

    /**
     * @ParamProviders({"dataProvider"})
     */
    public function benchFastRouteGroupCount($args)
    {
        $uri = $args[1];
        $result = $this->fastRouteDispatcherGroupCount->dispatch('GET', $uri);
        return $result;
    }

    /**
     * @ParamProviders({"dataProvider"})
     */
    public function benchFastRouteMark($args)
    {
        $uri = $args[1];
        $result = $this->fastRouteDispatcherMark->dispatch('GET', $uri);
        return $result;
    }

    /**
     * @ParamProviders({"dataProvider"})
     */
    public function benchSymfonyDynamic($args)
    {
        $uri = $args[1];
        try {
            $result = $this->symfonyUrlMatcherDynamic->match($uri);
            return $result;
        } catch (\Throwable $t) {
            return null;
        }
    }

    /**
     * @ParamProviders({"dataProvider"})
     */
    public function benchSymfonyCompiled($args)
    {
        $uri = $args[1];
        try {
            $result = $this->symfonyUrlMatcherCompiled->match($uri);
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
