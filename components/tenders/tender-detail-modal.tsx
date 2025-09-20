"use client";

import React, { useState, useEffect } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/common/dialog";
import { Button } from "@/components/common/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/common/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/common/tabs";
import { Badge } from "@/components/common/badge";
import { Separator } from "@/components/common/separator";
import {
  Calendar,
  DollarSign,
  Building,
  Clock,
  FileText,
  MessageSquare,
  Send,
  CheckCircle,
  XCircle,
  AlertTriangle,
  Download,
  Upload,
} from "lucide-react";
import { format } from "date-fns";
import { cn } from "@/lib/utils";
import TenderResponseForm from "./tender-response-form";
import TenderClarifications from "./tender-clarifications";
import TenderBidForm from "./tender-bid-form";

interface Tender {
  id: number;              // Database Id (t_Tenders.Id)
  tenderNo: string;
  title: string;
  tenderType: string;
  tenderCategory: string;
  scopeOfWork: string;
  instructions: string;
  submissionDeadline: string;
  openingDate: string;
  status: string;
  estimatedValue?: string | null;
  currency?: {
    code: string;
    symbol: string;
  };
  procurementMode?: {
    name: string;
  };
  tenderCategoryRelation?: {
    tenderCategory: string;
  };
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

interface TenderDetailModalProps {
  isOpen: boolean;
  onClose: () => void;
  tender: Tender | null;
  invitation?: TenderInvitation | null;
  onInvitationUpdate?: () => void;
}

export default function TenderDetailModal({
  isOpen,
  onClose,
  tender,
  invitation,
  onInvitationUpdate,
}: TenderDetailModalProps) {
  const [activeTab, setActiveTab] = useState("overview");

  if (!tender) return null;

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'pb': return 'bg-green-100 text-green-800';
      case 'dr': return 'bg-gray-100 text-gray-800';
      case 'cl': return 'bg-red-100 text-red-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getStatusText = (status: string) => {
    switch (status) {
      case 'pb': return 'Open';
      case 'dr': return 'Draft';
      case 'cl': return 'Closed';
      default: return status;
    }
  };

  const getResponseStatusColor = (status: string) => {
    switch (status) {
      case 'accepted': return 'text-green-600';
      case 'declined': return 'text-red-600';
      case 'submitted': return 'text-blue-600';
      case 'pending': return 'text-yellow-600';
      default: return 'text-gray-600';
    }
  };

  const getResponseStatusIcon = (status: string) => {
    switch (status) {
      case 'accepted': return <CheckCircle className="h-4 w-4" />;
      case 'declined': return <XCircle className="h-4 w-4" />;
      case 'submitted': return <Send className="h-4 w-4" />;
      case 'pending': return <AlertTriangle className="h-4 w-4" />;
      default: return <Clock className="h-4 w-4" />;
    }
  };

  const formatCurrency = (amount: string | null | undefined, currencyCode: string = 'KES') => {
    if (!amount) return 'Not specified';
    return new Intl.NumberFormat('en-KE', {
      style: 'currency',
      currency: currencyCode,
    }).format(parseFloat(amount));
  };

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="max-w-[98vw] w-full h-[90vh] flex flex-col">
        <DialogHeader className="flex-shrink-0 pb-4 border-b">
          <DialogTitle className="flex items-center justify-between">
            <div className="min-w-0 flex-1">
              <h2 className="text-2xl font-bold truncate">{tender.title}</h2>
              <p className="text-sm text-muted-foreground mt-1">
                {tender.tenderNo}
              </p>
            </div>
            <Badge className={cn("ml-4 flex-shrink-0", getStatusColor(tender.status))}>
              {getStatusText(tender.status)}
            </Badge>
          </DialogTitle>
        </DialogHeader>

        <Tabs value={activeTab} onValueChange={setActiveTab} className="flex flex-col flex-1 overflow-hidden">
          <TabsList className="grid w-full grid-cols-5 flex-shrink-0 bg-muted rounded-md">
            <TabsTrigger value="overview">Overview</TabsTrigger>
            <TabsTrigger value="response">Response</TabsTrigger>
            <TabsTrigger value="clarifications">Clarifications</TabsTrigger>
            <TabsTrigger value="bidding">Bidding</TabsTrigger>
            <TabsTrigger value="documents">Documents</TabsTrigger>
          </TabsList>

          <div className="flex-1 mt-4 overflow-y-auto">
            <TabsContent value="overview" className="space-y-6 px-4 py-2">
              {/* Invitation Status */}
              {invitation && (
                <Card>
                  <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                      <MessageSquare className="h-5 w-5" />
                      Invitation Status
                    </CardTitle>
                  </CardHeader>
                  <CardContent>
                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-2">
                        {getResponseStatusIcon(invitation.ResponseStatus || invitation.responseStatus || 'pending')}
                        <span className={cn("font-medium capitalize", getResponseStatusColor(invitation.ResponseStatus || invitation.responseStatus || 'pending'))}>
                          {invitation.ResponseStatus || invitation.responseStatus || 'pending'}
                        </span>
                      </div>
                      <div className="text-sm text-muted-foreground">
                        Invited: {format(new Date(invitation.InvitationDate || invitation.invitationDate!), 'PPP')}
                        {(invitation.ResponseDate || invitation.responseDate) && (
                          <span className="ml-4">
                            Responded: {format(new Date(invitation.ResponseDate || invitation.responseDate!), 'PPP')}
                          </span>
                        )}
                      </div>
                    </div>
                    {(invitation.DeclineReason || invitation.declineReason) && (
                      <div className="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <p className="text-sm text-red-800">
                          <strong>Decline Reason:</strong> {invitation.DeclineReason || invitation.declineReason}
                        </p>
                      </div>
                    )}
                  </CardContent>
                </Card>
              )}

