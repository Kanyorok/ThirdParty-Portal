"use client";

import React, { useState } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/common/card";
import { Button } from "@/components/common/button";
import { Textarea } from "@/components/common/textarea";
import { Alert, AlertDescription } from "@/components/common/alert";
import { toast } from "sonner";
import { getBaseUrl } from "@/lib/api-base";
import { 
  CheckCircle, 
  XCircle, 
  AlertTriangle, 
  Clock, 
  Send 
} from "lucide-react";
import { cn } from "@/lib/utils";

interface Tender {
  id: number;              // Database Id (t_Tenders.Id)
  title: string;
  tenderNo: string;
  tenderType: string; // 'op' = Open, 'rs' = Restricted
  submissionDeadline: string;
}

interface TenderInvitation {
  InvitationID?: number;
  invitationID?: number;   // Laravel lowercase
  TenderId?: number;       // Database Id (t_Tenders.Id) - uppercase  
  tenderId?: number;       // Laravel lowercase
  ResponseStatus?: 'pending' | 'accepted' | 'declined' | 'submitted';
  responseStatus?: 'pending' | 'accepted' | 'declined' | 'submitted'; // Laravel lowercase
  ResponseDate?: string;
  responseDate?: string;   // Laravel lowercase
  DeclineReason?: string;
  declineReason?: string;  // Laravel lowercase
  InvitationDate?: string;
  invitationDate?: string; // Laravel lowercase
}

interface TenderResponseFormProps {
  tender: Tender;
  invitation?: TenderInvitation | null;
  onUpdate?: () => void;
}

