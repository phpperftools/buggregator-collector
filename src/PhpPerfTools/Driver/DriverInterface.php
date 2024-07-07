<?php

namespace PhpPerfTools\Buggregator\Driver;

interface DriverInterface
{
    public function persists($stuff);

    public function setAppName(string $appName);

    public function setTags(array $tags);
}