              {/* Tender Information */}
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <Card>
                  <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                      <Building className="h-5 w-5" />
                      Tender Details
                    </CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-4">
                    <div>
                      <label className="text-sm font-medium text-muted-foreground">Category</label>
                      <p className="text-sm">{tender.tenderCategoryRelation?.tenderCategory || 'Not specified'}</p>
                    </div>
                    <div>
                      <label className="text-sm font-medium text-muted-foreground">Type</label>
                      <p className="text-sm">{tender.tenderType === 'op' ? 'Open to All' : 'Restricted'}</p>
                    </div>
                    <div>
                      <label className="text-sm font-medium text-muted-foreground">Procurement Mode</label>
                      <p className="text-sm">{tender.procurementMode?.name || 'Not specified'}</p>
                    </div>
                  </CardContent>
                </Card>

                <Card>
                  <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                      <DollarSign className="h-5 w-5" />
                      Financial Information
                    </CardTitle>
                  </CardHeader>
                  <CardContent className="space-y-4">
                    <div>
                      <label className="text-sm font-medium text-muted-foreground">Estimated Value</label>
                      <p className="text-sm font-semibold">
                        {formatCurrency(tender.estimatedValue, tender.currency?.code)}
                      </p>
                    </div>
                    <div>
                      <label className="text-sm font-medium text-muted-foreground">Currency</label>
                      <p className="text-sm">{tender.currency?.code || 'KES'}</p>
                    </div>
                  </CardContent>
                </Card>

                <Card className="md:col-span-2">
                  <CardHeader>
                    <CardTitle className="flex items-center gap-2">
                      <Calendar className="h-5 w-5" />
                      Important Dates
                    </CardTitle>
                  </CardHeader>
                  <CardContent className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label className="text-sm font-medium text-muted-foreground">Submission Deadline</label>
                      <p className="text-sm font-semibold text-red-600">
                        {format(new Date(tender.submissionDeadline), 'PPP p')}
                      </p>
                    </div>
                    <div>
                      <label className="text-sm font-medium text-muted-foreground">Opening Date</label>
                      <p className="text-sm">
                        {format(new Date(tender.openingDate), 'PPP p')}
                      </p>
                    </div>
                  </CardContent>
                </Card>
              </div>

              {/* Scope of Work */}
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <FileText className="h-5 w-5" />
                    Scope of Work
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="prose prose-sm max-w-none">
                    <p className="whitespace-pre-wrap">{tender.scopeOfWork}</p>
                  </div>
                </CardContent>
              </Card>

              {/* Instructions */}
              {tender.instructions && (
                <Card>
                  <CardHeader>
                    <CardTitle>Instructions to Bidders</CardTitle>
                  </CardHeader>
                  <CardContent>
                    <div className="prose prose-sm max-w-none">
                      <p className="whitespace-pre-wrap">{tender.instructions}</p>
                    </div>
                  </CardContent>
                </Card>
              )}
            </TabsContent>

            <TabsContent value="response" className="mt-0 data-[state=active]:block data-[state=inactive]:hidden">
              <div className="px-4 py-2">
                <TenderResponseForm
                  tender={tender}
                  invitation={invitation}
                  onUpdate={onInvitationUpdate}
                />
              </div>
            </TabsContent>

            <TabsContent value="clarifications" className="mt-0 data-[state=active]:block data-[state=inactive]:hidden">
              <div className="px-4 py-2">
                <TenderClarifications tenderId={tender.id.toString()} />
              </div>
            </TabsContent>

            <TabsContent value="bidding" className="mt-0 data-[state=active]:block data-[state=inactive]:hidden">
              <div className="px-4 py-2">
                <TenderBidForm tender={{
                  ...tender,
                  id: tender.id.toString()
                }} />
              </div>
            </TabsContent>

            <TabsContent value="documents" className="mt-0 data-[state=active]:block data-[state=inactive]:hidden">
              <div className="px-4 py-2">
              <Card>
                <CardHeader>
                  <CardTitle className="flex items-center gap-2">
                    <FileText className="h-5 w-5" />
                    Tender Documents
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="space-y-4">
                    <div className="flex items-center justify-between p-4 border rounded-lg">
                      <div className="flex items-center gap-3">
                        <FileText className="h-8 w-8 text-blue-600" />
                        <div>
                          <p className="font-medium">Tender Document.pdf</p>
                          <p className="text-sm text-muted-foreground">2.4 MB • PDF</p>
                        </div>
                      </div>
                      <Button variant="outline" size="sm">
                        <Download className="h-4 w-4 mr-2" />
                        Download
                      </Button>
                    </div>
                    
                    <div className="flex items-center justify-between p-4 border rounded-lg">
                      <div className="flex items-center gap-3">
                        <FileText className="h-8 w-8 text-green-600" />
                        <div>
                          <p className="font-medium">Bill of Quantities.xlsx</p>
                          <p className="text-sm text-muted-foreground">1.8 MB • Excel</p>
                        </div>
                      </div>
                      <Button variant="outline" size="sm">
                        <Download className="h-4 w-4 mr-2" />
                        Download
                      </Button>
                    </div>
                    
                    <div className="text-center py-8 text-muted-foreground">
                      <FileText className="h-12 w-12 mx-auto mb-2 opacity-50" />
                      <p>Additional documents will appear here</p>
                    </div>
                  </div>
                </CardContent>
              </Card>
              </div>
            </TabsContent>
          </div>
        </Tabs>
      </DialogContent>
    </Dialog>
  );
}
