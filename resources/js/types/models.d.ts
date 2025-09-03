import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: any;
    isActive?: boolean;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    [key: string]: unknown;
    ziggy: Config & { location: string };
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    roles: Array<{
        id: number;
        name: string;
    }>;
}

export interface Employee {
    id: number;
    employee_number: string;
    first_name: string;
    last_name: string;
    national_id?: string;
    kra_pin?: string;
    email: string;
    phone?: string;
    emergency_contact_name?: string;
    emergency_contact_phone?: string;
    date_of_birth?: string;
    gender?: string;
    marital_status?: string;
    employment_type?: string;
    hire_date?: string;
    termination_date?: string;
    is_active: boolean;
    department_id?: number;
    position_id?: number;
    location_id?: number;
    next_of_kin_name?: string;
    next_of_kin_relationship?: string;
    next_of_kin_phone?: string;
    next_of_kin_email?: string;
    email_verified_at?: string | null;
    created_at: string;
    updated_at: string;
    // Computed attributes
    name: string;
    full_name: string;
    avatar?: string;
    // Relationships
    department?: {
        id: number;
        name: string;
    };
    position?: {
        id: number;
        name: string;
    };
    location?: {
        id: number;
        name: string;
    };
}
