import { type HTMLAttributes } from 'react'
import { cva, type VariantProps } from 'class-variance-authority'
import { cn } from '../../lib/utils'

const badgeVariants = cva(
  'inline-block rounded-full px-3 py-1 text-xs font-semibold',
  {
    variants: {
      variant: {
        available: 'bg-green-100 text-green-700',
        rented: 'bg-red-100 text-red-700',
        default: 'bg-gray-100 text-gray-700',
      },
    },
    defaultVariants: { variant: 'default' },
  }
)

interface BadgeProps
  extends HTMLAttributes<HTMLSpanElement>,
    VariantProps<typeof badgeVariants> {}

function Badge({ className, variant, ...props }: BadgeProps) {
  return (
    <span
      className={cn(badgeVariants({ variant }), className)}
      aria-label={variant === 'available' ? 'Available' : variant === 'rented' ? 'Rented' : undefined}
      {...props}
    />
  )
}

export { Badge, badgeVariants }
