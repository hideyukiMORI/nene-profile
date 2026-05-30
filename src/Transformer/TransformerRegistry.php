<?php

declare(strict_types=1);

namespace NeneProfile\Transformer;

/**
 * Resolves a transformer ID (terminology.md §7) to a transformer instance.
 *
 * Stateless transformers are shared singletons. `regex_extract` is
 * parameterized, so it is built per-call from the supplied params.
 */
final class TransformerRegistry
{
    /** @var array<string, TransformerInterface> */
    private array $stateless;

    public function __construct()
    {
        $this->stateless = [
            'trim'                         => new TrimTransformer(),
            'date_ymd_slash'               => new DateYmdSlashTransformer(),
            'date_ymd_dash'                => new DateYmdDashTransformer(),
            'date_ymd_compact'             => new DateYmdCompactTransformer(),
            'amount_yen_to_cents'          => new AmountYenToCentsTransformer(),
            'debit_credit_to_signed_cents' => new DebitCreditToSignedCentsTransformer(),
            'single_column_signed_cents'   => new SingleColumnSignedCentsTransformer(),
        ];
    }

    public function has(string $id): bool
    {
        return $id === 'regex_extract' || isset($this->stateless[$id]);
    }

    /**
     * @param array<string, mixed> $params transformer params (e.g. regex pattern/group)
     * @throws UnknownTransformerException
     */
    public function get(string $id, array $params = []): TransformerInterface
    {
        if ($id === 'regex_extract') {
            $pattern = is_string($params['pattern'] ?? null) ? (string) $params['pattern'] : '';
            $group = is_int($params['group'] ?? null) ? (int) $params['group'] : 0;

            return new RegexExtractTransformer($pattern, $group);
        }

        return $this->stateless[$id] ?? throw new UnknownTransformerException($id);
    }
}
