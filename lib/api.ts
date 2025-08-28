import axios from 'axios';
import { getSession, signOut } from 'next-auth/react';

const API_URL = process.env.NEXT_PUBLIC_EXTERNAL_API_URL;

const api = axios.create({
    baseURL: API_URL,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
    withCredentials: true,
});


// This serves to get the csrf token (from sanctum)
api.interceptors.request.use(
    async (config) => {
        try {
            const csrfCookieResponse = await fetch(new URL('/sanctum/csrf-cookie', API_URL).toString(), {
                method: 'GET',
                credentials: 'include',
            });

            if (!csrfCookieResponse.ok) {
                console.error('Failed to fetch CSRF cookie:', csrfCookieResponse.statusText);
                return Promise.reject(new Error('Failed to obtain CSRF token.'));
            }

            const session = await getSession();
            if (session?.accessToken) {
                config.headers.Authorization = `Bearer ${session.accessToken}`;
            }
        } catch (error) {
            console.error('Error in request interceptor:', error);
            return Promise.reject(error);
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            // Unauthorized, sign out the user
            signOut({ callbackUrl: '/auth/signin' });
        }
        return Promise.reject(error);
    }
);

export default api;
