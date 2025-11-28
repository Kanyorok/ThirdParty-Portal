'use client'

import { useEffect } from 'react'
import { CLIENT_APP_NAME, CLIENT_APP_NAME_STRING } from '@/config/client-config'

export function usePageTitle(pageTitle?: string) {
    const appVersionSuffix = CLIENT_APP_NAME.version ? ` v${CLIENT_APP_NAME.version}` : '';
    const appTitleWithVersion = `${CLIENT_APP_NAME_STRING || 'Craft Silicon'} ${appVersionSuffix}`.trim();

    const fullTitle = pageTitle
        ? `${pageTitle} | ${appTitleWithVersion}`
        : appTitleWithVersion;

    useEffect(() => {
        if (typeof document !== 'undefined') {
            document.title = fullTitle
        }
    }, [fullTitle])
}