import { useEffect, useState } from 'react'
import type { AuditLog } from '@/entities/audit-log'
import { useTranslation } from '@/shared/i18n'
import { Button, Icon } from '@/shared/ui'

interface AuditLogDiffProps {
  log: AuditLog
}

interface DiffField {
  key: string
  before: unknown
  after: unknown
  hasBefore: boolean
  hasAfter: boolean
}

function formatValue(value: unknown): string {
  if (value === null) return 'null'
  if (typeof value === 'string') return value
  return JSON.stringify(value)
}

function computeDiff(
  before: Record<string, unknown> | null,
  after: Record<string, unknown> | null,
): DiffField[] {
  const beforeObj = before ?? {}
  const afterObj = after ?? {}
  const keys = Array.from(new Set([...Object.keys(beforeObj), ...Object.keys(afterObj)])).sort()
  return keys
    .map((key) => ({
      key,
      before: beforeObj[key],
      after: afterObj[key],
      hasBefore: key in beforeObj,
      hasAfter: key in afterObj,
    }))
    .filter((field) => JSON.stringify(field.before) !== JSON.stringify(field.after))
}

/**
 * Audit entry change viewer: a "View diff" trigger that opens a right-hand
 * drawer with field-by-field before → after pills (design-system `.drawer`).
 */
export function AuditLogDiff({ log }: AuditLogDiffProps) {
  const { t } = useTranslation()
  const [open, setOpen] = useState(false)
  const [entered, setEntered] = useState(false)

  useEffect(() => {
    if (!open) {
      setEntered(false)
      return
    }
    const raf = requestAnimationFrame(() => {
      setEntered(true)
    })
    document.body.style.overflow = 'hidden'
    const onKeyDown = (event: KeyboardEvent): void => {
      if (event.key === 'Escape') setOpen(false)
    }
    document.addEventListener('keydown', onKeyDown)
    return () => {
      cancelAnimationFrame(raf)
      document.removeEventListener('keydown', onKeyDown)
      document.body.style.overflow = ''
    }
  }, [open])

  if (log.before === null && log.after === null) {
    return (
      <span className="muted" aria-hidden="true">
        —
      </span>
    )
  }

  const entity =
    log.entityId !== null ? `${log.entityType} #${String(log.entityId)}` : log.entityType
  const actor =
    log.actorUserId !== null ? `#${String(log.actorUserId)}` : t('admin.auditLogs.actor.system')
  const fields = computeDiff(log.before, log.after)

  return (
    <>
      <Button
        variant="link"
        onClick={() => {
          setOpen(true)
        }}
      >
        {t('admin.auditLogs.viewDiff')}
      </Button>

      {open ? (
        <>
          <div
            className={`drawer-backdrop${entered ? ' is-open' : ''}`}
            onClick={() => {
              setOpen(false)
            }}
            aria-hidden="true"
          />
          <aside
            className={`drawer${entered ? ' is-open' : ''}`}
            role="dialog"
            aria-modal="true"
            aria-label={t('admin.auditLogs.drawer.title')}
          >
            <div className="drawer__head">
              <div>
                <div className="drawer__title">{t('admin.auditLogs.drawer.title')}</div>
                <div className="drawer__sub">{t('admin.auditLogs.drawer.subject', { entity })}</div>
              </div>
              <button
                type="button"
                className="drawer__close"
                aria-label={t('admin.auditLogs.drawer.close')}
                onClick={() => {
                  setOpen(false)
                }}
              >
                <Icon name="close" />
              </button>
            </div>

            <dl className="drawer__meta">
              <dt>{t('admin.auditLogs.col.createdAt')}</dt>
              <dd className="mono">{log.createdAt}</dd>
              <dt>{t('admin.auditLogs.col.actor')}</dt>
              <dd>{actor}</dd>
              <dt>{t('admin.auditLogs.col.action')}</dt>
              <dd>
                <span className="tag">{log.action}</span>
              </dd>
            </dl>

            <div className="drawer__body">
              {fields.length === 0 ? (
                <p className="muted">{t('admin.auditLogs.changes.none')}</p>
              ) : (
                fields.map((field) => (
                  <div className="diff-field" key={field.key}>
                    <div className="diff-flabel">{field.key}</div>
                    <div className="diff-vals">
                      {field.hasBefore ? (
                        <span
                          className="dval dval--before"
                          title={t('admin.auditLogs.changes.before')}
                        >
                          {formatValue(field.before)}
                        </span>
                      ) : (
                        <span className="dval dval--empty">
                          {t('admin.auditLogs.changes.empty')}
                        </span>
                      )}
                      <span className="diff-arrow" aria-hidden="true">
                        <Icon name="arrowRight" />
                      </span>
                      {field.hasAfter ? (
                        <span
                          className="dval dval--after"
                          title={t('admin.auditLogs.changes.after')}
                        >
                          {formatValue(field.after)}
                        </span>
                      ) : (
                        <span className="dval dval--empty">
                          {t('admin.auditLogs.changes.empty')}
                        </span>
                      )}
                    </div>
                  </div>
                ))
              )}
            </div>
          </aside>
        </>
      ) : null}
    </>
  )
}
