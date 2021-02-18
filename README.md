# PHP routing library benchmarks

This repo contains benchmarks for two PHP routing libraries:

- [Symfony Routing](https://symfony.com/doc/current/routing.html) both compiled and dynamic mode
- [FastRoute](https://github.com/nikic/FastRoute) in group count mode and mark mode

[Symfony Routing](https://symfony.com/doc/current/routing.html) in dynamic mode sequentially matches each route pattern against a URI. In compiled mode it matches static routes via dictionary lookup and dynamic routes with one giant pre-compiled regex.

[FastRoute](https://github.com/nikic/FastRoute) in group count mode matches batches of 10 to 30 route patterns against a URI. In mark mode it basically works like Symfony's compiled mode.

## Running the bechmarks

```
composer run bench
```

This runs 10 iterations of the benchmarks after a warmup. The URLs are tested against around [300 routes](https://github.com/hbgl/php-routing-bench/blob/main/routes/akaunting.php) which I exported from the open source application [akaunting](https://github.com/akaunting/akaunting). I believe that the set of routes is representative for an medium sized application.

The following benchmarks are performed:

- URL that matches one of the first routes (with and without parameter)
- URL that matches a route in the middle (with and without parameter)
- URL that matches one of the last routes (with and without parameter)
- URL that does not match any route

## Testing from the browser

The individual benchmarks can be run from the browser by serving `public/index.php`.

```
cd public
php -S localhost:8000
```
