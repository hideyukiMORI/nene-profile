<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Preset;

use Nene2\Routing\Router;
use Nene2\Validation\ValidationException;
use NeneProfile\Audit\AuditRecorder;
use NeneProfile\Preset\CreateMappingPresetHandler;
use NeneProfile\Preset\CreateMappingPresetUseCase;
use NeneProfile\Preset\DeleteMappingPresetHandler;
use NeneProfile\Preset\DeleteMappingPresetUseCase;
use NeneProfile\Preset\GetMappingPresetByIdHandler;
use NeneProfile\Preset\GetMappingPresetByIdUseCase;
use NeneProfile\Preset\ListMappingPresetsHandler;
use NeneProfile\Preset\ListMappingPresetsUseCase;
use NeneProfile\Preset\UpdateMappingPresetHandler;
use NeneProfile\Preset\UpdateMappingPresetUseCase;
use NeneProfile\Tests\Audit\InMemoryAuditLogRepository;
use NeneProfile\Tests\Http\ProblemDetailsTestTrait;
use PHPUnit\Framework\TestCase;

final class PresetHandlersTest extends TestCase
{
    use ProblemDetailsTestTrait;

    private InMemoryMappingPresetRepository $presets;
    private InMemoryMappingPresetVersionRepository $versions;
    private AuditRecorder $audit;

    protected function setUp(): void
    {
        $this->presets  = new InMemoryMappingPresetRepository();
        $this->versions = new InMemoryMappingPresetVersionRepository();
        $this->audit    = new AuditRecorder(new InMemoryAuditLogRepository());
    }

    /** @return array<string, mixed> */
    private function minimalDefinition(): array
    {
        return [
            'encoding'        => 'auto',
            'delimiter'       => 'auto',
            'header_row_index' => 0,
            'year_pivot'      => 50,
            'columns'         => [
                'transaction_date' => ['source' => '日付', 'transform' => 'date_ymd_slash'],
                'amount_cents'     => ['source' => ['入金額', '出金額'], 'transform' => 'debit_credit_to_signed_cents'],
                'description'      => ['source' => '摘要', 'transform' => 'trim'],
            ],
        ];
    }

    private function createPreset(int $orgId = 1): int
    {
        $uc = new CreateMappingPresetUseCase($this->presets, $this->versions, $this->audit);

        $handler = new CreateMappingPresetHandler($uc, $this->jsonFactory(), $this->problemFactory());
        $request = $this->withAuth(
            $this->jsonRequest('POST', '/admin/mapping-presets', [
                'name'       => 'Test Preset',
                'bank_label' => 'Test Bank',
                'definition' => $this->minimalDefinition(),
            ]),
            orgId: $orgId,
        );
        $response = $handler->handle($request);
        /** @var array<string, mixed> $payload */
        $payload = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return (int) $payload['id'];
    }

    // ── CreateMappingPresetHandler ────────────────────────────────────────

    public function test_create_returns_201_with_payload(): void
    {
        $handler = new CreateMappingPresetHandler(
            new CreateMappingPresetUseCase($this->presets, $this->versions, $this->audit),
            $this->jsonFactory(),
            $this->problemFactory(),
        );

        $request = $this->withAuth($this->jsonRequest('POST', '/admin/mapping-presets', [
            'name'       => 'MUFG Preset',
            'bank_label' => 'MUFG',
            'definition' => $this->minimalDefinition(),
        ]));

        $response = $handler->handle($request);
        $payload  = $this->decodeJson($response);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('MUFG Preset', $payload['name']);
        $this->assertSame('MUFG', $payload['bank_label']);
        $this->assertSame(1, $payload['version_number']);
        $this->assertArrayHasKey('definition', $payload);
    }