export default function TenderResponseForm({
  tender,
  invitation,
  onUpdate,
}: TenderResponseFormProps) {
  const [isLoading, setIsLoading] = useState(false);
  const [responseStatus, setResponseStatus] = useState<'accepted' | 'declined' | null>(null);
  const [declineReason, setDeclineReason] = useState("");
  
  // Component ready for production use

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
        invitation_id: invitation?.InvitationID || invitation?.invitationID,
        response_status: status,
        decline_reason: status === 'declined' ? declineReason : null,
        // Alternative field names for Laravel compatibility
        invitationId: invitation?.InvitationID || invitation?.invitationID,
        responseStatus: status,
        declineReason: status === 'declined' ? declineReason : null,
      };
      
      const invitationId = invitation?.InvitationID || invitation?.invitationID;
      const apiUrl = `${getBaseUrl()}/api/tender-invitations/${invitationId}`;
      
      const response = await fetch(apiUrl, {
        method: 'PUT',
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
      } catch (parseError) {
        data = { parseError: 'Invalid JSON response' };
      }

      if (!response.ok) {
        // Provide user-friendly error messages
        let errorMessage = 'Failed to update invitation response';
        if (data.message) errorMessage = data.message;
        else if (data.error) errorMessage = data.error;
        else if (response.status === 404) errorMessage = 'API endpoint not found';
        else if (response.status === 422) errorMessage = 'Invalid request data';
        else if (response.status === 500) errorMessage = 'Server error - please try again';
        
        throw new Error(errorMessage);
      }

      toast.success(
        status === 'accepted' 
          ? "Tender invitation accepted successfully!" 
          : "Tender invitation declined successfully!"
      );

      if (onUpdate) {
        onUpdate();
      }

    } catch (error) {
      console.error('Error updating invitation response:', error);
      toast.error(
        error instanceof Error 
          ? error.message 
          : "Failed to update invitation response"
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
        return <CheckCircle className="h-5 w-5 text-green-600" />;
      case 'declined':
        return <XCircle className="h-5 w-5 text-red-600" />;
      case 'submitted':
        return <Send className="h-5 w-5 text-blue-600" />;
      case 'pending':
        return <Clock className="h-5 w-5 text-yellow-600" />;
      default:
        return <AlertTriangle className="h-5 w-5 text-gray-600" />;
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
        return 'border-green-200 bg-green-50';
      case 'declined':
        return 'border-red-200 bg-red-50';
      case 'submitted':
        return 'border-blue-200 bg-blue-50';
      case 'pending':
        return 'border-yellow-200 bg-yellow-50';
      default:
        return 'border-gray-200 bg-gray-50';
    }
  };

  // Check if this is an open tender
  const isOpenTender = tender.tenderType === 'op';
  const isRestrictedTender = tender.tenderType === 'rs';

  if (!invitation && isOpenTender) {
    return (
      <Card className="border-blue-200 bg-blue-50">
        <CardContent className="py-8">
          <div className="text-center text-blue-800">
            <CheckCircle className="h-12 w-12 mx-auto mb-4 text-blue-600" />
            <p className="font-medium text-lg">Open Tender</p>
            <p className="text-sm mt-2">This tender is open to all suppliers. No invitation response required.</p>
            <p className="text-sm mt-1 font-medium">You can proceed directly to the Bidding section to submit your proposal.</p>
          </div>
        </CardContent>
      </Card>
    );
  }

  if (!invitation && isRestrictedTender) {
    return (
      <Card className="border-gray-200 bg-gray-50">
        <CardContent className="py-8">
          <div className="text-center text-muted-foreground">
            <AlertTriangle className="h-12 w-12 mx-auto mb-4 opacity-50" />
            <p className="font-medium">Restricted Tender</p>
            <p className="text-sm mt-2">This is a restricted tender. You have not been invited to participate.</p>
            <p className="text-sm mt-1">Only invited suppliers can respond to this tender.</p>
          </div>
        </CardContent>
      </Card>
    );
  }

  const currentStatus = invitation?.ResponseStatus || invitation?.responseStatus || 'pending';
  const isResponseSubmitted = currentStatus !== 'pending';
  const canRespond = currentStatus === 'pending' && isRestrictedTender;

  return (
    <div className="space-y-6">
      {/* Current Status */}
      <Card className={cn("border-2", getStatusColor(currentStatus))}>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            {getStatusIcon(currentStatus)}
            Invitation Response Status
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <span className="text-sm text-muted-foreground">Current Status:</span>
              <span className="font-medium capitalize">
                {getStatusText(currentStatus)}
              </span>
            </div>
            
            {(invitation?.ResponseDate || invitation?.responseDate) && (
              <div className="flex items-center justify-between">
                <span className="text-sm text-muted-foreground">Response Date:</span>
                <span className="text-sm">
                  {new Date(invitation?.ResponseDate || invitation?.responseDate!).toLocaleDateString()}
                </span>
              </div>
            )}

            {(invitation?.DeclineReason || invitation?.declineReason) && (
              <div className="mt-4 p-3 bg-red-100 border border-red-200 rounded-lg">
                <p className="text-sm text-red-800">
                  <strong>Decline Reason:</strong> {invitation?.DeclineReason || invitation?.declineReason}
                </p>
              </div>
            )}
          </div>
        </CardContent>
      </Card>

      {/* Response Actions */}
      {!isRestrictedTender && invitation && (
        <Card className="border-blue-200 bg-blue-50">
          <CardContent className="py-4">
            <div className="flex items-center gap-2 text-blue-800">
              <CheckCircle className="h-5 w-5" />
              <div>
                <p className="font-medium">Open Tender</p>
                <p className="text-sm">
                  This is an open tender. No invitation response required. You can proceed directly to bidding.
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      )}

      {canRespond && (
        <Card>
          <CardHeader>
            <CardTitle>Respond to Restricted Tender Invitation</CardTitle>
          </CardHeader>
          <CardContent className="space-y-6">
            <Alert>
              <AlertTriangle className="h-4 w-4" />
              <AlertDescription>
                This is a restricted tender invitation. Please carefully review the tender documents before responding.
                Once you accept, you'll be able to proceed with the bidding process.
              </AlertDescription>
            </Alert>
            

            <div className="space-y-4">
              <div className="flex gap-4">
                <Button
                  onClick={() => setResponseStatus('accepted')}
                  variant={responseStatus === 'accepted' ? 'default' : 'outline'}
                  className="flex-1"
                  disabled={isLoading}
                >
                  <CheckCircle className="h-4 w-4 mr-2" />
                  Accept Invitation
                </Button>
                <Button
                  onClick={() => setResponseStatus('declined')}
                  variant={responseStatus === 'declined' ? 'destructive' : 'outline'}
                  className="flex-1"
                  disabled={isLoading}
                >
                  <XCircle className="h-4 w-4 mr-2" />
                  Decline Invitation
                </Button>
              </div>

              {responseStatus === 'declined' && (
                <div className="space-y-2">
                  <label className="text-sm font-medium">
                    Reason for declining (required)
                  </label>
                  <Textarea
                    placeholder="Please provide a brief explanation for declining this tender invitation..."
                    value={declineReason}
                    onChange={(e) => setDeclineReason(e.target.value)}
                    className="min-h-[100px]"
                  />
                </div>
              )}

              {responseStatus && (
                <div className="flex gap-4">
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
                  >
                    Cancel
                  </Button>
                </div>
              )}
            </div>
          </CardContent>
        </Card>
      )}

      {/* Response Not Available for Restricted Tenders */}
      {isRestrictedTender && invitation && invitation.ResponseStatus === 'pending' && !canRespond && (
        <Card className="border-yellow-200 bg-yellow-50">
          <CardContent className="py-4">
            <div className="flex items-center gap-2 text-yellow-800">
              <AlertTriangle className="h-5 w-5" />
              <div>
                <p className="font-medium">Response Required</p>
                <p className="text-sm">
                  This restricted tender requires a response, but there may be an issue with your invitation status.
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      )}

      {/* Next Steps */}
      {currentStatus === 'accepted' && (
        <Card className="border-green-200 bg-green-50">
          <CardContent className="py-4">
            <div className="flex items-center gap-2 text-green-800">
              <CheckCircle className="h-5 w-5" />
              <div>
                <p className="font-medium">{isRestrictedTender ? 'Restricted Tender Invitation Accepted!' : 'Ready to Proceed!'}</p>
                <p className="text-sm">
                  You can now proceed to the "Clarifications" tab to ask questions or the "Bidding" tab to submit your proposal.
                </p>
              </div>
            </div>
          </CardContent>
        </Card>
      )}
    </div>
  );
}
