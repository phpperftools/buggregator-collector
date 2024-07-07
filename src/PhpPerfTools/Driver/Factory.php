<?php

namespace PhpPerfTools\Buggregator\Driver;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class Factory
{
    /**
     * Retrieves an instance of the specified driver with the given container and parameters.
     *
     * @codeCoverageIgnore This is just a proxy to factory above. Also during testing we might not have container
     * @param ContainerInterface $container The container to retrieve the dependencies from.
     * @param string $name The name of the driver to retrieve.
     * @param array $params Additional parameters to be passed to the driver.
     * @return mixed The instance of the driver specified by the name.
     * @throws InvalidArgumentException If the driver name is unknown.
     */
    public static function getWithContainer(ContainerInterface $container, string $name, array $params = [])
    {
        switch ($name) {
            case Buggregator::class:
                $params['client'] = $container->get(ClientInterface::class);
                $params['requestFactory'] = $container->get(RequestFactoryInterface::class);
                $params['streamFactory'] = $container->get(StreamFactoryInterface::class);

                return self::get($name, $params);
            default:
                throw new InvalidArgumentException("Unknown driver name '{$name}'");
        }
    }

    /**
     * Retrieves an object based on its name and parameters.
     *
     * @param string $name The name of the object to retrieve.
     * @param array $params An array of parameters to pass to the object's constructor. Default is an empty array.
     * @return object The retrieved object, or null if the object name is not recognized.
     */
    public static function get(string $name, array $params = [])
    {
        switch ($name) {
            case Buggregator::class:
                return new Buggregator(
                    $params['client'],
                    $params['requestFactory'],
                    $params['streamFactory'],
                    !empty($params['host']) ? $params['host'] : null,
                    !empty($params['path']) ? $params['path'] : null,
                    !empty($params['appName']) ? $params['appName'] : null,
                    !empty($params['tags']) ? $params['tags'] : null,
                    !empty($params['schema']) ? $params['schema'] : null
                );
            default:
                throw new InvalidArgumentException("Unknown driver name '{$name}'");
        }
    }
}
