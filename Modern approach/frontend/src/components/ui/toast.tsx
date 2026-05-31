import { useEffect, useState } from 'react'
import { cn } from '../../lib/utils'

interface ToastProps {
  message: string
  visible: boolean
  onClose: () => void
  duration?: number
}

export function Toast({ message, visible, onClose, duration = 3000 }: ToastProps) {
  const [showing, setShowing] = useState(false)

  useEffect(() => {
    if (visible) {
      setShowing(true)
      const timer = setTimeout(() => {
        setShowing(false)
        setTimeout(onClose, 300)
      }, duration)
      return () => clearTimeout(timer)
    }
    setShowing(false)
  }, [visible, duration, onClose])

  return (
    <div
      role="status"
      aria-live="polite"
      aria-atomic="true"
      className={cn(
        'fixed bottom-8 left-1/2 z-50 -translate-x-1/2 rounded-lg bg-black/85 px-6 py-3 text-sm text-white shadow-lg transition-all duration-300',
        showing ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0 pointer-events-none'
      )}
    >
      {message}
    </div>
  )
}
