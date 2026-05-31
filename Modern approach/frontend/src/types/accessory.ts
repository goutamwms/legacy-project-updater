export interface Accessory {
  AccessoryID: number
  Name: string
  Category: string
  Description: string
  UnitPrice: number
  StockCount: number
  CompatibleWith: string[]
}

export interface OrderItem {
  AccessoryID: number
  Quantity: number
}

export interface OrderResponse {
  Success: boolean
  Message: string
  TotalPrice: number
  DiscountAmount: number
  BundleDiscountApplied: boolean
}
