<?php

declare(strict_types=1);

namespace NeneProfile\Organization;

use Nene2\Http\JsonResponseFactory;
use Nene2\Http\PaginationQueryParser;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class ListOrganizationsHandler
{
    public function __construct(
        private ListOrganizationsUseCaseInterface $useCase,
        private JsonResponseFactory $response,
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $pagination = PaginationQueryParser::parse($request);

        $output = $this->useCase->execute(new ListOrganizationsInput(
            limit: $pagination->limit,
            offset: $pagination->offset,
        ));

        return $this->response->create([
            'items'  => array_map(
                static fn (ListOrganizationItem $item) => [
                    'id'            => $item->id,
                    'name'          => $item->name,
                    'slug'          => $item->slug,
                    'is_active'     => $item->isActive,
                    'custom_domain' => $item->customDomain,
                    'created_at'    => $item->createdAt,
                    'updated_at'    => $item->updatedAt,
                ],
                $output->items,
            ),
            'total'  => $output->total,
            'limit'  => $output->limit,
            'offset' => $output->offset,
        ]);
    }
}
