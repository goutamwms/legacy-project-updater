import { describe, it, expect, vi, beforeEach } from 'vitest'
import { getVersion, getBikes, getAccessories, rentBike, submitOrder, resetAllData } from '../lib/api'
import type { BikeType } from '../types/bike'

const mockFetch = vi.fn()
globalThis.fetch = mockFetch

beforeEach(() => {
  mockFetch.mockReset()
})

function mockResponse(data: unknown, ok = true) {
  return Promise.resolve({
    ok,
    json: () => Promise.resolve(data),
    status: ok ? 200 : 500,
    statusText: ok ? 'OK' : 'Internal Server Error',
  } as Response)
}

describe('API functions', () => {
  it('getVersion returns version info', async () => {
    mockFetch.mockResolvedValue(mockResponse({ version: '1.0.0', api_version: 'v1', name: 'PedalPal API' }))
    const result = await getVersion()
    expect(mockFetch).toHaveBeenCalledWith(
      '/v1/handlers/version',
      expect.objectContaining({ headers: { 'Content-Type': 'application/json' } })
    )
    expect(result).toEqual({ version: '1.0.0', api_version: 'v1', name: 'PedalPal API' })
  })

  it.each<{ type: BikeType; data: Record<string, unknown>; url: string }>([
    { type: 'beach', data: { bike_id: 1 }, url: '/v1/handlers/bike?action=beach' },
    { type: 'mountain', data: { BikeID: 101 }, url: '/v1/handlers/bike?action=mountain' },
    { type: 'electric', data: { bike_id: 201 }, url: '/v1/handlers/bike?action=electric' },
  ])('getBikes($type) calls correct URL', async ({ type, data, url }) => {
    mockFetch.mockResolvedValue(mockResponse([data]))
    const result = await getBikes(type)
    expect(mockFetch).toHaveBeenCalledWith(url, expect.objectContaining({ headers: { 'Content-Type': 'application/json' } }))
    expect(result).toEqual([data])
  })

  it('getAccessories appends bikeType query param', async () => {
    mockFetch.mockResolvedValue(mockResponse([]))
    await getAccessories('beach')
    expect(mockFetch).toHaveBeenCalledWith(
      '/v1/handlers/accessory?bikeType=beach',
      expect.any(Object)
    )
  })

  it('rentBike sends POST with bikeType and bikeId', async () => {
    mockFetch.mockResolvedValue(mockResponse({ Success: true, Message: 'OK' }))
    const result = await rentBike('beach', 1)
    expect(mockFetch).toHaveBeenCalledWith(
      '/v1/handlers/bike?action=rent',
      expect.objectContaining({
        method: 'POST',
        body: JSON.stringify({ bikeType: 'beach', bikeId: 1 }),
      })
    )
    expect(result).toEqual({ Success: true, Message: 'OK' })
  })

  it('submitOrder sends POST with items', async () => {
    mockFetch.mockResolvedValue(mockResponse({ Success: true, TotalPrice: 5.98 }))
    const items = [{ AccessoryID: 1, Quantity: 2 }]
    const result = await submitOrder(items)
    expect(mockFetch).toHaveBeenCalledWith(
      '/v1/handlers/accessory',
      expect.objectContaining({
        method: 'POST',
        body: JSON.stringify(items),
      })
    )
    expect(result).toEqual({ Success: true, TotalPrice: 5.98 })
  })

  it('resetAllData sends POST', async () => {
    mockFetch.mockResolvedValue(mockResponse({ Success: true, Message: 'Reset done' }))
    const result = await resetAllData()
    expect(mockFetch).toHaveBeenCalledWith(
      '/v1/handlers/bike?action=reset',
      expect.objectContaining({ method: 'POST' })
    )
    expect(result).toEqual({ Success: true, Message: 'Reset done' })
  })

  it('throws on non-ok response', async () => {
    mockFetch.mockResolvedValue(mockResponse(null, false))
    await expect(getBikes('beach')).rejects.toThrow('Request failed: 500 Internal Server Error')
  })

  it('getAccessories passes bikeType correctly with special chars', async () => {
    mockFetch.mockResolvedValue(mockResponse([]))
    await getAccessories('mountain')
    expect(mockFetch).toHaveBeenCalledWith(
      '/v1/handlers/accessory?bikeType=mountain',
      expect.any(Object)
    )
  })
})
