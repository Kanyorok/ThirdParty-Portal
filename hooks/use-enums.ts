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
        if (status === 'loading') {
            return;
        }

        if (!session?.accessToken) {
            setIsLoading(false);
            setError('Authentication required to fetch data.');
            return;
        }

        setIsLoading(true);
        setError(null);
        try {
            const response = await axios.get(
                `${process.env.NEXT_PUBLIC_EXTERNAL_API_URL}/api/enums/${endpoint}`,
                {
                    headers: {
                        Authorization: `Bearer ${session.accessToken}`,
                    },
                }
            );
            setData(response.data);
        } catch (err: any) {
            console.error('Failed to fetch enums:', err);
            setError(err.message || 'Failed to load options. Please try again.');
            setData([]);
        } finally {
            setIsLoading(false);
        }
    }, [endpoint, session, status]);

    useEffect(() => {
        if (status === 'authenticated' && session) {
            fetchEnums();
        } else if (status === 'unauthenticated') {
            setIsLoading(false);
            setError('Please log in to view options.');
        }
    }, [fetchEnums, session, status]);

    return { data, isLoading, error, refetch: fetchEnums };
};