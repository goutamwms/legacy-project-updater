import { BikeCard } from './BikeCard'
import type { BeachCruiser, MountainBike, ElectricBike, BikeType } from '../../types/bike'

type AnyBike = BeachCruiser | MountainBike | ElectricBike

interface BikeGridProps {
  bikes: AnyBike[]
  bikeType: BikeType
  onRent: (bikeId: number) => void
  processingId: number | null
  isLoading?: boolean
}

const bikeIdAccessors: Record<BikeType, (bike: AnyBike) => number> = {
  beach: b => (b as BeachCruiser).bike_id,
  mountain: b => (b as MountainBike).BikeID,
  electric: b => (b as ElectricBike).bike_id,
}

const gridLabels: Record<BikeType, string> = {
  beach: 'Beach cruiser',
  mountain: 'Mountain',
  electric: 'Electric',
}

function SkeletonCard() {
  return (
    <div className="w-72 animate-pulse" aria-hidden="true">
      <div className="rounded-xl bg-white/80 p-7 shadow-lg">
        <div className="mb-3 h-6 w-3/4 rounded bg-gray-200" />
        <div className="mb-4 h-5 w-20 rounded-full bg-gray-200" />
        <div className="space-y-2">
          <div className="h-4 w-full rounded bg-gray-100" />
          <div className="h-4 w-full rounded bg-gray-100" />
          <div className="mt-3 h-7 w-24 rounded bg-gray-200" />
        </div>
        <div className="mt-4 h-11 w-full rounded-lg bg-gray-200" />
      </div>
    </div>
  )
}

export function BikeGrid({ bikes, bikeType, onRent, processingId, isLoading }: BikeGridProps) {
  const getId = bikeIdAccessors[bikeType]
  const label = gridLabels[bikeType]

  if (!getId || !label) {
    return (
      <div role="alert" className="p-4 text-red-600 border border-red-300 rounded-xl bg-red-50">
        <strong>Unknown bike type:</strong> &ldquo;{bikeType}&rdquo; does not belong to BikeType. Expected: &ldquo;beach&rdquo;, &ldquo;mountain&rdquo;, or &ldquo;electric&rdquo;.
      </div>
    )
  }

  if (isLoading) {
    return (
      <div className="flex flex-wrap justify-center gap-6 max-w-5xl mx-auto" role="status" aria-label="Loading bikes">
        <SkeletonCard />
        <SkeletonCard />
        <SkeletonCard />
        <span className="sr-only">Loading bikes...</span>
      </div>
    )
  }

  if (!bikes.length) {
    return (
      <div className="py-16 text-center text-white/80 text-lg" role="status">
        No bikes found.
      </div>
    )
  }

  return (
    <div className="flex flex-wrap justify-center gap-6 max-w-5xl mx-auto" role="list" aria-label={`${label} bikes`}>
      {bikes.map((bike, index) => {
        const bikeId = getId(bike)
        return (
          <div
            key={bikeId}
            className="w-72 animate-slide-up opacity-0 [animation-fill-mode:forwards]"
            style={{ animationDelay: `${index * 80}ms` }}
            role="listitem"
          >
            <BikeCard
              bike={bike}
              bikeType={bikeType}
              onRent={onRent}
              isProcessing={processingId === bikeId}
            />
          </div>
        )
      })}
    </div>
  )
}
