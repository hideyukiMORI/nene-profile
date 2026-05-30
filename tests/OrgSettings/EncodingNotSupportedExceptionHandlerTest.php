<?php

declare(strict_types=1);

namespace NeneProfile\Tests\OrgSettings;

use NeneProfile\OrgSettings\EncodingNotSupportedException;
use NeneProfile\OrgSettings\EncodingNotSupportedExceptionHandler;
use NeneProfile\Tests\Http\ProblemDetailsTestTrait;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class EncodingNotSupportedExceptionHandlerTest extends TestCase
{
    use ProblemDetailsTestTrait;

    public function test_supports_only_its_exception(): void
    {
        $handler = new EncodingNotSupportedExceptionHandler($this->problemFactory());

        $this->assertTrue($handler->supports(new EncodingNotSupportedException('latin1')));
        $this->assertFalse($handler->supports(new RuntimeException()));
    }

    public function test_returns_422_validation_problem(): void
    {
        $handler = new EncodingNotSupportedExceptionHandler($this->problemFactory());

        $response = $handler->handle(
            new EncodingNotSupportedException('latin1'),
            $this->request('PATCH', '/admin/organization-settings'),
        );

        $this->assertProblem($response, 422, 'validation-failed', '/admin/organization-settings');
    }
}
