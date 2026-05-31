import { forwardRef, type ButtonHTMLAttributes } from 'react'
import { cva, type VariantProps } from 'class-variance-authority'
import { cn } from '../../lib/utils'

const buttonVariants = cva(
  'inline-flex items-center justify-center gap-2 rounded-lg font-semibold transition-all duration-200 active:scale-97 disabled:pointer-events-none disabled:opacity-50 cursor-pointer',
  {
    variants: {
      variant: {
        default: 'text-white',
        outline: 'border-2 border-gray-300 bg-white text-gray-500 hover:border-gray-400 hover:text-gray-600',
        ghost: 'text-gray-400 hover:text-gray-600',
      },
      size: {
        default: 'px-8 py-3 text-base',
        sm: 'px-4 py-2 text-sm',
        lg: 'px-10 py-4 text-lg',
        icon: 'size-8',
      },
    },
    defaultVariants: { variant: 'default', size: 'default' },
  }
)

interface ButtonProps
  extends ButtonHTMLAttributes<HTMLButtonElement>,
    VariantProps<typeof buttonVariants> {
  loading?: boolean
}

const Button = forwardRef<HTMLButtonElement, ButtonProps>(
  ({ className, variant, size, loading, disabled, children, ...props }, ref) => (
    <button
      className={cn(buttonVariants({ variant, size, className }))}
      ref={ref}
      disabled={disabled || loading}
      aria-busy={loading}
      {...props}
    >
      {loading && (
        <span
          className="inline-block size-4 rounded-full border-2 border-white/30 border-t-white animate-spin-slow"
          aria-hidden="true"
        />
      )}
      {children}
    </button>
  )
)
Button.displayName = 'Button'

export { Button, buttonVariants }
