type UnknownRecord = Record<string, unknown>

function pickFirstString(values: unknown[]) {
  for (const value of values) {
    if (typeof value !== "string") continue
    const trimmed = value.trim()
    if (trimmed) return trimmed
  }
  return null
}

function extractFileNameFromPath(value: unknown) {
  if (typeof value !== "string") return null
  const trimmed = value.trim()
  if (!trimmed) return null

  const withoutQuery = trimmed.split(/[?#]/, 1)[0] ?? ""
  const normalized = withoutQuery.replace(/\\/g, "/")
  const segment = normalized.split("/").filter(Boolean).pop()
  if (!segment) return null

  try {
    const decoded = decodeURIComponent(segment).trim()
    return decoded || null
  } catch {
    return segment.trim() || null
  }
}

export function resolveProcurementDocumentName(
  document: UnknownRecord | null | undefined,
  fallback = "Document"
) {
  if (!document) return fallback

  const directName = pickFirstString([
    document.name,
    document.file_name,
    document.fileName,
    document.filename,
    document.file_name_original,
    document.fileNameOriginal,
    document.originalFileName,
    document.original_filename,
    document.originalName,
    document.original_name,
    document.documentName,
    document.DocumentName,
    document.document_name,
    document.documentTitle,
    document.document_title,
    document.title,
    document.Title,
    document.label,
  ])

  if (directName) return directName

  const pathName = pickFirstString([
    extractFileNameFromPath(document.downloadUrl),
    extractFileNameFromPath(document.download_url),
    extractFileNameFromPath(document.url),
    extractFileNameFromPath(document.href),
    extractFileNameFromPath(document.path),
    extractFileNameFromPath(document.filePath),
    extractFileNameFromPath(document.file_path),
    extractFileNameFromPath(document.storagePath),
    extractFileNameFromPath(document.storage_path),
    extractFileNameFromPath(document.blobName),
    extractFileNameFromPath(document.blob_name),
  ])

  return pathName || fallback
}