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

export const useEnums = (endpoint: string): UseEnumsResult => {
    const { data: session, status } = useSession();
    const [data, setData] = useState<EnumOption[]>([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);

    const fetchEnums = useCallback(async () => {
        const isPublic = ['third-party-types'].includes(endpoint);
        if (status === 'loading' && !isPublic) return;
        if (!isPublic && !session?.accessToken) {
            setIsLoading(false);
            setError('Authentication required to fetch data.');
            setData([]);
            return;
        }

        setIsLoading(true);
        setError(null);
        try {
            const headers: Record<string, string> = {};
            if (session?.accessToken) headers['Authorization'] = `Bearer ${session.accessToken}`;
            const url = `${process.env.NEXT_PUBLIC_EXTERNAL_API_URL}/api/enums/${endpoint}`;
            const response = await axios.get(url, { headers });
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
    }, [endpoint, session?.accessToken, status]);

    useEffect(() => {
        const isPublic = ['third-party-types'].includes(endpoint);
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