<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Http;

use Nene2\Error\ProblemDetailsResponseFactory;
use Nene2\Http\JsonResponseFactory;
use Nyholm\Psr7\Factory\Psr17Factory;

use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertStringContainsString;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Shared setup for exception-handler / handler tests: real PSR-17 factories
 * (nyholm) wired into the NENE2 response factories, plus Problem Details
 * assertions against the RFC 9457 contract.
 */
trait ProblemDetailsTestTrait
{
    private const PROBLEM_BASE_URL = 'https://nene2.dev/problems/';

    private function psr17(): Psr17Factory
    {
        return new Psr17Factory();
    }

    private function problemFactory(): ProblemDetailsResponseFactory
    {
        $psr17 = $this->psr17();

        return new ProblemDetailsResponseFactory($psr17, $psr17);
    }

    private function jsonFactory(): JsonResponseFactory
    {
        $psr17 = $this->psr17();

        return new JsonResponseFactory($psr17, $psr17);
    }

    private function request(string $method = 'GET', string $path = '/admin/resource'): ServerRequestInterface
    {
        return $this->psr17()->createServerRequest($method, 'https://example.test' . $path);
    }

    /**
     * Request with JSON body — for POST/PATCH handler tests.
     *
     * @param array<string, mixed> $data
     */
    private function jsonRequest(string $method, string $path, array $data): ServerRequestInterface
    {
        $body = $this->psr17()->createStream((string) json_encode($data, JSON_THROW_ON_ERROR));

        return $this->psr17()
            ->createServerRequest($method, 'https://example.test' . $path)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($body);
    }

    private function withAuth(
        ServerRequestInterface $request,
        int $orgId = 1,
        int $userId = 1,
        string $role = 'admin',
    ): ServerRequestInterface {
        return $request
            ->withAttribute('nene2.org.id', $orgId)
            ->withAttribute('nene2.auth.claims', [
                'sub'    => $userId,
                'role'   => $role,
                'org_id' => $role === 'superadmin' ? null : $orgId,
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJson(ResponseInterface $response): array
    {
        assertStringContainsString('application/json', $response->getHeaderLine('Content-Type'));

        /** @var array<string, mixed> $payload */
        $payload = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return $payload;
    }

    /**
     * @return array<string, mixed> the decoded problem payload, for further assertions
     */
    private function assertProblem(
        ResponseInterface $response,
        int $status,
        string $typeSuffix,
        string $instance,
    ): array {
        assertSame($status, $response->getStatusCode());
        assertStringContainsString(
            'application/problem+json',
            $response->getHeaderLine('Content-Type'),
        );

        /** @var array<string, mixed> $payload */
        $payload = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        assertArrayHasKey('type', $payload);
        assertArrayHasKey('title', $payload);
        assertSame(self::PROBLEM_BASE_URL . $typeSuffix, $payload['type']);
        assertSame($status, $payload['status']);
        assertSame($instance, $payload['instance']);

        return $payload;
    }
}
