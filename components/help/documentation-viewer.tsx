"use client"

import React, { useState, useEffect } from "react"
import { Button } from "@/components/common/button"
import { ArrowLeft, Download, BookOpen, Loader2 } from "lucide-react"
import Link from "next/link"

interface DocumentationViewerProps {
  filePath: string
  title: string
  backHref?: string
}

function parseMarkdownToHTML(markdown: string): string {
  let html = markdown

  // Helper function to create slug from header text
  const createSlug = (text: string): string => {
    return text
      .toLowerCase()
      .trim()
      .replace(/[^\w\s-]/g, '') // Remove special characters
      .replace(/\s+/g, '-') // Replace spaces with hyphens
      .replace(/-+/g, '-') // Replace multiple hyphens with single hyphen
  }

  // Headers (process from most specific to least specific)
  html = html.replace(/^###### (.*$)/gim, (match, text) => {
    const slug = createSlug(text)
    return `<h6 id="${slug}" class="text-sm font-semibold text-slate-800 mt-4 mb-2 scroll-mt-20">${text}</h6>`
  })
  html = html.replace(/^##### (.*$)/gim, (match, text) => {
    const slug = createSlug(text)
    return `<h5 id="${slug}" class="text-base font-semibold text-slate-800 mt-5 mb-3 scroll-mt-20">${text}</h5>`
  })
  html = html.replace(/^#### (.*$)/gim, (match, text) => {
    const slug = createSlug(text)
    return `<h4 id="${slug}" class="text-lg font-bold text-slate-900 mt-6 mb-3 scroll-mt-20">${text}</h4>`
  })
  html = html.replace(/^### (.*$)/gim, (match, text) => {
    const slug = createSlug(text)
    return `<h3 id="${slug}" class="text-xl font-bold text-slate-900 mt-8 mb-4 scroll-mt-20">${text}</h3>`
  })
  html = html.replace(/^## (.*$)/gim, (match, text) => {
    const slug = createSlug(text)
    return `<h2 id="${slug}" class="text-2xl font-bold text-slate-900 mt-12 mb-5 pb-3 border-b-2 border-blue-200 scroll-mt-20">${text}</h2>`
  })
  html = html.replace(/^# (.*$)/gim, (match, text) => {
    const slug = createSlug(text)
    return `<h1 id="${slug}" class="text-3xl font-extrabold text-slate-900 mt-10 mb-6 scroll-mt-20">${text}</h1>`
  })

  // Bold and Italic
  html = html.replace(/\*\*\*(.*?)\*\*\*/g, '<strong><em class="font-bold text-slate-900">$1</em></strong>')
  html = html.replace(/\*\*(.*?)\*\*/g, '<strong class="font-bold text-slate-900">$1</strong>')
  html = html.replace(/\*(.*?)\*/g, '<em class="italic text-slate-700">$1</em>')

  // Code blocks (``` ... ```)
  html = html.replace(/```([\s\S]*?)```/g, '<pre class="bg-gradient-to-br from-slate-900 to-slate-800 text-slate-100 rounded-xl p-5 overflow-x-auto my-6 shadow-lg border border-slate-700 font-mono text-sm leading-relaxed"><code>$1</code></pre>')

  // Inline code
  html = html.replace(/`([^`]+)`/g, '<code class="bg-blue-50 text-blue-900 px-2 py-1 rounded-md text-sm font-mono border border-blue-100 shadow-sm">$1</code>')

  // Links [text](url)
  html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" class="text-blue-600 hover:text-blue-700 underline decoration-2 underline-offset-2 font-medium transition-colors">$1</a>')

  // Horizontal rules
  html = html.replace(/^---$/gim, '<hr class="my-10 border-t-2 border-slate-200" />')

  // Blockquotes
  html = html.replace(/^> (.*$)/gim, '<blockquote class="border-l-4 border-blue-500 bg-blue-50 pl-6 pr-4 py-4 my-6 text-slate-700 italic rounded-r-lg shadow-sm">$1</blockquote>')

  // Convert line breaks to <br> but preserve structure
  const lines = html.split('\n')
  const processedLines: string[] = []
  let inList = false
  let inOrderedList = false
  let inTable = false

  for (let i = 0; i < lines.length; i++) {
    const line = lines[i]
    const trimmedLine = line.trim()

    // Tables
    if (trimmedLine.startsWith('|')) {
      if (!inTable) {
        processedLines.push('<div class="my-8 overflow-hidden rounded-xl border border-slate-200 shadow-md"><table class="min-w-full divide-y divide-slate-200">')
        inTable = true
      }
      
      const cells = trimmedLine.split('|').filter(cell => cell.trim())
      const isHeaderSeparator = cells.every(cell => /^[\s:-]+$/.test(cell))
      
      if (isHeaderSeparator) {
        // Skip separator row
        continue
      }
      
      const isFirstRow = i === 0 || !lines[i - 1].trim().startsWith('|')
      const cellTag = isFirstRow ? 'th' : 'td'
      const cellClass = isFirstRow 
        ? 'px-6 py-4 text-left text-xs font-bold text-slate-900 uppercase tracking-wide bg-gradient-to-r from-slate-50 to-slate-100'
        : 'px-6 py-4 text-sm text-slate-700 border-t border-slate-100'
      
      processedLines.push('<tr class="hover:bg-slate-50 transition-colors">')
      cells.forEach(cell => {
        processedLines.push(`<${cellTag} class="${cellClass}">${cell.trim()}</${cellTag}>`)
      })
      processedLines.push('</tr>')
      continue
    } else if (inTable) {
      processedLines.push('</table></div>')
      inTable = false
    }

    // Unordered lists
    if (trimmedLine.startsWith('- ') || trimmedLine.startsWith('* ')) {
      if (!inList) {
        processedLines.push('<ul class="space-y-3 my-6 ml-6">')
        inList = true
      }
      const content = trimmedLine.substring(2)
      processedLines.push(`<li class="text-slate-700 leading-relaxed pl-2 relative before:content-['•'] before:absolute before:-left-4 before:text-blue-600 before:font-bold before:text-lg">${content}</li>`)
      continue
    } else if (inList) {
      processedLines.push('</ul>')
      inList = false
    }

    // Ordered lists
    if (/^\d+\.\s/.test(trimmedLine)) {
      if (!inOrderedList) {
        processedLines.push('<ol class="space-y-3 my-6 ml-6 list-decimal list-outside">')
        inOrderedList = true
      }
      const content = trimmedLine.replace(/^\d+\.\s/, '')
      processedLines.push(`<li class="text-slate-700 leading-relaxed pl-2 marker:text-blue-600 marker:font-bold">${content}</li>`)
      continue
    } else if (inOrderedList) {
      processedLines.push('</ol>')
      inOrderedList = false
    }

    // Regular paragraphs
    if (trimmedLine === '') {
      processedLines.push('<div class="h-2"></div>')
    } else if (!trimmedLine.startsWith('<')) {
      processedLines.push(`<p class="text-slate-700 leading-relaxed text-base my-4">${line}</p>`)
    } else {
      processedLines.push(line)
    }
  }

  // Close any open lists or tables
  if (inList) processedLines.push('</ul>')
  if (inOrderedList) processedLines.push('</ol>')
  if (inTable) processedLines.push('</table></div>')

  return processedLines.join('\n')
}

export function DocumentationViewer({ filePath, title, backHref = "/dashboard/help" }: DocumentationViewerProps) {
  const [content, setContent] = useState<string>("")
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  useEffect(() => {
    // Enable smooth scrolling for anchor links
    document.documentElement.style.scrollBehavior = 'smooth'
    
    return () => {
      document.documentElement.style.scrollBehavior = ''
    }
  }, [])

  useEffect(() => {
    fetch(filePath)
      .then((res) => {
        if (!res.ok) throw new Error("Failed to load documentation")
        return res.text()
      })
      .then((text) => {
        setContent(text)
        setLoading(false)
      })
      .catch((err) => {
        setError(err.message)
        setLoading(false)
      })
  }, [filePath])

  const handleDownload = () => {
    // Create a new window for PDF generation
    const printWindow = window.open('', '_blank')
    if (!printWindow) {
      alert('Please allow pop-ups to download PDF')
      return
    }

    const htmlContent = parseMarkdownToHTML(content)
    
    // Create a complete HTML document for printing
    const printContent = `
      <!DOCTYPE html>
      <html>
        <head>
          <meta charset="utf-8">
          <title>${title}</title>
          <style>
            @page {
              margin: 2cm;
              size: A4;
            }
            
            * {
              box-sizing: border-box;
            }
            
            body {
              font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
              font-size: 11pt;
              line-height: 1.6;
              color: #1e293b;
              max-width: 100%;
              margin: 0;
              padding: 20px;
            }
            
            h1 {
              font-size: 24pt;
              font-weight: 800;
              color: #0f172a;
              margin: 20px 0 15px 0;
              page-break-after: avoid;
            }
            
            h2 {
              font-size: 18pt;
              font-weight: 700;
              color: #0f172a;
              margin: 30px 0 12px 0;
              padding-bottom: 8px;
              border-bottom: 2px solid #3b82f6;
              page-break-after: avoid;
            }
            
            h3 {
              font-size: 14pt;
              font-weight: 700;
              color: #0f172a;
              margin: 20px 0 10px 0;
              page-break-after: avoid;
            }
            
            h4 {
              font-size: 12pt;
              font-weight: 700;
              color: #334155;
              margin: 15px 0 8px 0;
              page-break-after: avoid;
            }
            
            h5, h6 {
              font-size: 11pt;
              font-weight: 600;
              color: #334155;
              margin: 12px 0 6px 0;
              page-break-after: avoid;
            }
            
            p {
              margin: 8px 0;
              text-align: justify;
            }
            
            ul, ol {
              margin: 10px 0;
              padding-left: 30px;
            }
            
            li {
              margin: 5px 0;
              line-height: 1.5;
            }
            
            ul li::marker {
              color: #3b82f6;
              font-weight: bold;
            }
            
            ol li::marker {
              color: #3b82f6;
              font-weight: bold;
            }
            
            table {
              width: 100%;
              border-collapse: collapse;
              margin: 15px 0;
              page-break-inside: avoid;
            }
            
            th, td {
              border: 1px solid #cbd5e1;
              padding: 8px 12px;
              text-align: left;
            }
            
            th {
              background-color: #f1f5f9;
              font-weight: 700;
              font-size: 9pt;
              text-transform: uppercase;
              color: #0f172a;
            }
            
            td {
              font-size: 10pt;
            }
            
            code {
              background-color: #eff6ff;
              color: #1e40af;
              padding: 2px 6px;
              border-radius: 4px;
              font-family: 'Courier New', Courier, monospace;
              font-size: 9pt;
              border: 1px solid #dbeafe;
            }
            
            pre {
              background-color: #1e293b;
              color: #e2e8f0;
              padding: 15px;
              border-radius: 8px;
              overflow-x: auto;
              margin: 15px 0;
              page-break-inside: avoid;
            }
            
            pre code {
              background: none;
              border: none;
              color: inherit;
              padding: 0;
            }
            
            blockquote {
              border-left: 4px solid #3b82f6;
              background-color: #eff6ff;
              padding: 12px 15px;
              margin: 15px 0;
              font-style: italic;
              color: #475569;
              page-break-inside: avoid;
            }
            
            hr {
              border: none;
              border-top: 2px solid #e2e8f0;
              margin: 25px 0;
            }
            
            a {
              color: #2563eb;
              text-decoration: underline;
            }
            
            strong {
              font-weight: 700;
              color: #0f172a;
            }
            
            em {
              font-style: italic;
              color: #475569;
            }
            
            .header {
              text-align: center;
              margin-bottom: 30px;
              padding-bottom: 15px;
              border-bottom: 3px solid #3b82f6;
            }
            
            .header h1 {
              margin: 0;
              font-size: 28pt;
            }
            
            .header p {
              color: #64748b;
              margin: 8px 0 0 0;
              font-size: 10pt;
            }
            
            .footer {
              margin-top: 40px;
              padding-top: 15px;
              border-top: 2px solid #e2e8f0;
              text-align: center;
              font-size: 9pt;
              color: #64748b;
            }
            
            @media print {
              body {
                padding: 0;
              }
              
              .no-print {
                display: none !important;
              }
              
              h1, h2, h3, h4, h5, h6 {
                page-break-after: avoid;
              }
              
              table, figure, img, pre, blockquote {
                page-break-inside: avoid;
              }
              
              ul, ol {
                page-break-before: avoid;
              }
            }
          </style>
        </head>
        <body>
          <div class="header">
            <h1>${title}</h1>
            <p>Third Party Portal Documentation</p>
          </div>
          
          <div class="content">
            ${htmlContent}
          </div>
          
          <div class="footer">
            <p>Generated on ${new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' })}</p>
            <p>Third Party Portal - Version 2.0</p>
          </div>
        </body>
      </html>
    `
    
    printWindow.document.write(printContent)
    printWindow.document.close()
    
    // Wait for content to load, then trigger print
    printWindow.onload = () => {
      setTimeout(() => {
        printWindow.focus()
        printWindow.print()
        // Close the window after printing (user can cancel)
        setTimeout(() => {
          printWindow.close()
        }, 100)
      }, 250)
    }
  }

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-[500px] bg-gradient-to-br from-slate-50 to-blue-50 rounded-2xl border border-slate-200">
        <div className="text-center space-y-4">
          <div className="relative">
            <div className="absolute inset-0 bg-blue-400 rounded-full blur-xl opacity-20 animate-pulse"></div>
            <Loader2 className="relative h-10 w-10 animate-spin text-blue-600 mx-auto" />
          </div>
          <p className="text-base font-medium text-slate-700">Loading documentation...</p>
        </div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="rounded-2xl border-2 border-red-200 bg-gradient-to-br from-red-50 to-orange-50 px-8 py-12 text-center shadow-lg">
        <div className="mx-auto flex h-16 w-16 items-center justify-center rounded-full border-2 border-red-300 bg-white shadow-md">
          <BookOpen className="h-7 w-7 text-red-600" />
        </div>
        <p className="mt-5 text-lg font-bold text-red-900">Failed to load documentation</p>
        <p className="mt-2 text-sm text-red-700 max-w-md mx-auto">{error}</p>
        <Button asChild variant="outline" className="mt-6 shadow-sm">
          <Link href={backHref}>
            <ArrowLeft className="mr-2 h-4 w-4" />
            Back to Help Center
          </Link>
        </Button>
      </div>
    )
  }

  const htmlContent = parseMarkdownToHTML(content)

  return (
    <div className="w-full space-y-8">
      {/* Header */}
      <div className="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-2xl border border-blue-100 shadow-sm">
        <div className="flex items-center gap-4">
          <Button asChild variant="outline" size="sm" className="shadow-sm hover:shadow-md transition-shadow">
            <Link href={backHref}>
              <ArrowLeft className="h-4 w-4" />
            </Link>
          </Button>
          <div>
            <h1 className="text-3xl font-bold tracking-tight text-slate-900">{title}</h1>
            <p className="mt-1 text-sm text-slate-600 font-medium">Comprehensive documentation and guides</p>
          </div>
        </div>
        <Button onClick={handleDownload} variant="outline" size="sm" className="shadow-sm hover:shadow-md transition-all hover:bg-white">
          <Download className="mr-2 h-4 w-4" />
          Download
        </Button>
      </div>

      {/* Content */}
      <div className="rounded-2xl border border-slate-200 bg-white shadow-xl overflow-hidden">
        <div className="px-8 py-10 lg:px-12 lg:py-14 max-w-5xl mx-auto">
          <div 
            className="documentation-content prose prose-slate max-w-none"
            style={{
              fontSize: '16px',
              lineHeight: '1.75'
            }}
            dangerouslySetInnerHTML={{ __html: htmlContent }}
          />
        </div>
      </div>

      {/* Footer */}
      <div className="flex justify-center pt-4">
        <Button asChild variant="outline" size="lg" className="shadow-md hover:shadow-lg transition-all">
          <Link href={backHref}>
            <ArrowLeft className="mr-2 h-4 w-4" />
            Back to Help Center
          </Link>
        </Button>
      </div>
    </div>
  )
}
