import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { getAccessories, submitOrder } from '../lib/api'
import type { Accessory, OrderResponse } from '../types/accessory'

export function useAccessories(bikeType: string) {
  return useQuery<Accessory[]>({
    queryKey: ['accessories', bikeType],
    queryFn: () => getAccessories(bikeType),
    enabled: !!bikeType,
  })
}

export function useSubmitOrder() {
  const queryClient = useQueryClient()

  return useMutation<OrderResponse, Error, { AccessoryID: number; Quantity: number }[]>({
    mutationFn: submitOrder,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['accessories'] })
    },
  })
}
