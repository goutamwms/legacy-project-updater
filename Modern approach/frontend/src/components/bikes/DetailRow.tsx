import { memo } from 'react'

interface DetailRowProps {
  label: string
  value: string
}

function DetailRowInner({ label, value }: DetailRowProps) {
  return (
    <div className="flex justify-between">
      <span className="text-gray-400">{label}</span>
      <span className="font-medium text-gray-700">{value}</span>
    </div>
  )
}

export const DetailRow = memo(DetailRowInner)
