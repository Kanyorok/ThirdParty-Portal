export interface PropertyType {
    id: number
    propertyTypeName: string
}

export interface Property {
    id: number
    propertyName: string
    propertyCode: string
    category: string
    owner: string
    acquisitionDate: string
    address: string
    locationId: string
    type: PropertyType
    status: string
    description: string
}