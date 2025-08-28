'use client'

import { useEffect } from 'react'
import { CLIENT_APP_NAME, CLIENT_APP_NAME_STRING } from '@/config/client-config'

export function usePageTitle(pageTitle?: string) {
    const appTitleWithVersion = `${CLIENT_APP_NAME_STRING} v${CLIENT_APP_NAME.version}`
    const fullTitle = pageTitle ? `${pageTitle} | ${appTitleWithVersion}` : appTitleWithVersion

    useEffect(() => {
        if (typeof document !== 'undefined') {
            document.title = fullTitle
        }
    }, [fullTitle])
}
