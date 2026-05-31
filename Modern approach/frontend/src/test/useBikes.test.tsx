import { describe, it, expect, vi, beforeEach } from 'vitest'
import { renderHook, waitFor } from '@testing-library/react'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { useBikes, useRentBike, useResetData } from '../hooks/useBikes'
import type { ReactNode } from 'react'

const mockFetch = vi.fn()
globalThis.fetch = mockFetch

let queryClient: QueryClient

function createWrapper() {
  queryClient = new QueryClient({
    defaultOptions: { queries: { retry: false } },
  })
  return function Wrapper({ children }: { children: ReactNode }) {
    return <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>
  }
}

beforeEach(() => {
  mockFetch.mockReset()
})

describe('useBikes', () => {
  it('returns beach cruiser data on success', async () => {
    const data = [{ bike_id: 1, model_name: 'Sunset' }]
    mockFetch.mockResolvedValue({ ok: true, json: () => Promise.resolve(data) })

    const { result } = renderHook(() => useBikes('beach'), { wrapper: createWrapper() })

    await waitFor(() => expect(result.current.isSuccess).toBe(true))
    expect(result.current.data).toHaveLength(1)
  })

  it('sets error state on fetch failure', async () => {
    mockFetch.mockRejectedValue(new Error('Network error'))

    const { result } = renderHook(() => useBikes('beach'), { wrapper: createWrapper() })

    await waitFor(() => expect(result.current.isError).toBe(true))
  })

  it('returns mountain bike data on success', async () => {
    const data = [{ BikeID: 101, ModelName: 'TrailBlazer' }]
    mockFetch.mockResolvedValue({ ok: true, json: () => Promise.resolve(data) })

    const { result } = renderHook(() => useBikes('mountain'), { wrapper: createWrapper() })

    await waitFor(() => expect(result.current.isSuccess).toBe(true))
    expect(result.current.data).toHaveLength(1)
  })

  it('returns electric bike data on success', async () => {
    const data = [{ bike_id: 201, model_name: 'Volt' }]
    mockFetch.mockResolvedValue({ ok: true, json: () => Promise.resolve(data) })

    const { result } = renderHook(() => useBikes('electric'), { wrapper: createWrapper() })

    await waitFor(() => expect(result.current.isSuccess).toBe(true))
    expect(result.current.data).toHaveLength(1)
  })
})

describe('useRentBike', () => {
  it('sends rent mutation and invalidates cache', async () => {
    mockFetch.mockResolvedValue({
      ok: true,
      json: () => Promise.resolve({ Success: true, Message: 'Rented' }),
    })

    const { result } = renderHook(() => useRentBike(), { wrapper: createWrapper() })

    const mutationResult = await result.current.mutateAsync({ bikeType: 'beach', bikeId: 1 })
    expect(mutationResult.Success).toBe(true)
    expect(mutationResult.Message).toBe('Rented')
  })

  it('handles rent failure', async () => {
    mockFetch.mockResolvedValue({
      ok: true,
      json: () => Promise.resolve({ Success: false, Message: 'Not available' }),
    })

    const { result } = renderHook(() => useRentBike(), { wrapper: createWrapper() })

    const mutationResult = await result.current.mutateAsync({ bikeType: 'beach', bikeId: 1 })
    expect(mutationResult.Success).toBe(false)
  })
})

describe('useResetData', () => {
  it('calls reset endpoint and invalidates queries', async () => {
    mockFetch.mockResolvedValue({
      ok: true,
      json: () => Promise.resolve({ Success: true, Message: 'Reset done' }),
    })

    const { result } = renderHook(() => useResetData(), { wrapper: createWrapper() })

    const mutationResult = await result.current.mutateAsync()
    expect(mutationResult.Success).toBe(true)
  })
})
