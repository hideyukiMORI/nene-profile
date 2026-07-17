import type { ReactNode } from 'react'
import type { FieldErrors, UseFormRegister, UseFormWatch } from 'react-hook-form'
import type { StandardField } from '@/entities/mapping-preset'
import { useTranslation, type MessageKey } from '@/shared/i18n'
import { Field, Input, Select, type SelectOption } from '@/shared/ui'
import {
  DELIMITERS,
  ENCODINGS,
  STANDARD_FIELDS,
  TRANSFORMS,
  type PresetFormValues,
} from '../model/preset-schema'

interface PresetFieldsProps {
  register: UseFormRegister<PresetFormValues>
  errors: FieldErrors<PresetFormValues>
  watch: UseFormWatch<PresetFormValues>
  /** Title shown on the first (basic settings) card. */
  title: string
  /** Actions rendered as the mapping card footer (cancel / submit). */
  footer: ReactNode
}

const fieldLabelKey: Record<StandardField, MessageKey> = {
  transaction_date: 'admin.mappingPresets.field.transactionDate',
  value_date: 'admin.mappingPresets.field.valueDate',
  amount: 'admin.mappingPresets.field.amount',
  description: 'admin.mappingPresets.field.description',
  counterparty: 'admin.mappingPresets.field.counterparty',
  balance: 'admin.mappingPresets.field.balance',
}

function rawOptions(values: readonly string[]): readonly SelectOption[] {
  return values.map((value) => ({ value, label: value }))
}

const headerLabelStyle = {
  fontSize: 11.5,
  textTransform: 'uppercase',
  letterSpacing: '.04em',
  color: 'var(--ink-3)',
} as const

export function PresetFields({ register, errors, watch, title, footer }: PresetFieldsProps) {
  const { t } = useTranslation()

  return (
    <>
      <div className="card" style={{ marginBottom: 18 }}>
        <div className="card__head">
          <h2 className="card__title">{title}</h2>
        </div>
        <div className="card__body">
          <div className="form-grid">
            <div className="field-row">
              <Field
                label={t('admin.mappingPresets.create.name')}
                {...(errors.name ? { error: t('admin.mappingPresets.create.nameRequired') } : {})}
              >
                {({ id, invalid }) => (
                  <Input id={id} type="text" invalid={invalid} {...register('name')} />
                )}
              </Field>
              <Field
                label={t('admin.mappingPresets.create.bankLabel')}
                {...(errors.bankLabel
                  ? { error: t('admin.mappingPresets.create.bankLabelRequired') }
                  : {})}
              >
                {({ id, invalid }) => (
                  <Input id={id} type="text" invalid={invalid} {...register('bankLabel')} />
                )}
              </Field>
            </div>
            <div className="field-row">
              <Field label={t('admin.mappingPresets.create.encoding')}>
                {({ id }) => (
                  <Select id={id} options={rawOptions(ENCODINGS)} {...register('encoding')} />
                )}
              </Field>
              <Field label={t('admin.mappingPresets.create.delimiter')}>
                {({ id }) => (
                  <Select id={id} options={rawOptions(DELIMITERS)} {...register('delimiter')} />
                )}
              </Field>
            </div>
            <div className="field-row">
              <Field label={t('admin.mappingPresets.create.headerRow')}>
                {({ id }) => (
                  <Input
                    id={id}
                    type="number"
                    min={0}
                    className="mono"
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
                    className="mono"
                    {...register('yearPivot', { valueAsNumber: true })}
                  />
                )}
              </Field>
            </div>
          </div>
        </div>
      </div>

      <div className="card">
        <div className="card__head">
          <div>
            <div className="card__title">{t('admin.mappingPresets.create.columns')}</div>
            <div className="card__desc">{t('admin.mappingPresets.create.columnsHint')}</div>
          </div>
        </div>
        <div className="card__body" style={{ paddingTop: 8, paddingBottom: 8 }}>
          <div
            className="map-row"
            style={{ borderBottom: '1px solid var(--line)', paddingBottom: 8 }}
          >
            <div style={headerLabelStyle}>{t('admin.mappingPresets.create.fieldHeader')}</div>
            <div style={headerLabelStyle}>{t('admin.mappingPresets.create.source')}</div>
            <div style={headerLabelStyle}>
              {`${t('admin.mappingPresets.create.transform')} / ${t('admin.mappingPresets.create.optional')}`}
            </div>
          </div>
          {STANDARD_FIELDS.map((field, index) => {
            const label = t(fieldLabelKey[field])
            const optional = watch(`columns.${index}.optional`)
            return (
              <div className="map-row" key={field}>
                <div className="mlabel">
                  {label}
                  <small>{field}</small>
                </div>
                <Input
                  type="text"
                  className="mono"
                  aria-label={`${label} ${t('admin.mappingPresets.create.source')}`}
                  placeholder={t('admin.mappingPresets.create.source')}
                  {...register(`columns.${index}.source`)}
                />
                <div className="row" style={{ gap: 10 }}>
                  <Select
                    style={{ flex: 1 }}
                    aria-label={`${label} ${t('admin.mappingPresets.create.transform')}`}
                    options={rawOptions(TRANSFORMS)}
                    {...register(`columns.${index}.transform`)}
                  />
                  <label
                    className={`toggle${optional ? ' is-on' : ''}`}
                    title={t('admin.mappingPresets.create.optional')}
                  >
                    <input type="checkbox" hidden {...register(`columns.${index}.optional`)} />
                    <span className="toggle__track" aria-hidden="true" />
                  </label>
                </div>
              </div>
            )
          })}
        </div>
        <div className="card__foot">{footer}</div>
      </div>
    </>
  )
}
