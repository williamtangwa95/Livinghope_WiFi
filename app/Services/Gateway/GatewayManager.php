<?php

namespace App\Services\Gateway;

use App\Contracts\HotspotGatewayInterface;
use InvalidArgumentException;

class GatewayManager
{
    protected array $drivers = [];

    public function __construct(
        protected array $config = []
    ) {}

    /**
     * Get a gateway driver instance.
     */
    public function driver(?string $name = null): HotspotGatewayInterface
    {
        $name = $name ?: ($this->config['driver'] ?? 'simulated');

        if (! isset($this->drivers[$name])) {
            $this->drivers[$name] = $this->createDriver($name);
        }

        return $this->drivers[$name];
    }

    /**
     * Factory method to create driver instance.
     */
    protected function createDriver(string $name): HotspotGatewayInterface
    {
        return match ($name) {
            'simulated', 'mock' => new SimulatedHotspotGateway,
            default => throw new InvalidArgumentException("Unsupported Hotspot Gateway driver: [{$name}]"),
        };
    }

    /**
     * Pass dynamic calls through to default gateway driver.
     */
    public function __call(string $method, array $parameters)
    {
        return $this->driver()->$method(...$parameters);
    }
}
