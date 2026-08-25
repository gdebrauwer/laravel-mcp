<?php

declare(strict_types=1);

use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Prompt;
use PHPUnit\Framework\AssertionFailedError;

class GarageListPromptsServer extends Server
{
    public int $defaultPaginationLength = 1;

    protected array $prompts = [
        OilChangePrompt::class,
        TireRotationPrompt::class,
    ];
}

class OilChangePrompt extends Prompt
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

class TireRotationPrompt extends Prompt
{
    protected string $name = 'garage/tire-rotation';

    public function handle(): string
    {
        return 'Tires rotated.';
    }
}

it('asserts registered prompts of a server', function (): void {
    GarageListPromptsServer::prompts()->assertRegistered([
        OilChangePrompt::class,
        TireRotationPrompt::class,
    ]);

    try {
        GarageListPromptsServer::prompts()->assertRegistered([
            TireRotationPrompt::class,
        ])->assertNotRegistered([
            OilChangePrompt::class,
        ]);

        $this->fail('Expected an AssertionFailedError to be thrown, but it was not.');
    } catch (AssertionFailedError $error) {
        expect($error->getMessage())->toContain('The expected class ['.OilChangePrompt::class.'] was found in the response content.');
    }

    OilChangePrompt::$shouldRegister = false;

    GarageListPromptsServer::prompts()->assertRegistered([
        TireRotationPrompt::class,
    ])->assertNotRegistered([
        OilChangePrompt::class,
    ]);

    try {
        GarageListPromptsServer::prompts()->assertRegistered([
            TireRotationPrompt::class,
            OilChangePrompt::class,
        ]);

        $this->fail('Expected an AssertionFailedError to be thrown, but it was not.');
    } catch (AssertionFailedError $error) {
        expect($error->getMessage())->toContain('The expected class ['.OilChangePrompt::class.'] was not found in the response content.');
    }
});
