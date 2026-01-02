"use client";

import React, { useState, useEffect } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/common/card";
import { Button } from "@/components/common/button";
import { Textarea } from "@/components/common/textarea";
import { Badge } from "@/components/common/badge";
import { ScrollArea } from "@/components/common/scroll-area";
import { Alert, AlertDescription } from "@/components/common/alert";
import { toast } from "sonner";
import {
  MessageSquare,
  Send,
  Clock,
  CheckCircle,
  Users,
  User,
  AlertCircle,
  Plus,
  Paperclip,
  RefreshCw,
  Globe,
  Eye
} from "lucide-react";
import { cn } from "@/lib/utils";
import { format } from "date-fns";


interface TenderClarification {
  id: number;
  tenderId: string;
  supplierId: number;
  question: string;
  questionDate: string;
  // Portal expected field names
  response?: string;
  Response?: string;
  responseDate?: string;
  ResponseDate?: string;
  responseBy?: string;
  ResponseBy?: string;
  // ERP DATABASE ACTUAL FIELD NAMES
  answer?: string;           // ACTUAL API field name (lowercase)!
  answerDate?: string;       // ACTUAL API field name (lowercase)!
  clarificationId?: number;  // ACTUAL API field name for ID!
  Answer?: string;           // Backup capitalized version
  AnswerDate?: string;       // Backup capitalized version
  AnswerBy?: string;         // Possible ERP field name
  status: 'pending' | 'answered' | 'closed';
  Status?: 'pending' | 'answered' | 'closed';
  isPublic: boolean;
  IsPublic?: boolean;
  attachments?: string[];
  createdBy: string;
  createdOn: string;
  modifiedBy?: string;
  modifiedOn?: string;
}

interface TenderClarificationsProps {
  tenderId: string;
}

