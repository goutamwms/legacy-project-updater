export interface BeachCruiser {
  bike_id: number
  model_name: string
  color: string
  frame_size: string
  daily_rate: number
  is_available: boolean
}

export interface MountainBike {
  BikeID: number
  ModelName: string
  Brand: string
  GearCount: number
  SuspensionType: string
  FrameMaterial: string
  DailyRate: number
  IsAvailable: boolean
  Terrain: string
  WeightKg: number
}

export interface ElectricBike {
  bike_id: number
  model_name: string
  brand: string
  battery_range_km: number
  motor_power_w: number
  daily_rate: number
  is_available: boolean
  weight_kg: number
  charge_time_h: number
}

export type BikeType = 'beach' | 'mountain' | 'electric'
