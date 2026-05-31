import type { ReactNode } from 'react'
import { Card, CardHeader, CardTitle, CardContent } from '../ui/card'
import { Badge } from '../ui/badge'
import { Button } from '../ui/button'

interface BikeCardLayoutProps {
  modelName: string
  isAvailable: boolean
  dailyRate: number
  theme: string
  priceColor: string
  bikeId: number
  onRent: (bikeId: number) => void
  isProcessing?: boolean
  children: ReactNode
}

export function BikeCardLayout({
  modelName,
  isAvailable,
  dailyRate,
  theme,
  priceColor,
  bikeId,
  onRent,
  isProcessing,
  children,
}: BikeCardLayoutProps) {
  return (
    <article aria-label={`${modelName}, ${isAvailable ? 'available' : 'rented'}, $${dailyRate.toFixed(2)} per day`}>
      <Card>
        <CardHeader>
          <CardTitle>{modelName}</CardTitle>
          <Badge variant={isAvailable ? 'available' : 'rented'}>
            {isAvailable ? 'Available' : 'Rented'}
          </Badge>
        </CardHeader>
        <CardContent>
          {children}
          <div className={`text-2xl font-bold mt-3 ${priceColor}`}>
            ${dailyRate.toFixed(2)}<span className="text-sm font-normal text-gray-500">/day</span>
          </div>
        </CardContent>
        <Button
          className={`bg-linear-135 ${theme} mt-2 w-full`}
          disabled={!isAvailable || isProcessing}
          loading={isProcessing}
          onClick={() => onRent(bikeId)}
          aria-label={isProcessing ? `Processing rental for ${modelName}` : isAvailable ? `Rent ${modelName}` : `${modelName} is not available`}
        >
          {isProcessing ? 'Processing...' : isAvailable ? 'Rent This Bike' : 'Not Available'}
        </Button>
      </Card>
    </article>
  )
}
