import { describe, it, expect, vi } from 'vitest'
import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { Button } from '../components/ui/button'

describe('Button', () => {
  it('renders children text', () => {
    render(<Button>Click Me</Button>)
    expect(screen.getByText('Click Me')).toBeInTheDocument()
  })

  it('calls onClick when clicked', async () => {
    const onClick = vi.fn()
    const user = userEvent.setup()
    render(<Button onClick={onClick}>Click</Button>)
    await user.click(screen.getByText('Click'))
    expect(onClick).toHaveBeenCalledOnce()
  })

  it('disables when disabled prop is true', () => {
    render(<Button disabled>Disabled</Button>)
    expect(screen.getByText('Disabled')).toBeDisabled()
  })

  it('disables when loading prop is true', () => {
    render(<Button loading>Loading</Button>)
    expect(screen.getByText('Loading')).toBeDisabled()
  })

  it('sets aria-busy when loading', () => {
    render(<Button loading>Busy</Button>)
    expect(screen.getByText('Busy')).toHaveAttribute('aria-busy', 'true')
  })

  it('renders spinner when loading', () => {
    const { container } = render(<Button loading>Busy</Button>)
    const spinner = container.querySelector('span.animate-spin-slow')
    expect(spinner).toBeInTheDocument()
    expect(spinner).toHaveAttribute('aria-hidden', 'true')
  })

  it('does not render spinner when not loading', () => {
    const { container } = render(<Button>Normal</Button>)
    expect(container.querySelector('span.animate-spin-slow')).toBeNull()
  })

  it('applies variant classes', () => {
    const { rerender } = render(<Button variant="outline">Outline</Button>)
    expect(screen.getByText('Outline')).toHaveClass('border-2')

    rerender(<Button variant="ghost">Ghost</Button>)
    expect(screen.getByText('Ghost')).toHaveClass('text-gray-400')
  })

  it('does not call onClick when disabled', async () => {
    const onClick = vi.fn()
    const user = userEvent.setup()
    render(<Button onClick={onClick} disabled>Disabled</Button>)
    await user.click(screen.getByText('Disabled'))
    expect(onClick).not.toHaveBeenCalled()
  })

  it('forwards additional html attributes', () => {
    render(<Button data-testid="my-btn">Test</Button>)
    expect(screen.getByTestId('my-btn')).toBeInTheDocument()
  })
})
