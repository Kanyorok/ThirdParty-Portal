import { useEffect } from "react"

interface KeyboardShortcut {
  key: string
  ctrlKey?: boolean
  shiftKey?: boolean
  altKey?: boolean
  metaKey?: boolean
  callback: () => void
  description?: string
}

export function useKeyboardShortcut(shortcuts: KeyboardShortcut[]) {
  useEffect(() => {
    const handleKeyDown = (event: KeyboardEvent) => {
      if (!event.key) return

      for (const shortcut of shortcuts) {
        if (!shortcut.key) continue

        const keyMatch = event.key.toLowerCase() === shortcut.key.toLowerCase()
        const ctrlMatch = shortcut.ctrlKey === undefined || event.ctrlKey === shortcut.ctrlKey
        const shiftMatch = shortcut.shiftKey === undefined || event.shiftKey === shortcut.shiftKey
        const altMatch = shortcut.altKey === undefined || event.altKey === shortcut.altKey
        const metaMatch = shortcut.metaKey === undefined || event.metaKey === shortcut.metaKey

        if (keyMatch && ctrlMatch && shiftMatch && altMatch && metaMatch) {
          event.preventDefault()
          shortcut.callback()
          break
        }
      }
    }

    window.addEventListener("keydown", handleKeyDown)
    return () => window.removeEventListener("keydown", handleKeyDown)
  }, [shortcuts])
}
