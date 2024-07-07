<?php

namespace PhpPerfTools\Buggregator;

use PhpPerfTools\Buggregator\Driver\DriverInterface;

class Collector
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var string
     */
    protected $collectorPrefix = 'xhprof_';

    /**
     * @var array
     */
    protected $tags = [];

    /**
     * @var bool
     */
    protected $registerShutdownSubmit = false;

    /**
     * @var bool
     */
    protected $autoStart = false;

    /**
     * @var int
     */
    protected $flags = XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY;

    /**
     * @var array
     */
    protected $ignoredFunctions = [];

    /**
     * @var DriverInterface
     */
    protected $driver;

    /**
     * @var string
     */
    protected $appName;

    /**
     * @param string $appName
     * @param DriverInterface $driver
     * @param array $tags
     * @param array $ignoredFunctions
     * @param int $flags
     * @param bool $autoStart
     * @param bool $registerShutdownSubmit
     * @param string $collectorPrefix
     */
    public function __construct(
        string $appName,
        DriverInterface $driver,
        array $tags = [],
        array $ignoredFunctions = [],
        int $flags = XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY,
        bool $autoStart = false,
        bool $registerShutdownSubmit = false,
        string $collectorPrefix = 'xhprof_'
    ) {
        $this->appName = $appName;
        $this->driver = $driver;
        $this->ignoredFunctions = $ignoredFunctions;
        $this->flags = $flags;
        $this->autoStart = $autoStart;
        $this->registerShutdownSubmit = $registerShutdownSubmit;
        $this->tags = $tags;
        $this->collectorPrefix = trim($collectorPrefix, '_') . '_';

        $this->ignoredFunctions[] = $this->collectorPrefix . '_disable';
        $this->ignoredFunctions[] = $this->collectorPrefix . '_sample_disable';
        $this->ignoredFunctions[] = __CLASS__ . '::endProfile';
        $this->ignoredFunctions[] = __CLASS__ . '::endSample';
        $this->options['ignored_functions'] = $this->ignoredFunctions;

        if ($this->autoStart) {
            $this->startProfile();
        }

        if ($this->registerShutdownSubmit) {
            register_shutdown_function([$this, 'submit']);
        }

        $this->driver->setAppName($this->appName);
        $this->driver->setTags($this->tags);
    }

    /**
     * @return void
     */
    public function startProfile()
    {
        call_user_func($this->collectorPrefix . 'enable', $this->flags, $this->options);
    }

    /**
     * @param string $appName
     * @param DriverInterface $driver
     * @param array $tags
     * @param array $ignoredFunctions
     * @param int $flags
     * @param string $collectorPrefix
     * @return self
     */
    public static function start(
        string $appName,
        DriverInterface $driver,
        array $tags = [],
        array $ignoredFunctions = [],
        int $flags = XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY,
        string $collectorPrefix = 'xhprof_'
    ): self {
        return new self(
            $appName,
            $driver,
            $tags,
            $ignoredFunctions,
            $flags,
            true,
            false,
            $collectorPrefix
        );
    }

    /**
     * @param string $appName
     * @param DriverInterface $driver
     * @param array $tags
     * @param array $ignoredFunctions
     * @param int $flags
     * @param string $collectorPrefix
     * @return self
     */
    public static function startAndRegisterShutdown(
        string $appName,
        DriverInterface $driver,
        array $tags = [],
        array $ignoredFunctions = [],
        int $flags = XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY,
        string $collectorPrefix = 'xhprof_'
    ): self {
        return new self(
            $appName,
            $driver,
            $tags,
            $ignoredFunctions,
            $flags,
            true,
            true,
            $collectorPrefix
        );
    }

    /**
     * @param string $appName
     * @param DriverInterface $driver
     * @param array $ignoredFunction s
     * @param int $flags
     * @param array $tags
     * @param string $collectorPrefix
     * @return self
     */
    public static function startedAndRegisterShutdown(
        string $appName,
        DriverInterface $driver,
        array $tags = [],
        array $ignoredFunctions = [],
        int $flags = XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY,
        string $collectorPrefix = 'xhprof_'
    ): self {
        return new self(
            $appName,
            $driver,
            $tags,
            $ignoredFunctions,
            $flags,
            false,
            true,
            $collectorPrefix
        );
    }

    /**
     * @return void
     */
    public function startSample()
    {
        call_user_func($this->collectorPrefix . 'sample_enable');
    }

    /**
     * @return array
     */
    public function endSample(): array
    {
        return call_user_func($this->collectorPrefix . 'sample_disable');
    }

    /**
     * @param bool $end
     * @param array $data
     * @return void
     */
    public function submit(bool $end = true, array $data = [])
    {
        if ($end) {
            $data = array_merge($data, $this->endProfile());
        }

        $this->driver->persists($data);
    }

    /**
     * @return array
     */
    public function endProfile(): array
    {
        return call_user_func($this->collectorPrefix . 'disable');
    }
}
