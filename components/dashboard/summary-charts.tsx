"use client";

import React, { useEffect, useState } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/common/card";

type PreqBreakdown = {
  approved: number;
  submitted: number;
  under_review: number;
  rejected: number;
  not_applied: number;
};

type InvBreakdown = {
  pending: number;
  accepted: number;
  declined: number;
  submitted: number;
};

interface SummaryData {
  breakdowns: {
    prequalification: PreqBreakdown;
    invitations: InvBreakdown;
  };
}

function Bar({ value, color }: { value: number; color: string }) {
  return (
    <div className="h-2 rounded" style={{ width: `${Math.max(0, Math.min(100, value))}%`, backgroundColor: color }} />
  );
}

export default function SummaryCharts() {
  const [data, setData] = useState<SummaryData | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let isMounted = true;
    (async () => {
      try {
        const res = await fetch('/api/dashboard/summary', { cache: 'no-store' });
        if (res.ok) {
          const json = (await res.json()) as SummaryData;
          if (isMounted) setData(json);
        }
      } finally {
        if (isMounted) setLoading(false);
      }
    })();
    return () => { isMounted = false };
  }, []);

  const preq = data?.breakdowns?.prequalification;
  const inv = data?.breakdowns?.invitations;

  const preqTotal = (preq?.approved ?? 0) + (preq?.submitted ?? 0) + (preq?.under_review ?? 0) + (preq?.rejected ?? 0) + (preq?.not_applied ?? 0);
  const invTotal = (inv?.pending ?? 0) + (inv?.accepted ?? 0) + (inv?.declined ?? 0) + (inv?.submitted ?? 0);

  return (
    <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <Card>
        <CardHeader>
          <CardTitle>Prequalification Status</CardTitle>
        </CardHeader>
        <CardContent>
          {loading ? (
            <div className="text-sm text-muted-foreground">Loading…</div>
          ) : preqTotal === 0 ? (
            <div className="text-sm text-muted-foreground">No prequalification activity.</div>
          ) : (
            <div className="space-y-3">
              <div>
                <div className="flex justify-between text-xs mb-1"><span>Approved</span><span>{preq?.approved ?? 0}</span></div>
                <Bar value={((preq?.approved ?? 0) / preqTotal) * 100} color="#16a34a" />
              </div>
              <div>
                <div className="flex justify-between text-xs mb-1"><span>Submitted</span><span>{preq?.submitted ?? 0}</span></div>
                <Bar value={((preq?.submitted ?? 0) / preqTotal) * 100} color="#2563eb" />
              </div>
              <div>
                <div className="flex justify-between text-xs mb-1"><span>Under Review</span><span>{preq?.under_review ?? 0}</span></div>
                <Bar value={((preq?.under_review ?? 0) / preqTotal) * 100} color="#f97316" />
              </div>
              <div>
                <div className="flex justify-between text-xs mb-1"><span>Rejected</span><span>{preq?.rejected ?? 0}</span></div>
                <Bar value={((preq?.rejected ?? 0) / preqTotal) * 100} color="#dc2626" />
              </div>
              <div>
                <div className="flex justify-between text-xs mb-1"><span>Not Applied</span><span>{preq?.not_applied ?? 0}</span></div>
                <Bar value={((preq?.not_applied ?? 0) / preqTotal) * 100} color="#6b7280" />
              </div>
            </div>
          )}
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Tender Invitations</CardTitle>
        </CardHeader>
        <CardContent>
          {loading ? (
            <div className="text-sm text-muted-foreground">Loading…</div>
          ) : invTotal === 0 ? (
            <div className="text-sm text-muted-foreground">No invitations.</div>
          ) : (
            <div className="space-y-3">
              <div>
                <div className="flex justify-between text-xs mb-1"><span>Pending</span><span>{inv?.pending ?? 0}</span></div>
                <Bar value={((inv?.pending ?? 0) / invTotal) * 100} color="#6b7280" />
              </div>
              <div>
                <div className="flex justify-between text-xs mb-1"><span>Accepted</span><span>{inv?.accepted ?? 0}</span></div>
                <Bar value={((inv?.accepted ?? 0) / invTotal) * 100} color="#16a34a" />
              </div>
              <div>
                <div className="flex justify-between text-xs mb-1"><span>Submitted</span><span>{inv?.submitted ?? 0}</span></div>
                <Bar value={((inv?.submitted ?? 0) / invTotal) * 100} color="#2563eb" />
              </div>
              <div>
                <div className="flex justify-between text-xs mb-1"><span>Declined</span><span>{inv?.declined ?? 0}</span></div>
                <Bar value={((inv?.declined ?? 0) / invTotal) * 100} color="#dc2626" />
              </div>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}



