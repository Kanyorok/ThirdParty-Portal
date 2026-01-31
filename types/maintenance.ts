export interface MaintenanceRequest {
    id: number;
    ticketNumber: string;
    title: string;
    description: string;
    status: 'Open' | 'In Progress' | 'Resolved' | 'Closed' | 'Pending';
    priority: 'Low' | 'Medium' | 'High' | 'Emergency';
    category: string;
    propertyId: number;
    unitId?: number;
    propertyName: string;
    unitName?: string;
    requestedDate: string;
    scheduledDate?: string;
    completedDate?: string;
    technicianName?: string;
    images?: string[];
    updates?: MaintenanceUpdate[];
}

export interface MaintenanceUpdate {
    id: number;
    status: string;
    comment: string;
    updatedBy: string;
    updatedAt: string;
}

export interface CreateMaintenanceRequestPayload {
    title: string;
    description: string;
    priority: string;
    category: string;
    propertyId: number;
    unitId?: number;
    images?: File[];
}

export const MAINTENANCE_CATEGORIES = [
    "Plumbing",
    "Electrical",
    "HVAC",
    "Appliance",
    "Structural",
    "Pest Control",
    "Other"
];

export const PRIORITY_LEVELS = [
    { value: "Low", label: "Low", color: "bg-slate-500" },
    { value: "Medium", label: "Medium", color: "bg-blue-500" },
    { value: "High", label: "High", color: "bg-orange-500" },
    { value: "Emergency", label: "Emergency", color: "bg-red-500" },
];