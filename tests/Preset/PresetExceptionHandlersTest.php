<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Preset;

use NeneProfile\Preset\InvalidMappingDefinitionException;
use NeneProfile\Preset\InvalidMappingDefinitionExceptionHandler;
use NeneProfile\Preset\MappingPresetNotFoundException;
use NeneProfile\Preset\MappingPresetNotFoundExceptionHandler;
use NeneProfile\Tests\Http\ProblemDetailsTestTrait;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class PresetExceptionHandlersTest extends TestCase
{
    use ProblemDetailsTestTrait;

    public function test_preset_not_found_returns_404(): void
    {
        $handler = new MappingPresetNotFoundExceptionHandler($this->problemFactory());

        $this->assertTrue($handler->supports(new MappingPresetNotFoundException(3)));
        $this->assertFalse($handler->supports(new RuntimeException()));

        $response = $handler->handle(
            new MappingPresetNotFoundException(3),
            $this->request('GET', '/admin/mapping-presets/3'),
        );

        $this->assertProblem($response, 404, 'mapping-preset-not-found', '/admin/mapping-presets/3');
    }

    public function test_invalid_definition_returns_422_with_prefixed_field_errors(): void
    {
        $handler = new InvalidMappingDefinitionExceptionHandler($this->problemFactory());

        $exception = new InvalidMappingDefinitionException([
            ['field' => 'columns.amount.transform', 'message' => 'unknown transform', 'code' => 'invalid'],
        ]);

        $response = $handler->handle($exception, $this->request('POST', '/admin/mapping-presets'));

        $payload = $this->assertProblem($response, 422, 'validation-failed', '/admin/mapping-presets');

        $this->assertArrayHasKey('errors', $payload);
        /** @var list<array<string, string>> $errors */
        $errors = $payload['errors'];
        $this->assertCount(1, $errors);
        $this->assertSame('definition.columns.amount.transform', $errors[0]['field']);
        $this->assertSame('unknown transform', $errors[0]['message']);
        $this->assertSame('invalid', $errors[0]['code']);
    }
}
