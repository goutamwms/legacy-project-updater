import { memo } from 'react'
import type { ElectricBike } from '../../types/bike'
import { BikeCardLayout } from './BikeCardLayout'
import { DetailRow } from './DetailRow'

const style = { theme: 'from-purple-500 to-violet-600', priceColor: 'text-violet-600' } as const

interface ElectricBikeCardProps {
  bike: ElectricBike
  onRent: (bikeId: number) => void
  isProcessing?: boolean
}

function ElectricBikeCardInner({ bike, onRent, isProcessing }: ElectricBikeCardProps) {
  return (
    <BikeCardLayout
      modelName={bike.model_name}
      isAvailable={bike.is_available}
      dailyRate={bike.daily_rate}
      bikeId={bike.bike_id}
      theme={style.theme}
      priceColor={style.priceColor}
      onRent={onRent}
      isProcessing={isProcessing}
    >
      <DetailRow label="Brand" value={bike.brand} />
      <DetailRow label="Range" value={`${bike.battery_range_km} km`} />
      <DetailRow label="Motor" value={`${bike.motor_power_w} W`} />
      <DetailRow label="Weight" value={`${bike.weight_kg} kg`} />
      <DetailRow label="Charge Time" value={`${bike.charge_time_h} h`} />
    </BikeCardLayout>
  )
}

export const ElectricBikeCard = memo(ElectricBikeCardInner)
