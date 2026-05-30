import type { FieldErrors, UseFormRegister } from 'react-hook-form'
import type { StandardField } from '@/entities/mapping-preset'
import { useTranslation, type MessageKey } from '@/shared/i18n'
import { Field, Input, Select, Stack, Text, type SelectOption } from '@/shared/ui'
import {
  DELIMITERS,
  ENCODINGS,
  STANDARD_FIELDS,
  TRANSFORMS,
  type PresetFormValues,
} from '../hooks/preset-schema'

interface PresetFieldsProps {
  register: UseFormRegister<PresetFormValues>
  errors: FieldErrors<PresetFormValues>
}

const fieldLabelKey: Record<StandardField, MessageKey> = {
  transaction_date: 'admin.mappingPresets.field.transactionDate',
  value_date: 'admin.mappingPresets.field.valueDate',
  amount: 'admin.mappingPresets.field.amount',
  description: 'admin.mappingPresets.field.description',
  counterparty: 'admin.mappingPresets.field.counterparty',
  balance: 'admin.mappingPresets.field.balance',
}

/** Identity options where the enum value doubles as its (technical) label. */
function rawOptions(values: readonly string[]): readonly SelectOption[] {
  return values.map((value) => ({ value, label: value }))
}

/**
 * Presentational definition editor shared by the create and edit preset forms:
 * file-parsing settings + a row per StandardTransaction field.
 */
export function PresetFields({ register, errors }: PresetFieldsProps) {
  const { t } = useTranslation()

  return (
    <Stack gap="lg">
      <Field
        label={t('admin.mappingPresets.create.name')}
        {...(errors.name ? { error: t('admin.mappingPresets.create.nameRequired') } : {})}
      >
        {({ id, invalid }) => <Input id={id} type="text" invalid={invalid} {...register('name')} />}
      </Field>

      <Field
        label={t('admin.mappingPresets.create.bankLabel')}
        {...(errors.bankLabel ? { error: t('admin.mappingPresets.create.bankLabelRequired') } : {})}
      >
        {({ id, invalid }) => (
          <Input id={id} type="text" invalid={invalid} {...register('bankLabel')} />
        )}
      </Field>

      <div className="grid grid-cols-2 gap-inline-md">
        <Field label={t('admin.mappingPresets.create.encoding')}>
          {({ id }) => <Select id={id} options={rawOptions(ENCODINGS)} {...register('encoding')} />}
        </Field>
        <Field label={t('admin.mappingPresets.create.delimiter')}>
          {({ id }) => (
            <Select id={id} options={rawOptions(DELIMITERS)} {...register('delimiter')} />
          )}
        </Field>
        <Field label={t('admin.mappingPresets.create.headerRow')}>
          {({ id }) => (
            <Input
              id={id}
              type="number"
              min={0}
              {...register('headerRowIndex', { valueAsNumber: true })}
            />
          )}
        </Field>
        <Field label={t('admin.mappingPresets.create.yearPivot')}>
          {({ id }) => (
            <Input
              id={id}
              type="number"
              min={0}
              max={99}
              {...register('yearPivot', { valueAsNumber: true })}
            />
          )}
        </Field>
      </div>

      <Stack gap="sm">
        <Text variant="caption" tone="muted">
          {t('admin.mappingPresets.create.columns')}
        </Text>
        <Text variant="caption" tone="muted">
          {t('admin.mappingPresets.create.columnsHint')}
        </Text>
        {STANDARD_FIELDS.map((field, index) => (
          <div key={field} className="grid grid-cols-12 items-end gap-inline-sm">
            <div className="col-span-3">
              <Text variant="caption" tone="muted">
                {t(fieldLabelKey[field])}
              </Text>
            </div>
            <div className="col-span-4">
              <Input
                type="text"
                aria-label={`${t(fieldLabelKey[field])} ${t('admin.mappingPresets.create.source')}`}
                {...register(`columns.${String(index)}.source`)}
              />
            </div>
            <div className="col-span-4">
              <Select
                aria-label={`${t(fieldLabelKey[field])} ${t('admin.mappingPresets.create.transform')}`}
                options={rawOptions(TRANSFORMS)}
                {...register(`columns.${String(index)}.transform`)}
              />
            </div>
            <label className="col-span-1 flex items-center gap-inline-sm text-caption text-text-muted">
              <input type="checkbox" {...register(`columns.${String(index)}.optional`)} />
              {t('admin.mappingPresets.create.optional')}
            </label>
          </div>
        ))}
      </Stack>
    </Stack>
  )
}
