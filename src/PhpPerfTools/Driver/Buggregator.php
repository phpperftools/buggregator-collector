<?php

namespace PhpPerfTools\Buggregator\Driver;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Throwable;

class Buggregator implements DriverInterface
{
    /**
     * @var array
     */
    protected $tags = [];

    /**
     * @var string|null
     */
    protected $appName = null;

    /**
     * @var string
     */
    protected $host;

    /**
     * @var string
     */
    protected $path = '/api/profiler/store';

    /**
     * @var StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * @var RequestFactoryInterface
     */
    protected $requestFactory;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var string
     */
    protected $schema;

    /**
     * @param ClientInterface $client
     * @param RequestFactoryInterface $requestFactory
     * @param StreamFactoryInterface $streamFactory
     * @param string $host
     * @param string $path
     * @param string|null $appName
     * @param array $tags
     * @param string $schema
     */
    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        string $host = '128.0.0.1:8000',
        string $path = '/api/profiler/store',
        string $appName = '',
        array $tags = [],
        string $schema = 'http'
    ) {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->host = trim($host, '/');
        $this->path = trim($path, '/');
        $this->schema = $schema;

        $this->setAppName($appName);
        $this->setTags($tags);
    }

    public function persists($stuff)
    {
        $profileData = [
            'app_name' => $this->getAppName(),
            'profile' => $stuff,
            'tags' => $this->getTags(),
            'hostname' => gethostname(),
            'date' => time(),
        ];

        $encoded = json_encode($profileData);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(json_last_error_msg());
        }
        $request = $this->requestFactory->createRequest(
            'POST',
            $this->schema . '://' . $this->host . '/' . $this->path
        );
        $request = $request->withBody($this->streamFactory->createStream($encoded));
        try {
            $this->client->sendRequest($request);
        } catch (Throwable $e) {
        }
    }

    public function getAppName(): string
    {
        return $this->appName ?? gethostname();
    }

    public function setAppName(string $appName): self
    {
        $this->appName = $appName;

        return $this;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): self
    {
        $this->tags = $tags;

        return $this;
    }
}
