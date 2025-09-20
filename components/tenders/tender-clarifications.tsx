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
import ERPResponseInterface from "./erp-response-interface";

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
  const [isMockMode, setIsMockMode] = useState(false);
  const [lastRefresh, setLastRefresh] = useState<Date>(new Date());

  const fetchClarifications = async (showLoadingIndicator = true) => {
    if (showLoadingIndicator) {
      setIsLoading(true);
    }
    
    try {
      const response = await fetch(`/api/tender-clarifications?tenderId=${tenderId}`);
      
      if (!response.ok) {
        throw new Error('Failed to fetch clarifications');
      }

      const data = await response.json();
      const clarificationsData = data.data || [];
      
      setClarifications(clarificationsData);
      setIsMockMode(!!data.fallback); // Set mock mode indicator
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
          tenderId,
          question: newQuestion.trim(),
          isPublic,
        }),
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Failed to submit clarification');
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
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <div className="flex items-center gap-2">
            <h3 className="text-lg font-semibold">Tender Clarifications</h3>
            <Badge variant={isMockMode ? "secondary" : "default"} className="text-xs">
              {isMockMode ? "Demo Mode" : "Live"}
            </Badge>
            {clarifications.length > 0 && (
              <Badge variant="outline" className="text-xs">
                {clarifications.length} question{clarifications.length !== 1 ? 's' : ''} | {clarifications.filter(c => c.response || c.Response || c.answer || c.Answer).length} answered
              </Badge>
            )}
          </div>
          <div className="flex items-center gap-4 mt-1">
            <p className="text-sm text-muted-foreground">
              Ask questions or view responses about this tender
            </p>
            <p className="text-xs text-muted-foreground">
              Last updated: {format(lastRefresh, 'HH:mm:ss')}
            </p>
          </div>
        </div>
        <div className="flex items-center gap-2">
          <Button
            onClick={handleRefresh}
            variant="outline"
            size="sm"
            className="flex items-center gap-2"
          >
            <RefreshCw className="h-4 w-4" />
            Refresh
          </Button>
          <Button
            onClick={() => setShowNewClarification(!showNewClarification)}
            size="sm"
          >
            <Plus className="h-4 w-4 mr-2" />
            New Question
          </Button>
        </div>
      </div>

      {/* ERP Response Interface - For Procurement Staff */}
      <ERPResponseInterface 
        clarifications={clarifications}
        onResponseSubmitted={() => fetchClarifications()}
      />

      {/* New Clarification Form */}
      {showNewClarification && (
        <Card className="border-blue-200 bg-blue-50/50">
          <CardHeader>
            <CardTitle className="text-base flex items-center gap-2">
              <MessageSquare className="h-4 w-4" />
              Ask a Question
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <label className="text-sm font-medium">Your Question</label>
              <Textarea
                placeholder="Type your question about this tender..."
                value={newQuestion}
                onChange={(e) => setNewQuestion(e.target.value)}
                className="min-h-[100px]"
              />
            </div>

            <div className="flex items-center space-x-2">
              <input
                type="checkbox"
                id="isPublic"
                checked={isPublic}
                onChange={(e) => setIsPublic(e.target.checked)}
                className="rounded border-gray-300"
              />
              <label htmlFor="isPublic" className="text-sm">
                Make this question visible to all suppliers
              </label>
            </div>

            <Alert>
              <AlertCircle className="h-4 w-4" />
              <AlertDescription className="text-xs">
                {isPublic 
                  ? "This question and its response will be visible to all invited suppliers."
                  : "This question will only be visible to you and the procurement team."
                }
                {isMockMode && (
                  <span className="block mt-1 text-blue-600 font-medium">
                    Note: Currently in demo mode - questions won't be sent to procurement team.
                  </span>
                )}
              </AlertDescription>
            </Alert>

            <div className="flex gap-2">
              <Button
                onClick={handleSubmitClarification}
                disabled={isSubmitting || !newQuestion.trim()}
                size="sm"
              >
                {isSubmitting ? (
                  <div className="flex items-center">
                    <div className="animate-spin rounded-full h-3 w-3 border-b-2 border-white mr-2" />
                    Submitting...
                  </div>
                ) : (
                  <>
                    <Send className="h-3 w-3 mr-2" />
                    Submit Question
                  </>
                )}
              </Button>
              <Button
                onClick={() => {
                  setShowNewClarification(false);
                  setNewQuestion("");
                  setIsPublic(false);
                }}
                variant="outline"
                size="sm"
                disabled={isSubmitting}
              >
                Cancel
              </Button>
            </div>
          </CardContent>
        </Card>
      )}

      {/* Clarifications List */}
      <ScrollArea className="h-[500px]">
        <div className="space-y-4">
          {clarifications.length === 0 ? (
            <Card>
              <CardContent className="py-8">
                <div className="text-center text-muted-foreground">
                  <MessageSquare className="h-12 w-12 mx-auto mb-4 opacity-50" />
                  <p>No clarifications yet</p>
                  <p className="text-sm mt-2">Be the first to ask a question about this tender.</p>
                </div>
              </CardContent>
            </Card>
          ) : (
            clarifications.map((clarification, index) => (
              <Card key={`clarification-${clarification.clarificationId || clarification.id || index}-${clarification.tenderId || 'unknown'}-${index}`} className="relative">
                <CardHeader className="pb-3">
                  <div className="flex items-start justify-between">
                    <div className="flex items-center gap-2 flex-wrap">
                      <Badge className={cn("text-xs", getStatusColor(clarification.status || clarification.Status || 'pending'))}>
                        {getStatusIcon(clarification.status || clarification.Status || 'pending')}
                        <span className="ml-1 capitalize">{clarification.status || clarification.Status || 'pending'}</span>
                      </Badge>
                      
                      <Badge variant={(clarification.isPublic || clarification.IsPublic) ? "default" : "secondary"} className="text-xs">
                        {(clarification.isPublic || clarification.IsPublic) ? (
                          <>
                            <Users className="h-3 w-3 mr-1" />
                            Public
                          </>
                        ) : (
                          <>
                            <User className="h-3 w-3 mr-1" />
                            Private
                          </>
                        )}
                      </Badge>

                      {/* Show if response has been published to all suppliers */}
                      {(clarification.response || clarification.Response || clarification.answer || clarification.Answer) && (clarification.isPublic || clarification.IsPublic) && (
                        <Badge variant="outline" className="text-xs bg-blue-50 text-blue-700">
                          <Globe className="h-3 w-3 mr-1" />
                          Published to All
                        </Badge>
                      )}

                      {/* Show if this is a new response (within last 24 hours) */}
                      {(clarification.responseDate || clarification.ResponseDate || clarification.answerDate || clarification.AnswerDate) && 
                       new Date().getTime() - new Date(clarification.responseDate || clarification.ResponseDate || clarification.answerDate || clarification.AnswerDate!).getTime() < 86400000 && (
                        <Badge variant="outline" className="text-xs bg-green-50 text-green-700 animate-pulse">
                          New Response
                        </Badge>
                      )}
                    </div>
                    <span className="text-xs text-muted-foreground">
                      Asked: {format(new Date(clarification.questionDate), 'MMM dd, HH:mm')}
                    </span>
                  </div>
                </CardHeader>
                
                <CardContent className="space-y-4">
                  {/* Question */}
                  <div className="space-y-2">
                    <div className="flex items-center gap-2">
                      <MessageSquare className="h-4 w-4 text-blue-600" />
                      <span className="text-sm font-medium text-blue-600">Question</span>
                    </div>
                    <div className="bg-blue-50 p-3 rounded-lg border border-blue-200">
                      <p className="text-sm whitespace-pre-wrap">{clarification.question}</p>
                    </div>
                  </div>

                  {/* ERP Response */}
                  {(clarification.response || clarification.Response || clarification.answer || clarification.Answer) && (
                    <div className="space-y-2">
                      <div className="flex items-center justify-between">
                        <div className="flex items-center gap-2">
                          <CheckCircle className="h-4 w-4 text-green-600" />
                          <span className="text-sm font-medium text-green-600">ERP Response</span>
                          {(clarification.isPublic || clarification.IsPublic) && (
                            <Badge variant="outline" className="text-xs bg-blue-50 text-blue-700">
                              <Globe className="h-3 w-3 mr-1" />
                              Visible to All Suppliers
                            </Badge>
                          )}
                        </div>
                        <span className="text-xs text-muted-foreground">
                          {(clarification.responseDate || clarification.ResponseDate || clarification.answerDate || clarification.AnswerDate) && 
                            format(new Date(clarification.responseDate || clarification.ResponseDate || clarification.answerDate || clarification.AnswerDate!), 'MMM dd, HH:mm')
                          }
                        </span>
                      </div>
                      <div className="bg-green-50 p-4 rounded-lg border border-green-200 shadow-sm">
                        <p className="text-sm whitespace-pre-wrap leading-relaxed">
                          {clarification.response || clarification.Response || clarification.answer || clarification.Answer}
                        </p>
                        <div className="mt-3 pt-2 border-t border-green-200">
                          <div className="flex items-center justify-between">
                            <p className="text-xs text-green-700 font-medium">
                              — {clarification.responseBy || clarification.ResponseBy || clarification.AnswerBy || 'Procurement Team'}
                            </p>
                            <div className="flex items-center gap-2 text-xs text-green-600">
                              <CheckCircle className="h-3 w-3" />
                              <span>Official ERP Response</span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  )}

                  {/* Pending status */}
                  {(clarification.status === 'pending' || clarification.Status === 'pending') && !(clarification.response || clarification.Response || clarification.answer || clarification.Answer) && (
                    <div className="bg-yellow-50 p-3 rounded-lg border border-yellow-200">
                      <div className="flex items-center gap-2 text-yellow-800">
                        <Clock className="h-4 w-4" />
                        <span className="text-sm">Waiting for response from procurement team</span>
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
  );
}
