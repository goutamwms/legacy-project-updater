import type { BeachCruiser, MountainBike, ElectricBike, BikeType } from '../types/bike'

type BikeTypeMap = {
  beach: BeachCruiser
  mountain: MountainBike
  electric: ElectricBike
}

const BASE_URL = '/v1/handlers'

async function request<T>(url: string, options?: RequestInit): Promise<T> {
  const res = await fetch(`${BASE_URL}${url}`, {
    headers: { 'Content-Type': 'application/json' },
    ...options,
  })

  if (!res.ok) {
    throw new Error(`Request failed: ${res.status} ${res.statusText}`)
  }

  return res.json()
}

export function getVersion() {
  return request<{ version: string; api_version: string; name: string }>(
    '/version'
  )
}

export function getBikes<T extends BikeType>(type: T): Promise<BikeTypeMap[T][]> {
  return request<BikeTypeMap[T][]>(`/bike?action=${type}`)
}

export function getAccessories(bikeType: string) {
  return request<import('../types/accessory').Accessory[]>(
    `/accessory?bikeType=${bikeType}`
  )
}

export function rentBike(bikeType: string, bikeId: number) {
  return request<{ Success: boolean; Message: string }>(
    '/bike?action=rent',
    {
      method: 'POST',
      body: JSON.stringify({ bikeType, bikeId }),
    }
  )
}

export function submitOrder(items: { AccessoryID: number; Quantity: number }[]) {
  return request<import('../types/accessory').OrderResponse>(
    '/accessory',
    {
      method: 'POST',
      body: JSON.stringify(items),
    }
  )
}

export function resetAllData() {
  return request<{ Success: boolean; Message: string }>(
    '/bike?action=reset',
    { method: 'POST' }
  )
}
