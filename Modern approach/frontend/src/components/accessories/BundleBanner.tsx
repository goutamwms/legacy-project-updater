import { cn } from '../../lib/utils'

interface BundleBannerProps {
  visible: boolean
}

export function BundleBanner({ visible }: BundleBannerProps) {
  return (
    <div
      className={cn(
        'rounded-lg border border-amber-400 bg-linear-135 from-amber-200 to-amber-300 px-4 py-3 text-sm text-amber-900 mb-5',
        visible ? 'block' : 'hidden'
      )}
    >
      🎉 <strong>Bundle Deal!</strong> Water Bottle + Bike Light = 10% off your entire order.
    </div>
  )
}
