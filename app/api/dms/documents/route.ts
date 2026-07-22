import { NextRequest, NextResponse } from "next/server";
import { getServerSession } from "next-auth";
import { authOptions } from "@/lib/auth-options";

export async function GET(request: NextRequest) {
  try {
    const session = await getServerSession(authOptions);
    if (!session?.user) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const searchParams = request.nextUrl.searchParams;

    const erpBase = process.env.ERP_BASE_URL || process.env.NEXT_PUBLIC_ERP_BASE_URL || 'http://127.0.0.1:8000';
    const apiUrl = new URL(`${erpBase}/api/dms/documents`);
    searchParams.forEach((value, key) => {
      apiUrl.searchParams.set(key, value);
    });
    if (!apiUrl.searchParams.get('page')) apiUrl.searchParams.set('page', '1');
    if (!apiUrl.searchParams.get('limit')) apiUrl.searchParams.set('limit', '20');

    const response = await fetch(apiUrl.toString(), {
      headers: {
        'Accept': 'application/json',
        'Authorization': session.accessToken ? `Bearer ${session.accessToken}` : ''
      },
      signal: AbortSignal.timeout(10000)
    });

    if (!response.ok) {
      let message = `HTTP ${response.status}`;
      try {
        const err = await response.json();
        message = err.message || err.error || message;
      } catch {}
      return NextResponse.json({ error: message }, { status: response.status });
    }

    const data = await response.json();
    return NextResponse.json(data);
  } catch (e: any) {
    return NextResponse.json({ error: e?.message || 'Failed to fetch documents' }, { status: 500 });
  }
}

export async function POST(request: NextRequest) {
  try {
    const session = await getServerSession(authOptions);
    if (!session?.user) {
      return NextResponse.json({ error: "Unauthorized" }, { status: 401 });
    }

    const erpBase =
      process.env.ERP_BASE_URL ||
      process.env.NEXT_PUBLIC_ERP_BASE_URL ||
      "http://127.0.0.1:8000";

    const incomingFormData = await request.formData();

    const uploadFieldCandidates = [
      "file",
      "document",
      "documents",
      "documents[]",
      "attachment",
      "attachments",
      "attachments[]",
    ];

    let file: File | null = null;
    for (const key of uploadFieldCandidates) {
      const v = incomingFormData.get(key);
      if (v instanceof File && v.size > 0) {
        file = v;
        break;
      }
    }

    if (!file) {
      return NextResponse.json(
        { error: "Missing file" },
        { status: 400 }
      );
    }

    const metaEntries: Array<[string, FormDataEntryValue]> = [];
    incomingFormData.forEach((value, key) => {
      if (value instanceof File) return;
      metaEntries.push([key, value]);
    });

    const shouldRetry = (status: number, bodyText: string) => {
      if (status !== 400 && status !== 422) return false;
      const b = String(bodyText || "").toLowerCase();
      const mentionsUploadField =
        b.includes("file") || b.includes("document") || b.includes("attachment");
      const indicatesMissing =
        b.includes("required") || b.includes("missing") || b.includes("empty");
      return mentionsUploadField && indicatesMissing;
    };

    const sendUpstream = async (fieldName: string) => {
      const formData = new FormData();
      for (const [k, v] of metaEntries) {
        formData.append(k, v);
      }
      formData.append(fieldName, file as File, (file as File).name);

      const res = await fetch(`${erpBase}/api/dms/documents`, {
        method: "POST",
        headers: {
          Accept: "application/json",
          Authorization: session.accessToken ? `Bearer ${session.accessToken}` : "",
        },
        body: formData,
        cache: "no-store",
        signal: AbortSignal.timeout(60000),
      });

      const text = await res.text();
      const contentType = res.headers.get("content-type") || "";

      return { res, text, contentType };
    };

    let last: { res: Response; text: string; contentType: string } | null = null;
    for (const fieldName of uploadFieldCandidates) {
      const attempt = await sendUpstream(fieldName);
      last = attempt;

      if (attempt.res.ok) break;
      if (!shouldRetry(attempt.res.status, attempt.text)) break;
    }

    if (!last) {
      return NextResponse.json({ error: "Upload failed" }, { status: 500 });
    }

    let parsedJson: any = null;
    try {
      parsedJson = JSON.parse(last.text || "");
    } catch {}

    if (last.contentType.includes("application/json") || parsedJson != null) {
      const data: any = parsedJson ?? { raw: last.text };

      if (!last.res.ok) {
        return NextResponse.json(
          {
            error:
              data?.message ||
              data?.error ||
              `Upload failed (HTTP ${last.res.status})`,
            upstream: data,
          },
          { status: last.res.status }
        );
      }

      return NextResponse.json(data, { status: last.res.status });
    }

    if (!last.res.ok) {
      return NextResponse.json(
        { error: `Upload failed (HTTP ${last.res.status})`, upstream: last.text },
        { status: last.res.status }
      );
    }

    return new NextResponse(last.text, { status: last.res.status });
  } catch (e: any) {
    return NextResponse.json(
      { error: e?.message || "Failed to upload document" },
      { status: 500 }
    );
  }
}