export default function TenderClarifications({ tenderId }: TenderClarificationsProps) {
  const [clarifications, setClarifications] = useState<TenderClarification[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [showNewClarification, setShowNewClarification] = useState(false);
  const [newQuestion, setNewQuestion] = useState("");
  const [isPublic, setIsPublic] = useState(false);
  const [lastRefresh, setLastRefresh] = useState<Date>(new Date());

  const fetchClarifications = async (showLoadingIndicator = true) => {
    if (showLoadingIndicator) {
      setIsLoading(true);
    }

    try {
      const response = await fetch(`/api/tender-clarifications?tender_id=${tenderId}`);

      if (!response.ok) {
        let errorMessage = 'Failed to fetch clarifications';
        try {
          const errorData = await response.json();
          errorMessage = errorData.message || errorData.error || errorMessage;
          console.error('API Error:', { status: response.status, data: errorData });
        } catch (e) {
          console.error('API Error (Parse Fail):', response.status, response.statusText);
          errorMessage = `Failed to fetch: ${response.status} ${response.statusText}`;
        }
        throw new Error(errorMessage);
      }

      const data = await response.json();
      const clarificationsData = data.data || [];

      setClarifications(clarificationsData);
      setLastRefresh(new Date()); // Update refresh timestamp
    } catch (error) {
      console.error('Error fetching clarifications:', error);
      if (showLoadingIndicator) {
        toast.error("Failed to load clarifications");
      }
    } finally {
      if (showLoadingIndicator) {
        setIsLoading(false);
      }
    }
  };

  // Auto-refresh clarifications every 30 seconds to check for new responses
  useEffect(() => {
    if (tenderId) {
      fetchClarifications();

      // Set up auto-refresh interval
      const refreshInterval = setInterval(() => {
        fetchClarifications(false); // Silent refresh without loading indicator
      }, 30000); // 30 seconds

      return () => clearInterval(refreshInterval);
    }
  }, [tenderId]);

  // Manual refresh function for button
  const handleRefresh = () => {
    fetchClarifications();
    toast.success("Refreshed clarifications");
  };

  const handleSubmitClarification = async () => {
    if (!newQuestion.trim()) {
      toast.error("Please enter your question");
      return;
    }

    setIsSubmitting(true);

    try {
      const response = await fetch('/api/tender-clarifications', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          tender_id: tenderId,
          question: newQuestion.trim(),
          isPublic,
        }),
      });

      const data = await response.json();

      if (!response.ok) {
        let errorMessage = data.message || 'Failed to submit clarification';
        console.error('Submit Error:', { status: response.status, data });
        throw new Error(errorMessage);
      }

      toast.success("Clarification request submitted successfully!");
      setNewQuestion("");
      setIsPublic(false);
      setShowNewClarification(false);

      // Refresh clarifications
      fetchClarifications();

    } catch (error) {
      console.error('Error submitting clarification:', error);
      toast.error(
        error instanceof Error
          ? error.message
          : "Failed to submit clarification request"
      );
    } finally {
      setIsSubmitting(false);
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'answered':
        return 'bg-green-100 text-green-800';
      case 'pending':
        return 'bg-yellow-100 text-yellow-800';
      case 'closed':
        return 'bg-gray-100 text-gray-800';
      default:
        return 'bg-gray-100 text-gray-800';
    }
  };

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'answered':
        return <CheckCircle className="h-4 w-4" />;
      case 'pending':
        return <Clock className="h-4 w-4" />;
      case 'closed':
        return <AlertCircle className="h-4 w-4" />;
      default:
        return <Clock className="h-4 w-4" />;
    }
  };

  if (isLoading) {
    return (
      <Card>
        <CardContent className="py-8">
          <div className="text-center text-muted-foreground">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-300 mx-auto mb-4" />
            <p>Loading clarifications...</p>
          </div>
        </CardContent>
      </Card>
    );
  }

  return (
    <div className="space-y-6 max-w-full overflow-hidden">
      {/* Header and Actions */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h3 className="text-xl font-bold tracking-tight text-gray-900">Tender Clarifications</h3>
          <p className="text-sm text-gray-500 mt-1">
            Ask questions or view responses about this tender.
          </p>
        </div>
        <div className="flex items-center gap-2">
          {!showNewClarification && (
            <Button
              onClick={() => setShowNewClarification(true)}
              className="w-full sm:w-auto"
            >
              <Plus className="h-4 w-4 mr-2" />
              Ask Question
            </Button>
          )}
          <Button
            onClick={handleRefresh}
            variant="outline"
            size="icon"
            title="Refresh"
          >
            <RefreshCw className="h-4 w-4" />
          </Button>
        </div>
      </div>

      {/* New Clarification Form - Always Visible or Expandable */}
      {showNewClarification && (
        <Card className="border-blue-200 bg-blue-50/30 animate-in fade-in slide-in-from-top-4 duration-300">
          <CardHeader>
            <CardTitle className="text-base flex items-center gap-2 text-blue-800">
              <MessageSquare className="h-5 w-5" />
              Submit a New Question
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <Textarea
                placeholder="Type your question clearly here..."
                value={newQuestion}
                onChange={(e) => setNewQuestion(e.target.value)}
                className="min-h-[120px] bg-white border-blue-200 focus:border-blue-400 focus:ring-blue-400"
              />
              <p className="text-xs text-gray-500 text-right">
                {newQuestion.length}/2000 characters
              </p>
            </div>

            <div className="flex flex-col sm:flex-row gap-4 sm:items-center justify-between">
              <div className="flex items-start sm:items-center gap-3 bg-white p-3 rounded-md border border-gray-100 flex-1">
                <div className="flex h-6 items-center">
                  <input
                    type="checkbox"
                    id="isPublic"
                    checked={isPublic}
                    onChange={(e) => setIsPublic(e.target.checked)}
                    className="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-600"
                  />
                </div>
                <div className="text-sm">
                  <label htmlFor="isPublic" className="font-medium text-gray-900">
                    Mark as Public?
                  </label>
                  <p className="text-gray-500 text-xs">
                    {isPublic
                      ? "Visible to ALL suppliers. Good for general clarifications."
                      : "Visible ONLY to you and Procurement. Use for sensitive info."
                    }
                  </p>
                </div>
              </div>

              <div className="flex gap-2 justify-end pt-2 sm:pt-0">
                <Button
                  onClick={() => {
                    setShowNewClarification(false);
                    setNewQuestion("");
                    setIsPublic(false);
                  }}
                  variant="ghost"
                  disabled={isSubmitting}
                >
                  Cancel
                </Button>
                <Button
                  onClick={handleSubmitClarification}
                  disabled={isSubmitting || !newQuestion.trim()}
                  className="min-w-[140px]"
                >
                  {isSubmitting ? (
                    <>
                      <RefreshCw className="mr-2 h-4 w-4 animate-spin" />
                      Sending...
                    </>
                  ) : (
                    <>
                      <Send className="h-4 w-4 mr-2" />
                      Submit Question
                    </>
                  )}
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      )}

      {/* Clarifications List */}
      <div className="space-y-4">
        <div className="flex items-center justify-between text-sm text-gray-500 pb-2 border-b">
          <span>
            Showing {clarifications.length} question{clarifications.length !== 1 ? 's' : ''}
          </span>
          <span className="text-xs">
            Last updated: {format(lastRefresh, 'HH:mm')}
          </span>
        </div>

        <ScrollArea className="h-[600px] pr-4 -mr-4">
          <div className="space-y-4 pb-4">
            {clarifications.length === 0 ? (
              <div className="text-center py-12 bg-gray-50 rounded-lg border border-dashed border-gray-200">
                <MessageSquare className="h-12 w-12 mx-auto text-gray-300 mb-4" />
                <h3 className="text-lg font-medium text-gray-900">No questions yet</h3>
                <p className="text-gray-500 max-w-sm mx-auto mt-2">
                  Have a question about the tender specifications or requirements?
                  Click "Ask Question" above to start.
                </p>
                <Button
                  variant="outline"
                  className="mt-6"
                  onClick={() => setShowNewClarification(true)}
                >
                  Ask First Question
                </Button>
              </div>
            ) : (
              clarifications.map((clarification, index) => (
                <Card
                  key={`clarification-${clarification.clarificationId || clarification.id || index}`}
                  className={cn(
                    "transition-all duration-200 border-l-4",
                    (clarification.response || clarification.Response || clarification.answer || clarification.Answer)
                      ? "border-l-green-500 border-gray-200"
                      : "border-l-yellow-400 border-gray-200"
                  )}
                >
                  <CardHeader className="py-3 px-4 bg-gray-50/50">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                      <div className="flex items-center gap-3 flex-wrap">
                        <span className="font-mono text-xs text-gray-400">#{clarification.clarificationId || index + 1}</span>
                        {(clarification.isPublic || clarification.IsPublic) ? (
                          <Badge variant="outline" className="bg-blue-50 text-blue-700 border-blue-200 gap-1">
                            <Users className="h-3 w-3" /> Public
                          </Badge>
                        ) : (
                          <Badge variant="outline" className="bg-gray-100 text-gray-700 border-gray-200 gap-1">
                            <User className="h-3 w-3" /> Private
                          </Badge>
                        )}
                        <span className="text-xs text-gray-500 flex items-center gap-1">
                          <Clock className="h-3 w-3" />
                          {format(new Date(clarification.questionDate), 'MMM d, yyyy HH:mm')}
                        </span>
                      </div>
                      <Badge
                        className={cn("capitalize shadow-none", getStatusColor(clarification.status || clarification.Status || 'pending'))}
                        variant="secondary"
                      >
                        {getStatusIcon(clarification.status || clarification.Status || 'pending')}
                        <span className="ml-1">{clarification.status || clarification.Status || 'pending'}</span>
                      </Badge>
                    </div>
                  </CardHeader>

                  <CardContent className="p-4 space-y-4">
                    {/* Question Section */}
                    <div className="grid grid-cols-[24px_1fr] gap-3">
                      <div className="mt-1">
                        <div className="h-6 w-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center">
                          <span className="text-xs font-bold">Q</span>
                        </div>
                      </div>
                      <div className="text-gray-800 text-sm leading-relaxed whitespace-pre-wrap break-words">
                        {clarification.question}
                      </div>
                    </div>

                    {/* Answer Section (if exists) */}
                    {(clarification.response || clarification.Response || clarification.answer || clarification.Answer) && (
                      <div className="mt-4 pt-4 border-t border-gray-100 grid grid-cols-[24px_1fr] gap-3 bg-green-50/30 -mx-4 px-4 pb-2">
                        <div className="mt-1">
                          <div className="h-6 w-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center">
                            <span className="text-xs font-bold">A</span>
                          </div>
                        </div>
                        <div className="space-y-2">
                          <div className="text-gray-800 text-sm leading-relaxed whitespace-pre-wrap break-words font-medium">
                            {clarification.response || clarification.Response || clarification.answer || clarification.Answer}
                          </div>
                          <div className="flex items-center gap-2 text-xs text-green-700 pt-1">
                            <CheckCircle className="h-3 w-3" />
                            <span>
                              Answered by {clarification.responseBy || clarification.ResponseBy || clarification.AnswerBy || 'Procurement Team'}
                              {' • '}
                              {(clarification.responseDate || clarification.ResponseDate || clarification.answerDate || clarification.AnswerDate) &&
                                format(new Date(clarification.responseDate || clarification.ResponseDate || clarification.answerDate || clarification.AnswerDate!), 'MMM d, HH:mm')
                              }
                            </span>
                          </div>
                        </div>
                      </div>
                    )}
                  </CardContent>
                </Card>
              ))
            )}
          </div>
        </ScrollArea>
      </div>
    </div>
  );
}
