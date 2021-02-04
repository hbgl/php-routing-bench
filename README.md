# PHP routing library benchmarks

This repo contains benchmarks for two PHP routing libraries:

- [Symfony Routing](https://symfony.com/doc/current/routing.html)
- [FastRoute](https://github.com/nikic/FastRoute)

[Symfony Routing](https://symfony.com/doc/current/routing.html) sequentially matches each route pattern against a URI, whereas [FastRoute](https://github.com/nikic/FastRoute) matches batches of 10 to 30 route patterns against a URI.

## Running the bechmarks

```
composer run bench
```

This runs 10 iterations of the benchmarks without a warmup. The URLs are tested against around [300 routes](https://github.com/hbgl/php-routing-bench/blob/main/routes/akaunting.php) which I exported from the open source application [akaunting](https://github.com/akaunting/akaunting). I believe that the set of routes is representative for an medium sized application.

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