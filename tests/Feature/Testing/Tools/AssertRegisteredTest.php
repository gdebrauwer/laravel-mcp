<?php

declare(strict_types=1);

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Tool;
use PHPUnit\Framework\AssertionFailedError;

class GarageListToolsServer extends Server
{
    public int $defaultPaginationLength = 1;

    protected array $tools = [
        OilChangeTool::class,
        TireRotationTool::class,
    ];
}

class OilChangeTool extends Tool
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

class TireRotationTool extends Tool
{
    protected string $name = 'garage/tire-rotation';

    public function handle(): string
    {
        return 'Tires rotated.';
    }
}

it('asserts registered tools of a server', function (): void {
    GarageListToolsServer::tools()->assertRegistered([
        OilChangeTool::class,
        TireRotationTool::class,
    ]);

    try {
        GarageListToolsServer::tools()->assertRegistered([
            TireRotationTool::class,
        ])->assertNotRegistered([
            OilChangeTool::class,
        ]);

        $this->fail('Expected an AssertionFailedError to be thrown, but it was not.');
    } catch (AssertionFailedError $error) {
        expect($error->getMessage())->toContain('The expected class ['.OilChangeTool::class.'] was found in the response content.');
    }

    OilChangeTool::$shouldRegister = false;

    GarageListToolsServer::tools()->assertRegistered([
        TireRotationTool::class,
    ])->assertNotRegistered([
        OilChangeTool::class,
    ]);

    try {
        GarageListToolsServer::tools()->assertRegistered([
            TireRotationTool::class,
            OilChangeTool::class,
        ]);

        $this->fail('Expected an AssertionFailedError to be thrown, but it was not.');
    } catch (AssertionFailedError $error) {
        expect($error->getMessage())->toContain('The expected class ['.OilChangeTool::class.'] was not found in the response content.');
    }
});
