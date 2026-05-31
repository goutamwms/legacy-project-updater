import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import { getBikes, rentBike, resetAllData } from '../lib/api'
import type { BikeType } from '../types/bike'

export function useBikes(bikeType: BikeType) {
  return useQuery({
    queryKey: ['bikes', bikeType],
    queryFn: () => getBikes(bikeType),
  })
}

export function useRentBike() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: ({ bikeType, bikeId }: { bikeType: string; bikeId: number }) =>
      rentBike(bikeType, bikeId),
    onSuccess: (_data, variables) => {
      queryClient.invalidateQueries({ queryKey: ['bikes', variables.bikeType] })
    },
  })
}

export function useResetData() {
  const queryClient = useQueryClient()

  return useMutation({
    mutationFn: resetAllData,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['bikes'] })
      queryClient.invalidateQueries({ queryKey: ['accessories'] })
    },
  })
}