    public function test_create_returns_400_without_org(): void
    {
        $handler = new CreateMappingPresetHandler(
            new CreateMappingPresetUseCase($this->presets, $this->versions, $this->audit),
            $this->jsonFactory(),
            $this->problemFactory(),
        );

        $response = $handler->handle($this->jsonRequest('POST', '/admin/mapping-presets', [
            'name' => 'X', 'bank_label' => 'Y', 'definition' => $this->minimalDefinition(),
        ]));

        $this->assertSame(400, $response->getStatusCode());
    }

    public function test_create_throws_validation_when_name_missing(): void
    {
        $this->expectException(ValidationException::class);

        $handler = new CreateMappingPresetHandler(
            new CreateMappingPresetUseCase($this->presets, $this->versions, $this->audit),
            $this->jsonFactory(),
            $this->problemFactory(),
        );
        $request = $this->withAuth($this->jsonRequest('POST', '/admin/mapping-presets', [
            'bank_label' => 'MUFG',
            'definition' => $this->minimalDefinition(),
        ]));
        $handler->handle($request);
    }

    public function test_create_throws_validation_when_bank_label_missing(): void
    {
        $this->expectException(ValidationException::class);

        $handler = new CreateMappingPresetHandler(
            new CreateMappingPresetUseCase($this->presets, $this->versions, $this->audit),
            $this->jsonFactory(),
            $this->problemFactory(),
        );
        $request = $this->withAuth($this->jsonRequest('POST', '/admin/mapping-presets', [
            'name'       => 'Preset',
            'definition' => $this->minimalDefinition(),
        ]));
        $handler->handle($request);
    }

    public function test_create_throws_validation_when_definition_missing(): void
    {
        $this->expectException(ValidationException::class);

        $handler = new CreateMappingPresetHandler(
            new CreateMappingPresetUseCase($this->presets, $this->versions, $this->audit),
            $this->jsonFactory(),
            $this->problemFactory(),
        );
        $request = $this->withAuth($this->jsonRequest('POST', '/admin/mapping-presets', [
            'name'       => 'Preset',
            'bank_label' => 'Bank',
        ]));
        $handler->handle($request);
    }

    // ── GetMappingPresetByIdHandler ───────────────────────────────────────

