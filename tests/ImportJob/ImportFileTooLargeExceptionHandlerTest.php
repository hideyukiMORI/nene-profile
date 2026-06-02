<?php

declare(strict_types=1);

namespace NeneProfile\Tests\ImportJob;

use NeneProfile\ImportJob\ImportFileTooLargeException;
use NeneProfile\ImportJob\ImportFileTooLargeExceptionHandler;
use NeneProfile\Tests\Http\ProblemDetailsTestTrait;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ImportFileTooLargeExceptionHandlerTest extends TestCase
{
    use ProblemDetailsTestTrait;

    public function test_supports_only_its_exception(): void
    {
        $handler = new ImportFileTooLargeExceptionHandler($this->problemFactory());

        $this->assertTrue($handler->supports(new ImportFileTooLargeException(4)));
        $this->assertFalse($handler->supports(new RuntimeException()));
    }

    public function test_returns_413_problem(): void
    {
        $handler = new ImportFileTooLargeExceptionHandler($this->problemFactory());

        $response = $handler->handle(
            new ImportFileTooLargeException(1024),
            $this->request('POST', '/admin/import-jobs'),
        );

        $payload = $this->assertProblem($response, 413, 'file-too-large', '/admin/import-jobs');
        $this->assertStringContainsString('1024', (string) $payload['detail']);
    }
}
