import { memo } from 'react'
import type { MountainBike } from '../../types/bike'
import { BikeCardLayout } from './BikeCardLayout'
import { DetailRow } from './DetailRow'

const style = { theme: 'from-blue-400 to-cyan-300', priceColor: 'text-blue-600' } as const

interface MountainBikeCardProps {
  bike: MountainBike
  onRent: (bikeId: number) => void
  isProcessing?: boolean
}

function MountainBikeCardInner({ bike, onRent, isProcessing }: MountainBikeCardProps) {
  return (
    <BikeCardLayout
      modelName={bike.ModelName}
      isAvailable={bike.IsAvailable}
      dailyRate={bike.DailyRate}
      bikeId={bike.BikeID}
      theme={style.theme}
      priceColor={style.priceColor}
      onRent={onRent}
      isProcessing={isProcessing}
    >
      <DetailRow label="Brand" value={bike.Brand} />
      <DetailRow label="Suspension" value={bike.SuspensionType} />
      <DetailRow label="Frame" value={bike.FrameMaterial} />
      <DetailRow label="Gears" value={String(bike.GearCount)} />
      <DetailRow label="Terrain" value={bike.Terrain} />
      <DetailRow label="Weight" value={`${bike.WeightKg} kg`} />
    </BikeCardLayout>
  )
}

export const MountainBikeCard = memo(MountainBikeCardInner)