    public function test_get_returns_preset_with_definition(): void
    {
        $id = $this->createPreset();

        $handler = new GetMappingPresetByIdHandler(
            new GetMappingPresetByIdUseCase($this->presets, $this->versions),
            $this->jsonFactory(),
            $this->problemFactory(),
        );

        $request = $this->withAuth($this->request('GET', "/admin/mapping-presets/{$id}"))
            ->withAttribute(Router::PARAMETERS_ATTRIBUTE, ['id' => (string) $id]);

        $response = $handler->handle($request);
        $payload  = $this->decodeJson($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($id, $payload['id']);
        $this->assertArrayHasKey('definition', $payload);
        $this->assertArrayHasKey('version_number', $payload);
    }

    public function test_get_returns_400_without_org(): void
    {
        $handler = new GetMappingPresetByIdHandler(
            new GetMappingPresetByIdUseCase($this->presets, $this->versions),
            $this->jsonFactory(),
            $this->problemFactory(),
        );

        $response = $handler->handle(
            $this->request('GET', '/admin/mapping-presets/1')
                ->withAttribute(Router::PARAMETERS_ATTRIBUTE, ['id' => '1']),
        );

        $this->assertSame(400, $response->getStatusCode());
    }

    // ── ListMappingPresetsHandler ─────────────────────────────────────────

    public function test_list_returns_paginated_envelope(): void
    {
        $this->createPreset();
        $this->createPreset();

        $handler = new ListMappingPresetsHandler(
            new ListMappingPresetsUseCase($this->presets),
            $this->jsonFactory(),
            $this->problemFactory(),
        );

        $response = $handler->handle($this->withAuth($this->request('GET', '/admin/mapping-presets')));
        $payload  = $this->decodeJson($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(2, $payload['total']);
        $this->assertArrayHasKey('items', $payload);
        $this->assertArrayHasKey('limit', $payload);
        $this->assertArrayHasKey('offset', $payload);
    }

    public function test_list_respects_limit_param(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->createPreset();
        }

        $handler = new ListMappingPresetsHandler(
            new ListMappingPresetsUseCase($this->presets),
            $this->jsonFactory(),
            $this->problemFactory(),
        );

        $request = $this->withAuth($this->request('GET', '/admin/mapping-presets'))
            ->withQueryParams(['limit' => '2', 'offset' => '0']);

        $response = $handler->handle($request);
        $payload  = $this->decodeJson($response);

        $this->assertSame(3, $payload['total']);
        $this->assertCount(2, $payload['items']);
    }

    // ── UpdateMappingPresetHandler ────────────────────────────────────────

    public function test_update_returns_200_with_new_version(): void
    {
        $id = $this->createPreset();

        $handler = new UpdateMappingPresetHandler(
            new UpdateMappingPresetUseCase($this->presets, $this->versions, $this->audit),
            $this->jsonFactory(),
            $this->problemFactory(),
        );

        $newDef              = $this->minimalDefinition();
        $newDef['year_pivot'] = 40;

        $request = $this->withAuth(
            $this->jsonRequest('PATCH', "/admin/mapping-presets/{$id}", ['definition' => $newDef]),
        )->withAttribute(Router::PARAMETERS_ATTRIBUTE, ['id' => (string) $id]);

        $response = $handler->handle($request);
        $payload  = $this->decodeJson($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(2, $payload['version_number']); // new version
    }

    public function test_update_metadata_only_preserves_version(): void
    {
        $id = $this->createPreset();

        $handler = new UpdateMappingPresetHandler(
            new UpdateMappingPresetUseCase($this->presets, $this->versions, $this->audit),
            $this->jsonFactory(),
            $this->problemFactory(),
        );

        $request = $this->withAuth(
            $this->jsonRequest('PATCH', "/admin/mapping-presets/{$id}", ['name' => 'Updated Name']),
        )->withAttribute(Router::PARAMETERS_ATTRIBUTE, ['id' => (string) $id]);

        $response = $handler->handle($request);
        $payload  = $this->decodeJson($response);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Updated Name', $payload['name']);
        $this->assertSame(1, $payload['version_number']); // version unchanged
    }

    // ── DeleteMappingPresetHandler ────────────────────────────────────────

    public function test_delete_returns_204(): void
    {
        $id = $this->createPreset();

        $handler = new DeleteMappingPresetHandler(
            new DeleteMappingPresetUseCase($this->presets, $this->audit),
            $this->psr17(),
            $this->problemFactory(),
        );

        $request = $this->withAuth($this->request('DELETE', "/admin/mapping-presets/{$id}"))
            ->withAttribute(Router::PARAMETERS_ATTRIBUTE, ['id' => (string) $id]);

        $response = $handler->handle($request);

        $this->assertSame(204, $response->getStatusCode());
    }

    public function test_delete_returns_400_without_org(): void
    {
        $handler = new DeleteMappingPresetHandler(
            new DeleteMappingPresetUseCase($this->presets, $this->audit),
            $this->psr17(),
            $this->problemFactory(),
        );

        $response = $handler->handle(
            $this->request('DELETE', '/admin/mapping-presets/1')
                ->withAttribute(Router::PARAMETERS_ATTRIBUTE, ['id' => '1']),
        );

        $this->assertSame(400, $response->getStatusCode());
    }

    public function test_delete_propagates_not_found(): void
    {
        $this->expectException(\NeneProfile\Preset\MappingPresetNotFoundException::class);

        $handler = new DeleteMappingPresetHandler(
            new DeleteMappingPresetUseCase($this->presets, $this->audit),
            $this->psr17(),
            $this->problemFactory(),
        );

        $request = $this->withAuth($this->request('DELETE', '/admin/mapping-presets/999'))
            ->withAttribute(Router::PARAMETERS_ATTRIBUTE, ['id' => '999']);

        $handler->handle($request);
    }
}
