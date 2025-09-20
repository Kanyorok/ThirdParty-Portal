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
     
    </div>
  );
}
