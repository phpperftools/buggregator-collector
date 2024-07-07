# Xhprof (and compatible) buggregator profile collector #

Simple, framework-agnostic, library to collect and persist Xhprof (and compatible) profiles in [buggregator](https://buggregator.dev/).  

## Usage ##
For basic usage with guzzle, please add this to the main file:

```php
<?php
use PhpPerfTools\Buggregator\Collector;
use PhpPerfTools\Buggregator\Driver\Buggregator;

Collector::startAndRegisterShutdown(
    "app-name", 
    new Buggregator(
        'hostname', // for example, defined in docker-compose, please include port, by default :8000 
        new \GuzzleHttp\Client(),
        new \Http\Factory\Guzzle\RequestFactory(),
        new \Http\Factory\Guzzle\StreamFactory()
    ),
);
```
or when using Container in your project:

```php
<?php
use PhpPerfTools\Buggregator\Collector;
use PhpPerfTools\Buggregator\Driver\Buggregator;
use PhpPerfTools\Buggregator\Driver\Factory;

Collector::startAndRegisterShutdown(
    "app-name", 
    Factory::getWithContainer(
        $di, // container
        Buggregator::class, // class to create 
        ['hostname'=>'hostname'] // parameters used in driver constructor
    )
);
```

It will start profiling and register a shutdown function to submit profiles.
If you want to manually submit profiles use `Collector::start`, assign instance to a variable and call `$collector->submit(true);` like this:

```php
<?php
use PhpPerfTools\Buggregator\Collector;
use PhpPerfTools\Buggregator\Driver\Buggregator;

// init with static::start or just create an instance and call ->startProfile
$collector = Collector::start(
    "app-name", 
    new Buggregator(
        'hostname', // for example, defined in docker-compose, please include port, by default :8000 
        new \GuzzleHttp\Client(),
        new \Http\Factory\Guzzle\RequestFactory(),
        new \Http\Factory\Guzzle\StreamFactory()
    )
);

// your code here

$collector->submit(true);
```

## Why Buggregator ##

All libraries I found were framework-specific (either a middleware, laravel or symfony specific), but this library will work without any framework. Useful when using something different from the most popular ones. 
Options:
```php
new Buggregator(
    Psr\Http\Client\ClientInterface::class,
    Psr\Http\Message\RequestFactoryInterface::class,
    Psr\Http\Message\StreamFactoryInterface::class,
    'host',
    'path', // default: '/api/profiler/store',
    'app-name', // app name used in UI
    'tags'=> [], // list of tags
    'schema' => 'http' // By default, buggregator is used on the local dev machine, so http is enough. If you use a shared instance, you might wish to use https.
);
```

