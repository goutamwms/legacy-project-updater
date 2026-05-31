import { describe, it, expect, vi } from 'vitest'
import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { AccessoryItem } from '../components/accessories/AccessoryItem'

const accessory = {
  AccessoryID: 1,
  Name: 'Water Bottle',
  Category: 'Hydration',
  Description: 'Stays cold',
  UnitPrice: 2.99,
  StockCount: 15,
  CompatibleWith: ['all'],
}

describe('AccessoryItem', () => {
  it('renders accessory details', () => {
    render(<AccessoryItem accessory={accessory} quantity={0} onAdjust={vi.fn()} />)
    expect(screen.getByText('Water Bottle')).toBeInTheDocument()
    expect(screen.getByText(/2\.99/)).toBeInTheDocument()
    expect(screen.getByText(/In stock: 15/)).toBeInTheDocument()
  })

  it('shows out of stock when count is 0', () => {
    render(
      <AccessoryItem
        accessory={{ ...accessory, StockCount: 0 }}
        quantity={0}
        onAdjust={vi.fn()}
      />
    )
    expect(screen.getByText(/Out of stock/)).toBeInTheDocument()
  })

  it('calls onAdjust with -1 when minus clicked', async () => {
    const onAdjust = vi.fn()
    const user = userEvent.setup()
    render(<AccessoryItem accessory={accessory} quantity={1} onAdjust={onAdjust} />)

    const minusBtn = screen.getAllByRole('button')[0]
    await user.click(minusBtn)
    expect(onAdjust).toHaveBeenCalledWith(1, -1)
  })

  it('calls onAdjust with +1 when plus clicked', async () => {
    const onAdjust = vi.fn()
    const user = userEvent.setup()
    render(<AccessoryItem accessory={accessory} quantity={0} onAdjust={onAdjust} />)

    const plusBtn = screen.getAllByRole('button')[1]
    await user.click(plusBtn)
    expect(onAdjust).toHaveBeenCalledWith(1, 1)
  })

  it('disables minus at quantity 0', () => {
    render(<AccessoryItem accessory={accessory} quantity={0} onAdjust={vi.fn()} />)
    const minusBtn = screen.getAllByRole('button')[0]
    expect(minusBtn).toBeDisabled()
  })

  it('disables plus at max stock', () => {
    render(<AccessoryItem accessory={accessory} quantity={15} onAdjust={vi.fn()} />)
    const plusBtn = screen.getAllByRole('button')[1]
    expect(plusBtn).toBeDisabled()
  })
})
