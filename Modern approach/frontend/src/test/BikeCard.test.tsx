import { describe, it, expect, vi } from 'vitest'
import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { BikeCard } from '../components/bikes/BikeCard'

const beachBike = {
  bike_id: 1,
  model_name: 'Sunset Drifter',
  color: 'Coral',
  frame_size: 'Medium',
  daily_rate: 14.99,
  is_available: true,
}

const electricBike = {
  bike_id: 201,
  model_name: 'Volt Rider',
  brand: 'EcoMotion',
  battery_range_km: 80,
  motor_power_w: 500,
  daily_rate: 29.99,
  is_available: true,
  weight_kg: 22.5,
  charge_time_h: 4.5,
}

const mountainBike = {
  BikeID: 101,
  ModelName: 'TrailBlazer X9',
  Brand: 'ApexRide',
  GearCount: 21,
  SuspensionType: 'Full',
  FrameMaterial: 'Aluminum',
  DailyRate: 24.99,
  IsAvailable: true,
  Terrain: 'All-Mountain',
  WeightKg: 13.5,
}

describe('BikeCard', () => {
  it('renders beach cruiser details', () => {
    render(<BikeCard bike={beachBike} bikeType="beach" onRent={vi.fn()} />)
    expect(screen.getByText('Sunset Drifter')).toBeInTheDocument()
    expect(screen.getByText('Available')).toBeInTheDocument()
    expect(screen.getByText('Coral')).toBeInTheDocument()
    expect(screen.getByText('Medium')).toBeInTheDocument()
    expect(screen.getByText(/14\.99/)).toBeInTheDocument()
  })

  it('renders mountain bike details', () => {
    render(<BikeCard bike={mountainBike} bikeType="mountain" onRent={vi.fn()} />)
    expect(screen.getByText('TrailBlazer X9')).toBeInTheDocument()
    expect(screen.getByText('Available')).toBeInTheDocument()
    expect(screen.getByText('ApexRide')).toBeInTheDocument()
    expect(screen.getByText('Full')).toBeInTheDocument()
    expect(screen.getByText(/24\.99/)).toBeInTheDocument()
    expect(screen.getByText('13.5 kg')).toBeInTheDocument()
  })

  it('shows rented badge when unavailable', () => {
    render(
      <BikeCard
        bike={{ ...beachBike, is_available: false }}
        bikeType="beach"
        onRent={vi.fn()}
      />
    )
    expect(screen.getByText('Rented')).toBeInTheDocument()
    expect(screen.getByText('Not Available')).toBeDisabled()
  })

  it('calls onRent when rent button is clicked', async () => {
    const onRent = vi.fn()
    const user = userEvent.setup()
    render(<BikeCard bike={beachBike} bikeType="beach" onRent={onRent} />)
    await user.click(screen.getByText('Rent This Bike'))
    expect(onRent).toHaveBeenCalledWith(1)
  })

  it('renders electric bike details', () => {
    render(<BikeCard bike={electricBike} bikeType="electric" onRent={vi.fn()} />)
    expect(screen.getByText('Volt Rider')).toBeInTheDocument()
    expect(screen.getByText('Available')).toBeInTheDocument()
    expect(screen.getByText('EcoMotion')).toBeInTheDocument()
    expect(screen.getByText('80 km')).toBeInTheDocument()
    expect(screen.getByText('500 W')).toBeInTheDocument()
    expect(screen.getByText('22.5 kg')).toBeInTheDocument()
    expect(screen.getByText('4.5 h')).toBeInTheDocument()
    expect(screen.getByText(/29\.99/)).toBeInTheDocument()
  })

  it('shows rented badge when electric bike is unavailable', () => {
    render(
      <BikeCard
        bike={{ ...electricBike, is_available: false }}
        bikeType="electric"
        onRent={vi.fn()}
      />
    )
    expect(screen.getByText('Rented')).toBeInTheDocument()
    expect(screen.getByText('Not Available')).toBeDisabled()
  })

  it('disables button while processing', () => {
    render(
      <BikeCard bike={beachBike} bikeType="beach" onRent={vi.fn()} isProcessing />
    )
    expect(screen.getByText('Processing...')).toBeDisabled()
  })
})
