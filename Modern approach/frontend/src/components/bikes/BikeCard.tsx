import { memo } from 'react'
import type { BeachCruiser, MountainBike, ElectricBike, BikeType } from '../../types/bike'
import { BeachCruiserCard } from './BeachCruiserCard'
import { MountainBikeCard } from './MountainBikeCard'
import { ElectricBikeCard } from './ElectricBikeCard'

type BikeByType = {
  beach: BeachCruiser
  mountain: MountainBike
  electric: ElectricBike
}

type AnyBike = BikeByType[BikeType]

export interface BikeCardProps {
  bike: AnyBike
  bikeType: BikeType
  onRent: (bikeId: number) => void
  isProcessing?: boolean
}

function BikeCardInner({ bike, bikeType, onRent, isProcessing }: BikeCardProps) {
  switch (bikeType) {
    case 'beach':
      return <BeachCruiserCard bike={bike as BeachCruiser} onRent={onRent} isProcessing={isProcessing} />
    case 'mountain':
      return <MountainBikeCard bike={bike as MountainBike} onRent={onRent} isProcessing={isProcessing} />
    case 'electric':
      return <ElectricBikeCard bike={bike as ElectricBike} onRent={onRent} isProcessing={isProcessing} />
    default: {
      const unknownType: string = bikeType
      return (
        <div role="alert" className="p-4 text-red-600 border border-red-300 rounded-xl bg-red-50">
          <strong>Unknown bike type:</strong> &ldquo;{unknownType}&rdquo; does not belong to BikeType. Expected: &ldquo;beach&rdquo;, &ldquo;mountain&rdquo;, or &ldquo;electric&rdquo;.
        </div>
      )
    }
  }
}

export const BikeCard = memo(BikeCardInner)
