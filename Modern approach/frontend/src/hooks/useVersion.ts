import { useQuery } from '@tanstack/react-query'
import { getVersion } from '../lib/api'

export function useVersion() {
  return useQuery({
    queryKey: ['version'],
    queryFn: getVersion,
    staleTime: Infinity,
  })
}
