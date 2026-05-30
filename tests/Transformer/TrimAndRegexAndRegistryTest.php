<?php

declare(strict_types=1);

namespace NeneProfile\Tests\Transformer;

use NeneProfile\Transformer\RegexExtractTransformer;
use NeneProfile\Transformer\TransformContext;
use NeneProfile\Transformer\TransformerRegistry;
use NeneProfile\Transformer\TrimTransformer;
use NeneProfile\Transformer\UnknownTransformerException;
use PHPUnit\Framework\TestCase;

final class TrimAndRegexAndRegistryTest extends TestCase
{
    private TransformContext $ctx;

    protected function setUp(): void
    {
        $this->ctx = new TransformContext();
    }

    public function test_trim_ascii_whitespace(): void
    {
        $out = (new TrimTransformer())->transform('  hello  ', $this->ctx);
        $this->assertSame('hello', $out->value);
    }

    public function test_trim_ideographic_space(): void
    {
        $out = (new TrimTransformer())->transform("\u{3000}振込\u{3000}", $this->ctx);
        $this->assertSame('振込', $out->value);
    }

    public function test_regex_extract_group(): void
    {
        $t = new RegexExtractTransformer('/振込\s+(.+)/u', 1);
        $out = $t->transform('振込 カブシキガイシャアオイ', $this->ctx);
        $this->assertSame('カブシキガイシャアオイ', $out->value);
    }

    public function test_regex_no_match_is_error(): void
    {
        $t = new RegexExtractTransformer('/^FOO(.+)/', 1);
        $out = $t->transform('BAR', $this->ctx);
        $this->assertFalse($out->ok);
    }

    public function test_regex_invalid_pattern_is_error(): void
    {
        $t = new RegexExtractTransformer('/(unclosed', 1);
        $out = $t->transform('anything', $this->ctx);
        $this->assertFalse($out->ok);
        $this->assertStringContainsString('invalid regex', (string) $out->error);
    }

    public function test_registry_resolves_all_builtin_transformers(): void
    {
        $registry = new TransformerRegistry();

        foreach ([
            'trim',
            'date_ymd_slash',
            'date_ymd_dash',
            'date_ymd_compact',
            'amount_yen_to_cents',
            'debit_credit_to_signed_cents',
            'single_column_signed_cents',
            'regex_extract',
        ] as $id) {
            $this->assertTrue($registry->has($id), "registry should know {$id}");
        }
    }

    public function test_registry_builds_regex_with_params(): void
    {
        $registry = new TransformerRegistry();
        $t = $registry->get('regex_extract', ['pattern' => '/(\d+)/', 'group' => 1]);
        $out = $t->transform('abc123', $this->ctx);
        $this->assertSame('123', $out->value);
    }

    public function test_registry_throws_on_unknown(): void
    {
        $registry = new TransformerRegistry();
        $this->assertFalse($registry->has('magic'));
        $this->expectException(UnknownTransformerException::class);
        $registry->get('magic');
    }
}
