<?php

declare(strict_types=1);

namespace NeneProfile\Tests\ImportJob;

use NeneProfile\ImportJob\ImportJobNotFoundException;
use NeneProfile\ImportJob\ImportJobNotFoundExceptionHandler;
use NeneProfile\Tests\Http\ProblemDetailsTestTrait;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ImportJobNotFoundExceptionHandlerTest extends TestCase
{
    use ProblemDetailsTestTrait;

    public function test_supports_only_its_exception(): void
    {
        $handler = new ImportJobNotFoundExceptionHandler($this->problemFactory());

        $this->assertTrue($handler->supports(new ImportJobNotFoundException(7)));
        $this->assertFalse($handler->supports(new RuntimeException()));
    }

    public function test_returns_404_problem(): void
    {
        $handler = new ImportJobNotFoundExceptionHandler($this->problemFactory());

        $response = $handler->handle(
            new ImportJobNotFoundException(7),
            $this->request('GET', '/admin/import-jobs/7'),
        );

        $payload = $this->assertProblem($response, 404, 'import-job-not-found', '/admin/import-jobs/7');
        $this->assertStringContainsString('7', (string) $payload['detail']);
    }
}
