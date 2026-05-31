import { memo } from 'react'
import type { BeachCruiser } from '../../types/bike'
import { BikeCardLayout } from './BikeCardLayout'
import { DetailRow } from './DetailRow'

const style = { theme: 'from-pink-400 to-rose-400', priceColor: 'text-rose-500' } as const

interface BeachCruiserCardProps {
  bike: BeachCruiser
  onRent: (bikeId: number) => void
  isProcessing?: boolean
}

function BeachCruiserCardInner({ bike, onRent, isProcessing }: BeachCruiserCardProps) {
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
      <DetailRow label="Color" value={bike.color} />
      <DetailRow label="Frame Size" value={bike.frame_size} />
    </BikeCardLayout>
  )
}

export const BeachCruiserCard = memo(BeachCruiserCardInner)
