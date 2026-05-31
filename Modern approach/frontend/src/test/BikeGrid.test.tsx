import { describe, it, expect, vi } from 'vitest'
import { render, screen } from '@testing-library/react'
import { BikeGrid } from '../components/bikes/BikeGrid'
import type { BeachCruiser, ElectricBike } from '../types/bike'

const beachBikes: BeachCruiser[] = [
  { bike_id: 1, model_name: 'Beach A', color: 'Red', frame_size: 'M', daily_rate: 10, is_available: true },
  { bike_id: 2, model_name: 'Beach B', color: 'Blue', frame_size: 'L', daily_rate: 12, is_available: false },
]

const electricBikes: ElectricBike[] = [
  { bike_id: 201, model_name: 'E-Bike A', brand: 'Eco', battery_range_km: 80, motor_power_w: 500, daily_rate: 29.99, is_available: true, weight_kg: 22.5, charge_time_h: 4.5 },
]

describe('BikeGrid', () => {
  it('renders skeleton cards when loading', () => {
    render(<BikeGrid bikes={[]} bikeType="beach" onRent={vi.fn()} processingId={null} isLoading />)
    expect(screen.getByRole('status')).toBeInTheDocument()
    expect(screen.getByText('Loading bikes...')).toBeInTheDocument()
  })

  it('shows empty state when no bikes', () => {
    render(<BikeGrid bikes={[]} bikeType="beach" onRent={vi.fn()} processingId={null} />)
    expect(screen.getByText('No bikes found.')).toBeInTheDocument()
  })

  it('renders beach bike cards', () => {
    render(<BikeGrid bikes={beachBikes} bikeType="beach" onRent={vi.fn()} processingId={null} />)
    expect(screen.getByText('Beach A')).toBeInTheDocument()
    expect(screen.getByText('Beach B')).toBeInTheDocument()
    expect(screen.getByText('Rented')).toBeInTheDocument()
    expect(screen.getByText('Available')).toBeInTheDocument()
  })

  it('renders electric bike cards', () => {
    render(<BikeGrid bikes={electricBikes} bikeType="electric" onRent={vi.fn()} processingId={null} />)
    expect(screen.getByText('E-Bike A')).toBeInTheDocument()
    expect(screen.getByText('Eco')).toBeInTheDocument()
    expect(screen.getByText('500 W')).toBeInTheDocument()
    expect(screen.getByText('80 km')).toBeInTheDocument()
  })

  it('passes processingId to highlight active bike', () => {
    const { container } = render(
      <BikeGrid bikes={beachBikes} bikeType="beach" onRent={vi.fn()} processingId={1} />
    )
    const items = container.querySelectorAll('[role="listitem"]')
    expect(items.length).toBe(2)
  })

  it('has correct aria label for beach grid', () => {
    render(<BikeGrid bikes={beachBikes} bikeType="beach" onRent={vi.fn()} processingId={null} />)
    expect(screen.getByRole('list')).toHaveAttribute('aria-label', 'Beach cruiser bikes')
  })

  it('has correct aria label for electric grid', () => {
    render(<BikeGrid bikes={electricBikes} bikeType="electric" onRent={vi.fn()} processingId={null} />)
    expect(screen.getByRole('list')).toHaveAttribute('aria-label', 'Electric bikes')
  })
})
