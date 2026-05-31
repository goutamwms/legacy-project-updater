import { describe, it, expect } from 'vitest'
import { render, screen } from '@testing-library/react'
import { Badge } from '../components/ui/badge'

describe('Badge', () => {
  it('renders children text', () => {
    render(<Badge>Available</Badge>)
    expect(screen.getByText('Available')).toBeInTheDocument()
  })

  it('renders with default variant', () => {
    render(<Badge>Default</Badge>)
    expect(screen.getByText('Default')).toHaveClass('bg-gray-100', 'text-gray-700')
  })

  it('renders with available variant', () => {
    render(<Badge variant="available">Available</Badge>)
    const el = screen.getByText('Available')
    expect(el).toHaveClass('bg-green-100', 'text-green-700')
    expect(el).toHaveAttribute('aria-label', 'Available')
  })

  it('renders with rented variant', () => {
    render(<Badge variant="rented">Rented</Badge>)
    const el = screen.getByText('Rented')
    expect(el).toHaveClass('bg-red-100', 'text-red-700')
    expect(el).toHaveAttribute('aria-label', 'Rented')
  })

  it('does not set aria-label for default variant', () => {
    render(<Badge>Plain</Badge>)
    expect(screen.getByText('Plain')).not.toHaveAttribute('aria-label')
  })
})
