import { useState, useCallback } from 'react'
import { Link } from '@tanstack/react-router'
import { ArrowLeft } from 'lucide-react'
import { Header } from '../components/layout/Header'
import { Footer } from '../components/layout/Footer'
import { BikeGrid } from '../components/bikes/BikeGrid'
import { AccessoryModal } from '../components/accessories/AccessoryModal'
import { useBikes, useRentBike } from '../hooks/useBikes'
import type { BikeType } from '../types/bike'

interface BikeListPageProps {
  bikeType: BikeType
}

export function BikeListPage({ bikeType }: BikeListPageProps) {
  const { data: bikes = [], isLoading } = useBikes(bikeType)
  const rentBike = useRentBike()

  const [processingId, setProcessingId] = useState<number | null>(null)
  const [modalOpen, setModalOpen] = useState(false)

  const isBeach = bikeType === 'beach'
  const isElectric = bikeType === 'electric'
  const themeBg = isBeach
    ? 'from-pink-300 to-rose-400'
    : isElectric
      ? 'from-purple-500 to-violet-600'
      : 'from-blue-400 to-cyan-300'
  const themeSubtitle = isBeach
    ? 'Slow down. Feel the breeze.'
    : isElectric
      ? 'Silent power. Endless range.'
      : 'More gears than you will use. More suspension than you need.'
  const backPath = '/'

  const handleRent = useCallback(async (bikeId: number) => {
    setProcessingId(bikeId)
    try {
      const result = await rentBike.mutateAsync({ bikeType, bikeId })
      if (result.Success) {
        setModalOpen(true)
      } else {
        alert(result.Message)
      }
    } catch {
      alert('Failed to rent bike. Check the server is running.')
    } finally {
      setProcessingId(null)
    }
  }, [bikeType, rentBike])

  return (
    <div className={`min-h-screen bg-linear-135 ${themeBg} px-5 py-8 animate-fade-in`}>
      <Link to={backPath} className="inline-flex items-center gap-1 text-white/80 hover:text-white no-underline mb-5 text-sm">
        <ArrowLeft className="size-4" aria-hidden="true" /> Back to PedalPal
      </Link>

      <Header subtitle={themeSubtitle} />

      <BikeGrid
        bikes={bikes}
        bikeType={bikeType}
        onRent={handleRent}
        processingId={processingId}
        isLoading={isLoading}
      />

      <AccessoryModal
        bikeType={bikeType}
        open={modalOpen}
        onClose={() => setModalOpen(false)}
        onOrderComplete={() => setModalOpen(false)}
      />

      <Footer />
    </div>
  )
}
