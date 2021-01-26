<?php

namespace Anteris\ITGlue;

use Anteris\ITGlue\Support\Exception\InvalidConnectionException;
use Http\Client\Common\HttpMethodsClient;
use Http\Client\Common\HttpMethodsClientInterface;
use Http\Client\Common\Plugin;
use Http\Client\Common\Plugin\BaseUriPlugin;
use Http\Client\Common\Plugin\CachePlugin;
use Http\Client\Common\Plugin\ErrorPlugin;
use Http\Client\Common\Plugin\HeaderDefaultsPlugin;
use Http\Client\Common\PluginClient;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class ConnectionFactory
{
    protected string $baseUri = 'https://api.itglue.com';
    protected CacheItemPoolInterface $cache;
    protected array $cacheConfig;
    protected ClientInterface $client;
    protected string $name   = 'default';
    protected array $plugins = [];
    protected RequestFactoryInterface $requestFactory;
    protected StreamFactoryInterface $streamFactory;
    protected string $token;

    public static function new(string $name = null)
    {
        $new = new self;

        if ($name) {
            $new->name($name);
        }

        return $new;
    }

    /***************************************************************************
     * Factory Options
     **************************************************************************/

    public function baseUri(string $baseUri): ConnectionFactory
    {
        $this->baseUri = $baseUri;

        return $this;
    }

    public function cache(CacheItemPoolInterface $cache, array $config = []): ConnectionFactory
    {
        $this->cache       = $cache;
        $this->cacheConfig = $config;

        return $this;
    }

    public function client(ClientInterface $client): ConnectionFactory
    {
        $this->client = $client;

        return $this;
    }

    public function eu(): ConnectionFactory
    {
        return $this->baseUri('https://api.eu.itglue.com');
    }

    public function name(string $name): ConnectionFactory
    {
        $this->name = $name;

        return $this;
    }

    public function registerPlugin(Plugin $plugin): ConnectionFactory
    {
        $this->plugins[] = $plugin;

        return $this;
    }

    public function requestFactory(RequestFactoryInterface $factory): ConnectionFactory
    {
        $this->requestFactory = $factory;

        return $this;
    }

    public function streamFactory(StreamFactoryInterface $factory): ConnectionFactory
    {
        $this->streamFactory = $factory;

        return $this;
    }

    public function token(string $token): ConnectionFactory
    {
        $this->token = $token;

        return $this;
    }

    /***************************************************************************
     * Factory Creators
     **************************************************************************/

    /**
     * Creates a new http client and commits it to the connection manager.
     */
    public function commit(): void
    {
        Connection::set($this->name, $this->create());
    }

    /**
     * Creates a new http client.
     */
    public function create(): HttpMethodsClientInterface
    {
        $this->validate();

        // Find our PSR compliant clients
        $client         = $this->client ?? Psr18ClientDiscovery::find();
        $requestFactory = $this->requestFactory ?? Psr17FactoryDiscovery::findRequestFactory();
        $streamFactory  = $this->streamFactory ?? Psr17FactoryDiscovery::findStreamFactory();
        $uriFactory     = Psr17FactoryDiscovery::findUriFactory();

        // Load our plugins and add defaults
        $plugins = $this->plugins;

        $plugins[] = new BaseUriPlugin($uriFactory->createUri($this->baseUri));
        $plugins[] = new ErrorPlugin();
        $plugins[] = new HeaderDefaultsPlugin([
            'x-api-key'    => $this->token,
            'Content-Type' => 'application/vnd.api+json',
        ]);

        if (isset($this->cache)) {
            $plugins[] = CachePlugin::serverCache($this->cache, $streamFactory, $this->cacheConfig);
        }

        // Build our client and pass it back
        return new HttpMethodsClient(
            new PluginClient($client, $plugins),
            $requestFactory,
            $streamFactory
        );
    }

    protected function validate()
    {
        if (! isset($this->name)) {
            throw new InvalidConnectionException(
                'No name was specified for this connection! Did you forget to call "name()"?'
            );
        }

        if (! isset($this->token)) {
            throw new InvalidConnectionException(
                'No API token was specified for this connection! Did you forget to call "token()"?'
            );
        }
    }
}
