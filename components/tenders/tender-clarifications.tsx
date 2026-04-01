"use client";

import { useState, useEffect, useCallback } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/common/card";
import { Button } from "@/components/common/button";
import { Textarea } from "@/components/common/textarea";
import { Badge } from "@/components/common/badge";
import { ScrollArea } from "@/components/common/scroll-area";
import { toast } from "sonner";
import { Spinner } from "@/components/common/spinner";
import {
  MessageSquare,
  Send,
  Clock,
  CheckCircle,
  Users,
  User,
  AlertCircle,
  Plus,
  RefreshCw,
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
  onRequestAccess?: () => void;
  canAcceptInvitation?: boolean;
  isOpenTender?: boolean;
}

export default function TenderClarifications({
  tenderId,
  onRequestAccess,
  canAcceptInvitation = false,
  isOpenTender = false,
}: TenderClarificationsProps) {
  const [clarifications, setClarifications] = useState<TenderClarification[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [inlineError, setInlineError] = useState<string | null>(null);
  const [permissionDenied, setPermissionDenied] = useState(false);
  const [accepting, setAccepting] = useState(false);
  const [acceptError, setAcceptError] = useState<string | null>(null);
  const [showNewClarification, setShowNewClarification] = useState(false);
  const [newQuestion, setNewQuestion] = useState("");
  const [isPublic, setIsPublic] = useState(false);
  const [lastRefresh, setLastRefresh] = useState<Date>(new Date());

  const fetchClarifications = useCallback(async (showLoadingIndicator = true) => {
    if (showLoadingIndicator) {
      setIsLoading(true);
    }

    try {
      const response = await fetch(`/api/tender-clarifications?tender_id=${tenderId}`);

      if (!response.ok) {
        const raw = await response.text().catch(() => "");
        const rawText = raw.trim();
        let errorData: any = null;
        try {
          errorData = rawText ? JSON.parse(rawText) : null;
        } catch {
          errorData = null;
        }

        const parsedMessage =
          (typeof errorData === "string" ? errorData : null) ||
          errorData?.message ||
          errorData?.error ||
          errorData?.details?.message;
        const nonEmptyRaw =
          rawText && rawText !== "{}" && rawText !== "null" ? rawText : "";
        const fallbackMessage = `Failed to fetch clarifications: ${response.status} ${response.statusText}`;
        const errorMessage = parsedMessage || nonEmptyRaw || fallbackMessage;

        if (process.env.NODE_ENV !== "production") {
          console.warn("Clarifications request failed", {
            status: response.status,
            statusText: response.statusText,
            message: errorMessage,
          });
        }

        const err = new Error(errorMessage) as Error & { status?: number };
        err.status = response.status;
        throw err;
      }

      const data = await response.json();
      const clarificationsData = data.data || [];

      setClarifications(clarificationsData);
      setInlineError(null);
      setPermissionDenied(false);
      setLastRefresh(new Date()); // Update refresh timestamp
    } catch (error) {
      if (process.env.NODE_ENV !== "production") {
        console.warn("Error fetching clarifications", error);
      }
      const status = (error as { status?: number })?.status;
      const denied = status === 403;
      setPermissionDenied(denied);
      setInlineError(
        error instanceof Error ? error.message : "Unable to load clarifications"
      );
      if (showLoadingIndicator) {
        toast.error("Unable to load clarifications.");
      }
    } finally {
      if (showLoadingIndicator) {
        setIsLoading(false);
      }
    }
  }, [tenderId]);

  // Load clarifications once; no auto-refresh to avoid interrupting long actions on the screen.
  useEffect(() => {
    if (tenderId) {
      fetchClarifications();
    }
  }, [tenderId, fetchClarifications]);

  // Manual refresh function for button
  const handleRefresh = () => {
    fetchClarifications();
    toast.success("Clarifications refreshed.");
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
          is_public: isPublic,
          isPublic,
        }),
      });

      const data = await response.json();

      if (!response.ok) {
        let errorMessage = data.message || 'Unable to submit clarification.';
        console.error('Submit Error:', { status: response.status, data });
        throw new Error(errorMessage);
      }

      toast.success("Clarification submitted successfully.");
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
          : "Unable to submit clarification."
      );
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleAcceptInvitation = async () => {
    try {
      setAccepting(true);
      setAcceptError(null);
      const response = await fetch('/api/tender-invitations', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          tender_id: tenderId,
          response_status: "accepted",
        }),
      });

      const raw = await response.text().catch(() => "");
      let data: any = null;
      try {
        data = raw ? JSON.parse(raw) : null;
      } catch {
        data = null;
      }

      if (!response.ok) {
        const msg = data?.message || data?.error || raw || "Unable to accept invitation.";
        throw new Error(msg);
      }

      toast.success(data?.message || "Invitation accepted.");
      setPermissionDenied(false);
      await fetchClarifications(false);
      onRequestAccess?.();
    } catch (error) {
      setAcceptError(error instanceof Error ? error.message : "Unable to accept invitation.");
      toast.error("Unable to accept invitation.");
    } finally {
      setAccepting(false);
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'answered':
        return 'bg-emerald-50 text-emerald-700 border border-emerald-200';
      case 'pending':
        return 'bg-amber-50 text-amber-700 border border-amber-200';
      case 'closed':
        return 'bg-slate-100 text-slate-600 border border-slate-200';
      default:
        return 'bg-slate-100 text-slate-600 border border-slate-200';
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
      <Card className="border-slate-200/70 bg-white shadow-none">
        <CardContent className="py-8">
          <div className="text-center text-slate-500">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-slate-300 mx-auto mb-4" />
            <p>Loading clarifications...</p>
          </div>
        </CardContent>
      </Card>
    );
  }

  return (
    <div className="space-y-5 max-w-full overflow-hidden">
      {/* Header and Actions */}
      <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div className="space-y-1">
          <div className="flex items-center gap-2">
            <h3 className="text-lg font-semibold tracking-tight text-slate-900">Clarifications</h3>
            <span className="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[11px] font-semibold text-slate-600">
              {clarifications.length}
            </span>
          </div>
          <p className="text-xs text-slate-500">
            Public questions are visible to all suppliers.
          </p>
        </div>
        <div className="flex items-center gap-2">
          {!showNewClarification && (
            <Button
              onClick={() => setShowNewClarification(true)}
              className="h-9 px-4 bg-indigo-600 hover:bg-indigo-700"
              disabled={permissionDenied}
            >
              <Plus className="h-4 w-4 mr-2" />
              New question
            </Button>
          )}
          <Button
            onClick={handleRefresh}
            variant="outline"
            size="icon"
            title="Refresh"
            className="h-9 w-9 border-slate-200 text-slate-600 hover:bg-slate-50"
          >
            <RefreshCw className="h-4 w-4" />
          </Button>
        </div>
      </div>

      {/* New Clarification Form - Always Visible or Expandable */}
      {showNewClarification && !permissionDenied && (
        <Card className="border-indigo-200 bg-indigo-50/30 shadow-none animate-in fade-in slide-in-from-top-4 duration-300">
          <CardHeader className="pb-3">
            <CardTitle className="text-sm flex items-center gap-2 text-indigo-800">
              <MessageSquare className="h-4 w-4" />
              New clarification
            </CardTitle>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <Textarea
                placeholder="Write a concise question for procurement..."
                value={newQuestion}
                onChange={(e) => setNewQuestion(e.target.value)}
                className="min-h-[110px] bg-white border-indigo-200 focus:border-indigo-400 focus:ring-indigo-400"
              />
              <p className="text-xs text-slate-500 text-right">
                {newQuestion.length}/2000
              </p>
            </div>

            <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
              <label className="flex items-center gap-2 text-xs text-slate-600">
                <input
                  type="checkbox"
                  checked={isPublic}
                  onChange={(e) => setIsPublic(e.target.checked)}
                  className="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-600"
                />
                Share with all suppliers
              </label>

              <div className="flex gap-2">
                <Button
                  onClick={() => {
                    setShowNewClarification(false);
                    setNewQuestion("");
                    setIsPublic(false);
                  }}
                  variant="ghost"
                  disabled={isSubmitting}
                  className="text-slate-600 hover:bg-slate-100"
                >
                  Cancel
                </Button>
                <Button
                  onClick={handleSubmitClarification}
                  disabled={isSubmitting || !newQuestion.trim()}
                  className="min-w-[140px] bg-indigo-600 hover:bg-indigo-700"
                >
                  {isSubmitting ? (
                    <>
                      <Spinner className="mr-2 h-4 w-4" />
                      Sending clarification
                    </>
                  ) : (
                    <>
                      <Send className="h-4 w-4 mr-2" />
                      Submit
                    </>
                  )}
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      )}

      {/* Clarifications List */}
      <div className="space-y-3">
        <div className="flex items-center justify-between text-xs text-slate-500 pb-2 border-b border-slate-200/70">
          <span>
            {clarifications.length} question{clarifications.length !== 1 ? 's' : ''}
          </span>
          <span>
            Updated {format(lastRefresh, 'HH:mm')}
          </span>
        </div>
        {inlineError && (
          <div className="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700">
            {inlineError}
          </div>
        )}
        {permissionDenied && (
          <div className="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800 flex flex-wrap items-center justify-between gap-2">
            <span>
              {isOpenTender
                ? "Clarifications are currently unavailable for this tender."
                : "Clarifications are available after you accept the tender invitation."}
            </span>
            {canAcceptInvitation && onRequestAccess && (
              <Button
                size="sm"
                variant="outline"
                className="h-7 px-2 border-amber-200 text-amber-800 hover:bg-amber-100"
                onClick={handleAcceptInvitation}
                disabled={accepting}
              >
                {accepting ? "Accepting..." : "Accept invitation"}
              </Button>
            )}
          </div>
        )}
        {acceptError && (
          <div className="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700">
            {acceptError}
          </div>
        )}

        <ScrollArea className="h-[600px] pr-4 -mr-4">
          <div className="space-y-4 pb-4">
            {permissionDenied ? (
              <div className="text-center py-12 bg-slate-50 rounded-lg border border-dashed border-slate-200/70">
                <AlertCircle className="h-10 w-10 mx-auto text-amber-400 mb-3" />
                <h3 className="text-base font-semibold text-slate-900">Access restricted</h3>
                <p className="text-sm text-slate-500 max-w-sm mx-auto mt-2">
                  {isOpenTender
                    ? "Clarifications are currently unavailable for this tender."
                    : "Accept the invitation to view or ask clarifications."}
                </p>
                {canAcceptInvitation && onRequestAccess && (
                  <Button
                    size="sm"
                    className="mt-4 bg-amber-600 hover:bg-amber-700"
                    onClick={handleAcceptInvitation}
                    disabled={accepting}
                  >
                    {accepting ? "Accepting..." : "Accept invitation"}
                  </Button>
                )}
              </div>
            ) : clarifications.length === 0 ? (
              <div className="text-center py-12 bg-slate-50 rounded-lg border border-dashed border-slate-200/70">
                <MessageSquare className="h-12 w-12 mx-auto text-slate-300 mb-4" />
                <h3 className="text-lg font-medium text-slate-900">No questions yet</h3>
                <p className="text-slate-500 max-w-sm mx-auto mt-2">
                  Have a question about the tender specifications or requirements?
                  Click "Ask Question" above to start.
                </p>
                <Button
                  variant="outline"
                  className="mt-6 border-indigo-200 text-indigo-700 hover:bg-indigo-50"
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
                    "transition-all duration-200 border border-slate-200/70 shadow-none",
                    (clarification.response || clarification.Response || clarification.answer || clarification.Answer)
                      ? "bg-white"
                      : "bg-slate-50/40"
                  )}
                >
                  <CardHeader className="py-3 px-4 border-b border-slate-200/70">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                      <div className="flex items-center gap-2 flex-wrap">
                        <span className="font-mono text-[11px] text-slate-400">#{clarification.clarificationId || index + 1}</span>
                        {(clarification.isPublic || clarification.IsPublic) ? (
                          <Badge variant="outline" className="bg-indigo-50 text-indigo-700 border-indigo-200 gap-1">
                            <Users className="h-3 w-3" /> Public
                          </Badge>
                        ) : (
                          <Badge variant="outline" className="bg-slate-100 text-slate-700 border-slate-200 gap-1">
                            <User className="h-3 w-3" /> Private
                          </Badge>
                        )}
                        <span className="text-[11px] text-slate-500 flex items-center gap-1">
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

                  <CardContent className="p-4 space-y-3">
                    <div className="text-sm text-slate-800 leading-relaxed whitespace-pre-wrap break-words">
                      {clarification.question}
                    </div>

                    {(clarification.response || clarification.Response || clarification.answer || clarification.Answer) && (
                      <div className="rounded-lg border border-emerald-200/70 bg-emerald-50/50 p-3 text-sm text-slate-800">
                        <div className="text-xs font-semibold text-emerald-700 mb-1">Answer</div>
                        <div className="whitespace-pre-wrap break-words">
                          {clarification.response || clarification.Response || clarification.answer || clarification.Answer}
                        </div>
                        <div className="mt-2 text-xs text-emerald-700 flex items-center gap-1">
                          <CheckCircle className="h-3 w-3" />
                          <span>
                            {clarification.responseBy || clarification.ResponseBy || clarification.AnswerBy || 'Procurement Team'}
                            {' • '}
                            {(clarification.responseDate || clarification.ResponseDate || clarification.answerDate || clarification.AnswerDate) &&
                              format(new Date(clarification.responseDate || clarification.ResponseDate || clarification.answerDate || clarification.AnswerDate!), 'MMM d, HH:mm')
                            }
                          </span>
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
