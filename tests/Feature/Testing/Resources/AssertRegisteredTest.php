<?php

declare(strict_types=1);

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Resource;
use PHPUnit\Framework\AssertionFailedError;

class GarageListResourcesServer extends Server
{
    public int $defaultPaginationLength = 1;

    protected array $resources = [
        OilChangeResource::class,
        TireRotationResource::class,
    ];
}

class OilChangeResource extends Resource
{
    public static $shouldRegister = true;

    protected string $name = 'garage/oil-change';

    public function shouldRegister(): bool
    {
        return static::$shouldRegister;
    }

    public function handle(): string
    {
        return 'Oil changed.';
    }
}

class TireRotationResource extends Resource
{
    protected string $name = 'garage/tire-rotation';

    public function handle(): string
    {
        return 'Tires rotated.';
    }
}

it('asserts registered resources of a server', function (): void {
    GarageListResourcesServer::resources()->assertRegistered([
        OilChangeResource::class,
        TireRotationResource::class,
    ]);

    try {
        GarageListResourcesServer::resources()->assertRegistered([
            TireRotationResource::class,
        ])->assertNotRegistered([
            OilChangeResource::class,
        ]);

        $this->fail('Expected an AssertionFailedError to be thrown, but it was not.');
    } catch (AssertionFailedError $error) {
        expect($error->getMessage())->toContain('The expected class ['.OilChangeResource::class.'] was found in the response content.');
    }

    OilChangeResource::$shouldRegister = false;

    GarageListResourcesServer::resources()->assertRegistered([
        TireRotationResource::class,
    ])->assertNotRegistered([
        OilChangeResource::class,
    ]);

    try {
        GarageListResourcesServer::resources()->assertRegistered([
            TireRotationResource::class,
            OilChangeResource::class,
        ]);

        $this->fail('Expected an AssertionFailedError to be thrown, but it was not.');
    } catch (AssertionFailedError $error) {
        expect($error->getMessage())->toContain('The expected class ['.OilChangeResource::class.'] was not found in the response content.');
    }
});
