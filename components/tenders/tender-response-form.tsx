"use client";

import React, { useState } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/common/card";
import { Button } from "@/components/common/button";
import { Textarea } from "@/components/common/textarea";
import { toast } from "sonner";
import {
  CheckCircle,
  XCircle,
  AlertTriangle,
  Clock,
  Send
} from "lucide-react";
import { cn } from "@/lib/utils";
import type { PortalTender, TenderInvitation } from "@/types/tender";

interface TenderResponseFormProps {
  tender: PortalTender;
  invitation?: TenderInvitation | null;
  onUpdate?: () => void;
  onStartBid?: () => void;
  bidAlreadySubmitted?: boolean;
}

export default function TenderResponseForm({
  tender,
  invitation,
  onUpdate,
  onStartBid,
  bidAlreadySubmitted = false,
}: TenderResponseFormProps) {
  const [isLoading, setIsLoading] = useState(false);
  const [responseStatus, setResponseStatus] = useState<'accepted' | 'declined' | null>(null);
  const [declineReason, setDeclineReason] = useState("");

  // Add optimistic status state
  const [optimisticStatus, setOptimisticStatus] = useState<'accepted' | 'declined' | null>(null);

  // Component ready for production use

  const tenderId = (tender as any)?.id ?? (tender as any)?.Id;
  const tenderTypeValue = String((tender as any)?.tenderType ?? (tender as any)?.TenderType ?? "").toLowerCase();
  const invitationAny = invitation as any;

  const handleResponse = async (status: 'accepted' | 'declined') => {
    if (!invitation) {
      toast.error("No invitation found to respond to");
      return;
    }

    if (status === 'declined' && !declineReason.trim()) {
      toast.error("Please provide a reason for declining the invitation");
      return;
    }

    setIsLoading(true);

    try {
      const requestPayload = {
        tender_id: invitation?.TenderId || invitationAny?.tenderId || tenderId,
        response_status: status,
        decline_reason: status === 'declined' ? declineReason : null,
      };

      const response = await fetch("/api/tender-invitations", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestPayload),
      });

      let data: any = {};

      // Parse response
      try {
        const responseText = await response.text();
        if (responseText.trim()) {
          data = JSON.parse(responseText);
        }
      } catch {
        data = { parseError: 'Invalid JSON response' };
      }

      if (!response.ok) {
        // Provide user-friendly error messages
        let errorMessage = 'Unable to update invitation response.';
        if (data.message) errorMessage = data.message;
        else if (data.error) errorMessage = data.error;
        else if (response.status === 404) errorMessage = 'Invitation response endpoint was not found.';
        else if (response.status === 422) errorMessage = 'The invitation response request is invalid.';
        else if (response.status === 500) errorMessage = 'The server could not process the invitation response.';

        throw new Error(errorMessage);
      }

      toast.success(
        status === 'accepted'
          ? "Tender invitation accepted."
          : "Tender invitation declined."
      );

      setOptimisticStatus(status);

      if (onUpdate) {
        onUpdate();
      }

    } catch (error) {
      console.error('Error updating invitation response:', error);
      toast.error(
        error instanceof Error
          ? error.message
          : "Unable to update invitation response."
      );
    } finally {
      setIsLoading(false);
      setResponseStatus(null);
      setDeclineReason("");
    }
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'accepted':
        return <CheckCircle className="h-5 w-5 text-emerald-600" />;
      case 'declined':
        return <XCircle className="h-5 w-5 text-rose-600" />;
      case 'submitted':
        return <Send className="h-5 w-5 text-indigo-600" />;
      case 'pending':
        return <Clock className="h-5 w-5 text-amber-600" />;
      default:
        return <AlertTriangle className="h-5 w-5 text-slate-600" />;
    }
  };

  const getStatusText = (status: string) => {
    switch (status) {
      case 'accepted':
        return 'Accepted';
      case 'declined':
        return 'Declined';
      case 'submitted':
        return 'Bid Submitted';
      case 'pending':
        return 'Pending Response';
      default:
        return status;
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'accepted':
        return 'border-emerald-200 bg-emerald-50/60';
      case 'declined':
        return 'border-rose-200 bg-rose-50/60';
      case 'submitted':
        return 'border-indigo-200 bg-indigo-50/60';
      case 'pending':
        return 'border-amber-200 bg-amber-50/60';
      default:
        return 'border-slate-200 bg-slate-50';
    }
  };

  // Check if this is an open tender
  const isOpenTender = tenderTypeValue === 'op' || tenderTypeValue === 'open';
  const isRestrictedTender = tenderTypeValue === 'rs' || tenderTypeValue === 'restricted';

  if (isOpenTender) {
    return null;
  }

  if (!invitation && isRestrictedTender) {
    return (
      <Card className="border-slate-200 bg-slate-50 shadow-none">
        <CardContent className="py-5">
          <div className="flex items-center gap-3 text-slate-600">
            <AlertTriangle className="h-5 w-5" />
            <div>
              <p className="text-sm font-semibold text-slate-800">Restricted tender</p>
              <p className="text-xs text-slate-500">
                Only invited suppliers can respond to this tender.
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
    );
  }

  const currentStatus = String(optimisticStatus || invitation?.ResponseStatus || invitationAny?.responseStatus || 'pending').toLowerCase();
  const canRespond = currentStatus === 'pending' && isRestrictedTender;
  const isResponseLocked = !canRespond && currentStatus !== 'pending';
  const isDeclined = currentStatus === 'declined' || currentStatus === 'rejected';

  return (
    <div className="space-y-4">
      <Card className={cn("border-2 shadow-none", getStatusColor(currentStatus))}>
        <CardContent className="py-4">
          <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div className="flex items-center gap-3">
              <span className="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white">
                {getStatusIcon(currentStatus)}
              </span>
              <div>
                <p className="text-sm font-semibold text-slate-900">Invitation status</p>
                <p className="text-xs text-slate-600 capitalize">{getStatusText(currentStatus)}</p>
              </div>
            </div>
            {isResponseLocked && (
              <span className="inline-flex items-center rounded-full border border-slate-300 bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-700">
                Response locked
              </span>
            )}
            {invitation && (invitation.ResponseDate || invitationAny?.responseDate) && (
              <div className="text-xs text-slate-500">
                Responded on {new Date(invitation.ResponseDate || invitationAny?.responseDate).toLocaleDateString()}
              </div>
            )}
          </div>

          {isDeclined && (
            <div className="mt-3 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700">
              <strong>Access locked:</strong> Clarifications and bidding locked after decline.
            </div>
          )}

          {isDeclined && (invitation?.DeclineReason || invitationAny?.declineReason) && (
            <div className="mt-3 rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700">
              <strong>Decline Reason:</strong> {invitation?.DeclineReason || invitationAny?.declineReason}
            </div>
          )}
        </CardContent>
      </Card>

      {canRespond && (
        <Card className="border-slate-200/70 bg-white shadow-none">
          <CardHeader>
            <CardTitle className="text-base">Respond to invitation</CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="flex flex-col gap-3 sm:flex-row">
              <Button
                onClick={() => setResponseStatus('accepted')}
                variant={responseStatus === 'accepted' ? 'default' : 'outline'}
                className="flex-1"
                disabled={isLoading}
              >
                <CheckCircle className="h-4 w-4 mr-2" />
                Accept
              </Button>
              <Button
                onClick={() => setResponseStatus('declined')}
                variant={responseStatus === 'declined' ? 'destructive' : 'outline'}
                className="flex-1"
                disabled={isLoading}
              >
                <XCircle className="h-4 w-4 mr-2" />
                Decline
              </Button>
            </div>

            {responseStatus === 'declined' && (
              <div className="space-y-2">
                <label className="text-sm font-medium">
                  Reason for declining
                </label>
                <Textarea
                  placeholder="Provide a brief reason for declining..."
                  value={declineReason}
                  onChange={(e) => setDeclineReason(e.target.value)}
                  className="min-h-[90px]"
                />
              </div>
            )}

            {responseStatus && (
              <div className="flex flex-col gap-2 sm:flex-row">
                <Button
                  onClick={() => handleResponse(responseStatus)}
                  disabled={
                    isLoading ||
                    (responseStatus === 'declined' && !declineReason.trim())
                  }
                  className="flex-1"
                  variant={responseStatus === 'accepted' ? 'default' : 'destructive'}
                >
                  {isLoading ? (
                    <div className="flex items-center">
                      <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2" />
                      Processing...
                    </div>
                  ) : (
                    `Confirm ${responseStatus === 'accepted' ? 'Accept' : 'Decline'}`
                  )}
                </Button>
                <Button
                  onClick={() => {
                    setResponseStatus(null);
                    setDeclineReason("");
                  }}
                  variant="outline"
                  disabled={isLoading}
                  className="flex-1"
                >
                  Cancel
                </Button>
              </div>
            )}
          </CardContent>
        </Card>
      )}

      {currentStatus === 'accepted' && (
        <Card className="border-emerald-200 bg-emerald-50 shadow-none">
          <CardContent className="py-4">
            <div className="flex items-center justify-between gap-3">
              <div className="flex items-center gap-2 text-emerald-800">
                <CheckCircle className="h-5 w-5" />
                <div>
                  <p className="text-sm font-semibold">Invitation accepted</p>
                  <p className="text-xs text-emerald-700">
                    {bidAlreadySubmitted
                      ? "You already submitted a bid for this tender."
                      : "Proceed to bidding when ready."
                    }
                  </p>
                </div>
              </div>
              <Button
                onClick={onStartBid}
                size="sm"
                disabled={bidAlreadySubmitted}
                className={cn(
                  bidAlreadySubmitted
                    ? "bg-slate-200 text-slate-600 hover:bg-slate-200"
                    : "bg-emerald-600 hover:bg-emerald-700"
                )}
              >
                {bidAlreadySubmitted ? "Applied" : "Go to bidding"}
              </Button>
            </div>
          </CardContent>
        </Card>
      )}
    </div>
  );
}
