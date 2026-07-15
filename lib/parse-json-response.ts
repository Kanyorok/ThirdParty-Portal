export async function parseJsonResponse<T = any>(res: Response): Promise<T | null> {
    const contentType = res.headers.get("content-type") || ""

    if (!contentType.includes("application/json")) {
        await res.text().catch(() => "")
        return null
    }

    return (await res.json().catch(() => null)) as T | null
}