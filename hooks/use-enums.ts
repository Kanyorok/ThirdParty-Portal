import { useState, useEffect, useCallback } from 'react';
import axios from 'axios';
import { useSession } from 'next-auth/react';

interface EnumOption {
    value: string;
    label: string;
}

interface UseEnumsResult {
    data: EnumOption[];
    isLoading: boolean;
    error: string | null;
    refetch: () => void;
}

const PUBLIC_ENUMS = ['third-party-types', 'BusinessType', 'Gender', 'MaritalStatus', 'Occupation'];

export const useEnums = (endpoint: string): UseEnumsResult => {
    const { status } = useSession();
    const [data, setData] = useState<EnumOption[]>([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    const fetchEnums = useCallback(async () => {
        const isPublic = PUBLIC_ENUMS.includes(endpoint);
        if (status === 'loading' && !isPublic) return;
        if (!isPublic && status !== 'authenticated') {
            setIsLoading(false);
            setError('Authentication required to fetch data.');
            setData([]);
            return;
        }

        setIsLoading(true);
        setError(null);
        try {
            const url = `/api/enums/${encodeURIComponent(endpoint)}`;
            const response = await axios.get(url, { withCredentials: true });
            if (!Array.isArray(response.data)) {
                throw new Error('Unexpected response format');
            }
            setData(response.data);
        } catch (err: any) {
            console.error('Failed to fetch enums:', err);
            setError(err?.response?.status === 401 ? 'Not authorized to load options.' : (err.message || 'Failed to load options.'));
            setData([]);
        } finally {
            setIsLoading(false);
        }
    }, [endpoint, status]);

    useEffect(() => {
        const isPublic = PUBLIC_ENUMS.includes(endpoint);
        if (isPublic) {
            fetchEnums();
            return;
        }
        if (status === 'authenticated') fetchEnums();
        else if (status === 'unauthenticated') {
            setIsLoading(false);
            setError('Please log in to view options.');
        }
    }, [endpoint, fetchEnums, status]);

    return { data, isLoading, error, refetch: fetchEnums };
};