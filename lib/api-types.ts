export type RoundSummary = {
    id: string
    title: string
    description?: string
    deadline?: string // ISO
}

export type Criterion = {
    id: string
    label: string
    type: "text" | "textarea" | "number" | "date" | "select" | "checkbox" | "file"
    placeholder?: string
    required?: boolean
    options?: { label: string; value: string }[]
    description?: string
}

export type Section = {
    id: string
    title: string
    description?: string
    criteria: Criterion[]
}

export type RoundDetail = {
    id: string
    title: string
    description?: string
    deadline?: string
    sections: Section[]
}

export type ApplicationResponse = {
    success: boolean
    applicationId?: string
    message?: string
    errors?: Record<string, string>
}

export interface ApiErrorResponse {
    status: "error"
    message: string
    errors?: Record<string, string[]>
}

export interface RegisterSuccessResponse {
    message: string;
    userId: string;
    redirectUrl: string;
}

export interface CompanyRegisterSuccessResponse {
    message: string;
    third_party: {
        id: number;
        third_party_name: string;
    };
    user_active_status: boolean;
    third_party_approval_status: string;
}

export interface LoginSuccessResponse {
    user: {
        id: number;
        user_id: string;
        first_name: string;
        last_name: string;
        email: string;
        phone: string;
        gender?: string;
        third_party?: {
            id: number;
            third_party_name: string;
        };
    };
    token: string;
    token_type: string;
}

export interface GenericSuccessResponse {
    status: "success";
    message: string;
    data?: any;
}

export type ApiResponse<T = any> = ApiErrorResponse | T;
