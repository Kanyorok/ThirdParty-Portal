export type Round = {
    id: string // RoundID
    title: string
    status: "O" | "CL";
    startDate: string
    endDate: string
    maxVendors: number
}
