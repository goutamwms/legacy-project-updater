import { useState, useCallback, useEffect } from 'react'
import {
  Dialog, DialogContent, DialogTitle, DialogDescription,
} from '../ui/dialog'
import { Button } from '../ui/button'
import { AccessoryItem } from './AccessoryItem'
import { BundleBanner } from './BundleBanner'
import { useAccessories, useSubmitOrder } from '../../hooks/useAccessories'
import type { Accessory } from '../../types/accessory'

const BUNDLE_IDS = [1, 3]
const DISCOUNT_RATE = 0.10

interface AccessoryModalProps {
  bikeType: string
  open: boolean
  onClose: () => void
  onOrderComplete: () => void
}

export function AccessoryModal({ bikeType, open, onClose, onOrderComplete }: AccessoryModalProps) {
  const { data: accessories = [], isLoading } = useAccessories(open ? bikeType : '')
  const submitOrder = useSubmitOrder()
  const [quantities, setQuantities] = useState<Record<number, number>>({})
  const [showSuccess, setShowSuccess] = useState(false)
  const [successDetail, setSuccessDetail] = useState('')

  useEffect(() => {
    if (open) {
      setQuantities({})
      setShowSuccess(false)
      setSuccessDetail('')
    }
  }, [open])

  const adjustQty = useCallback((id: number, delta: number) => {
    setQuantities((prev) => {
      const current = prev[id] ?? 0
      const next = Math.max(0, Math.min(current + delta, getStock(id, accessories)))
      return { ...prev, [id]: next }
    })
  }, [accessories])

  const hasItems = Object.values(quantities).some((q) => q > 0)

  const subtotal = accessories.reduce((sum, acc) => {
    const qty = quantities[acc.AccessoryID] ?? 0
    return sum + acc.UnitPrice * qty
  }, 0)

  const hasBundle = accessories.some(
    (a) => a.AccessoryID === BUNDLE_IDS[0] && (quantities[a.AccessoryID] ?? 0) > 0
  ) && accessories.some(
    (a) => a.AccessoryID === BUNDLE_IDS[1] && (quantities[a.AccessoryID] ?? 0) > 0
  )

  const discount = hasBundle ? Math.round(subtotal * DISCOUNT_RATE * 100) / 100 : 0
  const total = Math.round((subtotal - discount) * 100) / 100

  const handleConfirm = useCallback(async () => {
    const items = accessories
      .filter((a) => (quantities[a.AccessoryID] ?? 0) > 0)
      .map((a) => ({ AccessoryID: a.AccessoryID, Quantity: quantities[a.AccessoryID] }))

    if (items.length === 0) {
      onClose()
      return
    }

    try {
      const result = await submitOrder.mutateAsync(items)
      if (result.Success) {
        const detail = result.BundleDiscountApplied
          ? `Total: $${result.TotalPrice.toFixed(2)} (saved $${result.DiscountAmount.toFixed(2)})`
          : `Total: $${result.TotalPrice.toFixed(2)}`
        setSuccessDetail(detail)
        setShowSuccess(true)
        setTimeout(() => {
          onClose()
          onOrderComplete()
        }, 2500)
      }
    } catch {
      alert('Order failed. Check the server is running.')
    }
  }, [accessories, quantities, submitOrder, onClose, onOrderComplete])

  return (
    <Dialog open={open} onOpenChange={(o) => { if (!o) { onClose() } }}>
      <DialogContent>
        <DialogTitle>Add Accessories</DialogTitle>
        <DialogDescription>Rented! Would you like to add anything?</DialogDescription>

        <BundleBanner visible={hasBundle} />

        {isLoading && (
          <div className="py-10 flex flex-col items-center gap-4" role="status" aria-label="Loading accessories">
            <span className="inline-block size-8 rounded-full border-4 border-rose-200 border-t-rose-500 animate-spin-slow" aria-hidden="true" />
            <span className="text-gray-400 text-sm">Loading accessories...</span>
          </div>
        )}

        {!isLoading && accessories.length === 0 && (
          <p className="py-10 text-center text-gray-400" role="status">No accessories available.</p>
        )}

        {!isLoading && accessories.map((acc, index) => (
          <div
            key={acc.AccessoryID}
            className="animate-slide-up opacity-0 [animation-fill-mode:forwards]"
            style={{ animationDelay: `${index * 60}ms` }}
          >
            <AccessoryItem
              accessory={acc}
              quantity={quantities[acc.AccessoryID] ?? 0}
              onAdjust={adjustQty}
            />
          </div>
        ))}

        {!showSuccess && hasItems && (
          <div className="border-t border-gray-100 pt-4 mt-2 space-y-2">
            {subtotal > 0 && (
              <div className="flex justify-between text-sm text-gray-500">
                <span>Subtotal</span>
                <span>${subtotal.toFixed(2)}</span>
              </div>
            )}
            {hasBundle && (
              <div className="flex justify-between text-sm text-green-600">
                <span>Bundle Discount (10%)</span>
                <span>-${discount.toFixed(2)}</span>
              </div>
            )}
            <div className="flex justify-between text-lg font-bold text-gray-800 pt-2 border-t border-gray-100">
              <span>Total</span>
              <span>${total.toFixed(2)}</span>
            </div>
          </div>
        )}

        {!showSuccess && (
          <>
            <Button
              className="bg-linear-135 from-pink-400 to-rose-400 mt-5 w-full"
              disabled={!hasItems || submitOrder.isPending}
              loading={submitOrder.isPending}
              onClick={handleConfirm}
            >
              {submitOrder.isPending ? 'Processing...' : 'Confirm Order'}
            </Button>
            <Button variant="outline" className="mt-2 w-full" onClick={() => { onClose(); onOrderComplete() }}>
              No thanks, just the bike
            </Button>
          </>
        )}

        {showSuccess && (
          <div className="py-6 text-center animate-fade-in" role="alert" aria-live="assertive">
            <div className="text-4xl mb-2" aria-hidden="true">🎉</div>
            <h3 className="text-lg font-bold text-green-600 mb-1">You're all set!</h3>
            <p className="text-sm text-gray-500">{successDetail}</p>
          </div>
        )}
      </DialogContent>
    </Dialog>
  )
}

function getStock(id: number, accessories: Accessory[]): number {
  return accessories.find((a) => a.AccessoryID === id)?.StockCount ?? 0
}
