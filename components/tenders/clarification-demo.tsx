"use client";

import React, { useState } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/common/card";
import { Button } from "@/components/common/button";
import { Textarea } from "@/components/common/textarea";
import { Badge } from "@/components/common/badge";
import { Alert, AlertDescription } from "@/components/common/alert";
import { toast } from "sonner";
import { 
  MessageSquare, 
  Send, 
  Settings,
  Globe,
  User,
  CheckCircle,
  AlertTriangle
} from "lucide-react";

interface ERPResponseDemoProps {
  tenderId: string;
  onResponse?: () => void;
}

export default function ClarificationDemo({ tenderId, onResponse }: ERPResponseDemoProps) {
  const [response, setResponse] = useState("");
  const [responseBy, setResponseBy] = useState("Procurement Team");
  const [publishToAll, setPublishToAll] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleERPResponse = async () => {
    if (!response.trim()) {
      toast.error("Please enter a response");
      return;
    }

    setIsSubmitting(true);

    try {
      // Simulate responding to clarification ID 1
      const clarificationId = 1;
      const apiResponse = await fetch(`/api/tender-clarifications/${clarificationId}/respond`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          response: response.trim(),
          responseBy,
          publishToAll,
          status: 'answered',
        }),
      });

      const data = await apiResponse.json();

      if (!apiResponse.ok) {
        throw new Error(data.message || 'Failed to submit response');
      }

      toast.success(`Response submitted successfully! ${publishToAll ? 'Published to all suppliers.' : 'Sent to requesting supplier only.'}`);
      setResponse("");
      
      // Call onResponse callback to refresh parent component
      if (onResponse) {
        onResponse();
      }

    } catch (error) {
      console.error('Error submitting ERP response:', error);
      toast.error(
        error instanceof Error 
          ? error.message 
          : "Failed to submit response"
      );
    } finally {
      setIsSubmitting(false);
    }
  };

  const handlePublishExisting = async () => {
    setIsSubmitting(true);

    try {
      // Simulate publishing existing clarification ID 1 to all suppliers
      const clarificationId = 1;
      const apiResponse = await fetch(`/api/tender-clarifications/${clarificationId}/publish`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          publishToAll: true,
          notifySuppliers: true,
        }),
      });

      const data = await apiResponse.json();

      if (!apiResponse.ok) {
        throw new Error(data.message || 'Failed to publish clarification');
      }

      toast.success("Clarification published to all suppliers successfully!");
      
      // Call onResponse callback to refresh parent component
      if (onResponse) {
        onResponse();
      }

    } catch (error) {
      console.error('Error publishing clarification:', error);
      toast.error(
        error instanceof Error 
          ? error.message 
          : "Failed to publish clarification"
      );
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="space-y-4">
      {/* ERP Response Demo */}
      <Card className="border-green-200 bg-green-50/30">
        <CardHeader>
          <CardTitle className="text-base flex items-center gap-2">
            <Settings className="h-4 w-4 text-green-600" />
            ERP Response Demo
          </CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <Alert>
            <AlertTriangle className="h-4 w-4" />
            <AlertDescription className="text-xs">
              <strong>For ERP Team:</strong> This demonstrates how to respond to supplier clarifications and optionally publish them to all suppliers.
            </AlertDescription>
          </Alert>

          <div className="space-y-2">
            <label className="text-sm font-medium">ERP Response</label>
            <Textarea
              placeholder="Type the response to the supplier's question..."
              value={response}
              onChange={(e) => setResponse(e.target.value)}
              className="min-h-[80px]"
            />
          </div>

          <div className="space-y-2">
            <label className="text-sm font-medium">Response By</label>
            <input
              type="text"
              value={responseBy}
              onChange={(e) => setResponseBy(e.target.value)}
              className="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
              placeholder="e.g., Procurement Team, John Doe"
            />
          </div>

          <div className="flex items-center space-x-2">
            <input
              type="checkbox"
              id="publishToAll"
              checked={publishToAll}
              onChange={(e) => setPublishToAll(e.target.checked)}
              className="rounded border-gray-300"
            />
            <label htmlFor="publishToAll" className="text-sm flex items-center gap-2">
              <Globe className="h-4 w-4 text-blue-600" />
              Publish this response to ALL suppliers
            </label>
          </div>

          <Alert>
            <MessageSquare className="h-4 w-4" />
            <AlertDescription className="text-xs">
              {publishToAll 
                ? "✅ This response will be visible to ALL invited suppliers for transparency."
                : "👤 This response will only be sent to the supplier who asked the question."
              }
            </AlertDescription>
          </Alert>

          <div className="flex gap-2">
            <Button
              onClick={handleERPResponse}
              disabled={isSubmitting || !response.trim()}
              size="sm"
              className="bg-green-600 hover:bg-green-700"
            >
              {isSubmitting ? (
                <div className="flex items-center">
                  <div className="animate-spin rounded-full h-3 w-3 border-b-2 border-white mr-2" />
                  Submitting...
                </div>
              ) : (
                <>
                  <Send className="h-3 w-3 mr-2" />
                  Submit ERP Response
                </>
              )}
            </Button>

            <Button
              onClick={handlePublishExisting}
              disabled={isSubmitting}
              variant="outline"
              size="sm"
              className="border-blue-500 text-blue-600 hover:bg-blue-50"
            >
              {isSubmitting ? (
                <div className="flex items-center">
                  <div className="animate-spin rounded-full h-3 w-3 border-b-2 border-blue-600 mr-2" />
                  Publishing...
                </div>
              ) : (
                <>
                  <Globe className="h-3 w-3 mr-2" />
                  Publish Existing to All
                </>
              )}
            </Button>
          </div>

          <div className="text-xs text-muted-foreground space-y-1">
            <p><strong>API Endpoints Created:</strong></p>
            <p>• <code>PUT /api/tender-clarifications/[id]/respond</code> - ERP responds to clarification</p>
            <p>• <code>PATCH /api/tender-clarifications/[id]/publish</code> - Publish clarification to all suppliers</p>
            <p>• Auto-refresh every 30 seconds to show new responses</p>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
