<?php

declare(strict_types=1);

namespace NeneProfile\Preset;

use Nene2\Error\DomainExceptionHandlerInterface;
use Nene2\Error\ProblemDetailsResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final readonly class InvalidMappingDefinitionExceptionHandler implements DomainExceptionHandlerInterface
{
    public function __construct(
        private ProblemDetailsResponseFactory $problemDetails,
    ) {
    }

    public function supports(Throwable $exception): bool
    {
        return $exception instanceof InvalidMappingDefinitionException;
    }

    public function handle(Throwable $exception, ServerRequestInterface $request): ResponseInterface
    {
        assert($exception instanceof InvalidMappingDefinitionException);

        return $this->problemDetails->create(
            $request,
            'validation-failed',
            'Validation Failed',
            422,
            'The mapping definition is invalid.',
            ['errors' => array_map(
                static fn (array $e): array => [
                    'field'   => 'definition.' . $e['field'],
                    'message' => $e['message'],
                    'code'    => $e['code'],
                ],
                $exception->errors,
            )],
        );
    }
}
