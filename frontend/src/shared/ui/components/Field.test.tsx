import { render, screen } from '@testing-library/react'
import { describe, expect, it } from 'vitest'
import { Field } from './Field'

describe('Field', () => {
  it('wires the label to the control via a shared id', () => {
    render(
      <Field label="Email">{({ id }) => <input id={id} aria-label="Email" type="email" />}</Field>,
    )

    const input = screen.getByLabelText('Email')
    expect(input).toHaveAttribute('id')
  })

  it('exposes invalid=false and hides the error when none is given', () => {
    render(
      <Field label="Email">
        {({ invalid }) => <input aria-label="Email" data-invalid={invalid} />}
      </Field>,
    )

    expect(screen.getByLabelText('Email')).toHaveAttribute('data-invalid', 'false')
    expect(screen.queryByRole('alert')).not.toBeInTheDocument()
  })

  it('shows the error as an alert and marks the control invalid', () => {
    render(
      <Field label="Email" error="Email is required.">
        {({ invalid }) => <input aria-label="Email" data-invalid={invalid} />}
      </Field>,
    )

    expect(screen.getByLabelText('Email')).toHaveAttribute('data-invalid', 'true')
    expect(screen.getByRole('alert')).toHaveTextContent('Email is required.')
  })
})
