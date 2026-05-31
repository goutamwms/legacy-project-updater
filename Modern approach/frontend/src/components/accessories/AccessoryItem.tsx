import { memo } from 'react'
import { Minus, Plus } from 'lucide-react'
import type { Accessory } from '../../types/accessory'

interface AccessoryItemProps {
  accessory: Accessory
  quantity: number
  onAdjust: (id: number, delta: number) => void
}

function AccessoryItemInner({ accessory, quantity, onAdjust }: AccessoryItemProps) {
  const outOfStock = accessory.StockCount === 0
  const stockNote = outOfStock
    ? 'Out of stock'
    : `In stock: ${accessory.StockCount}`
  const atMax = quantity >= accessory.StockCount

  return (
    <div
      className="flex items-center gap-4 rounded-lg border border-gray-100 p-4 transition-all duration-200 hover:border-rose-200 animate-slide-in-right opacity-0 [animation-fill-mode:forwards]"
      role="group"
      aria-label={`${accessory.Name}, $${accessory.UnitPrice.toFixed(2)}. ${stockNote}`}
    >
      <div className="flex-1 min-w-0">
        <h4 className="font-semibold text-gray-800">{accessory.Name}</h4>
        <p className="text-xs text-gray-500">{accessory.Description}</p>
        <p className="text-xs text-gray-400 mt-0.5" aria-live="polite">
          {accessory.Category} &bull; {stockNote}
        </p>
      </div>
      <span className="font-bold text-rose-500 whitespace-nowrap" aria-label={`$${accessory.UnitPrice.toFixed(2)} each`}>
        ${accessory.UnitPrice.toFixed(2)}
      </span>
      <div className="flex items-center gap-2" role="spinbutton" aria-valuenow={quantity} aria-valuemin={0} aria-valuemax={accessory.StockCount} aria-label={`Quantity of ${accessory.Name}: ${quantity}`}>
        <button
          className="flex size-8 items-center justify-center rounded-full border-2 border-rose-500 text-rose-500 font-bold hover:bg-rose-500 hover:text-white transition-colors cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed"
          onClick={() => onAdjust(accessory.AccessoryID, -1)}
          disabled={quantity <= 0}
          aria-label={`Decrease ${accessory.Name} quantity`}
        >
          <Minus className="size-4" aria-hidden="true" />
        </button>
        <span className="min-w-[24px] text-center font-semibold" aria-hidden="true">{quantity}</span>
        <button
          className="flex size-8 items-center justify-center rounded-full border-2 border-rose-500 text-rose-500 font-bold hover:bg-rose-500 hover:text-white transition-colors cursor-pointer disabled:opacity-30 disabled:cursor-not-allowed"
          onClick={() => onAdjust(accessory.AccessoryID, 1)}
          disabled={outOfStock || atMax}
          aria-label={`Increase ${accessory.Name} quantity`}
        >
          <Plus className="size-4" aria-hidden="true" />
        </button>
      </div>
    </div>
  )
}

export const AccessoryItem = memo(AccessoryItemInner)
