"use client";

import React, { useState } from "react";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/components/common/dialog";
import { Button } from "@/components/common/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/common/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/common/tabs";
import { Badge } from "@/components/common/badge";
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
  documents?: {
    id: number;
    fileName: string;
    extension: string;
    fileSize: string;
    module: string;
    createdOn: string;
  }[];
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

  const formatCurrency = (amount: string | null | undefined, currencyCode?: string) => {
    if (!amount) return 'Not specified';
    try {
      return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: currencyCode || 'KES', // Only fall back if absolutely necessary, but preferably use the one from tender
      }).format(parseFloat(amount));
    } catch (e) {
      // Fallback for invalid currency codes
      return `${currencyCode || ''} ${parseFloat(amount).toLocaleString()}`;
    }
  };

  return (
    <Dialog open={isOpen} onOpenChange={onClose}>
      <DialogContent className="sm:max-w-[95vw] w-full h-[85vh] flex flex-col p-0 gap-0 overflow-hidden bg-white" showCloseButton={false}>
        <div className="flex flex-col h-full overflow-hidden">
            <DialogHeader className="px-6 py-4 flex flex-row items-center justify-between gap-4 space-y-0 flex-shrink-0 bg-white border-b shadow-sm z-10">
                <div className="flex items-center gap-4 min-w-0 flex-1">
                    <div className="h-10 w-10 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0">
                        <FileText className="h-5 w-5 text-primary" />
                    </div>
                    <div className="min-w-0 flex-1">
                        <DialogTitle className="text-xl font-bold truncate text-gray-900 leadings-tight">
                            {tender.title}
                        </DialogTitle>
                         <div className="flex items-center gap-2 text-sm text-muted-foreground mt-0.5">
                            <span className="font-medium">{tender.tenderNo}</span>
                            <span className="w-1 h-1 rounded-full bg-gray-300" />
                            <Badge variant="secondary" className={cn("rounded-md px-2 py-0 text-xs font-medium capitalize", getStatusColor(tender.status))}>
                                {getStatusText(tender.status)}
                            </Badge>
                             {invitation && (
                                <>
                                    <span className="w-1 h-1 rounded-full bg-gray-300" />
                                     <span className={cn("text-xs font-medium capitalize flex items-center gap-1", getResponseStatusColor(invitation.ResponseStatus || invitation.responseStatus || 'pending'))}>
                                        {getResponseStatusIcon(invitation.ResponseStatus || invitation.responseStatus || 'pending')}
                                        {invitation.ResponseStatus || invitation.responseStatus || 'pending'}
                                    </span>
                                </>
                             )}
                        </div>
                    </div>
                </div>
                
                <div className="flex items-center gap-2">
                    <Button variant="ghost" size="icon" className="rounded-full h-8 w-8 hover:bg-gray-100" onClick={onClose}>
                        <XCircle className="h-5 w-5 text-gray-500" />
                        <span className="sr-only">Close</span>
                    </Button>
                </div>
            </DialogHeader>

            <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full flex-1 flex flex-col overflow-hidden">
                <div className="px-6 border-t overflow-x-auto scrollbar-hide">
                     <TabsList className="h-12 w-full justify-start gap-6 bg-transparent p-0 min-w-max">
                        {['Overview', 'Response', 'Clarifications', 'Bidding', 'Documents'].map((tab) => (
                          <TabsTrigger 
                            key={tab} 
                            value={tab.toLowerCase()}
                            className="relative h-12 rounded-none border-b-2 border-transparent px-1 pb-3 pt-3 font-medium text-muted-foreground data-[state=active]:border-primary data-[state=active]:text-primary data-[state=active]:shadow-none hover:text-gray-900 transition-colors"
                          >
                            {tab}
                          </TabsTrigger>
                        ))}
                    </TabsList>
                </div>

                <div className="flex-1 overflow-y-auto bg-white min-h-0">
                    <TabsContent value="overview" className="min-h-full p-8 mt-0 focus-visible:outline-none">
                        <div className="max-w-7xl mx-auto space-y-8">
                             {/* Key Stats Row */}
                            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                <div className="bg-white p-4 rounded-xl border shadow-sm flex flex-col justify-between h-24">
                                    <span className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Submission Deadline</span>
                                    <div className="flex items-center gap-2 text-red-600">
                                        <Clock className="h-4 w-4" />
                                        <span className="font-bold text-lg">{format(new Date(tender.submissionDeadline), 'MMM d, yyyy')}</span>
                                    </div>
                                    <span className="text-xs text-muted-foreground">{format(new Date(tender.submissionDeadline), 'p')}</span>
                                </div>
                                <div className="bg-white p-4 rounded-xl border shadow-sm flex flex-col justify-between h-24">
                                    <span className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Estimated Value</span>
                                    <div className="flex items-center gap-2 text-gray-900">
                                        <DollarSign className="h-4 w-4 text-green-600" />
                                        <span className="font-bold text-lg">{formatCurrency(tender.estimatedValue, tender.currency?.code)}</span>
                                    </div>
                                     <span className="text-xs text-muted-foreground">{tender.currency?.code || 'KES'}</span>
                                </div>
                                 <div className="bg-white p-4 rounded-xl border shadow-sm flex flex-col justify-between h-24">
                                    <span className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Tender Type</span>
                                    <div className="flex items-center gap-2 text-gray-900">
                                        <Building className="h-4 w-4 text-blue-600" />
                                        <span className="font-bold text-lg">{tender.tenderType === 'op' ? 'Open Tender' : 'Restricted'}</span>
                                    </div>
                                    <span className="text-xs text-muted-foreground">{tender.procurementMode?.name || 'Standard Mode'}</span>
                                </div>
                                <div className="bg-white p-4 rounded-xl border shadow-sm flex flex-col justify-between h-24">
                                    <span className="text-xs font-semibold text-muted-foreground uppercase tracking-wider">Opening Date</span>
                                    <div className="flex items-center gap-2 text-gray-900">
                                        <Calendar className="h-4 w-4 text-purple-600" />
                                        <span className="font-bold text-lg">{format(new Date(tender.openingDate), 'MMM d, yyyy')}</span>
                                    </div>
                                    <span className="text-xs text-muted-foreground">{format(new Date(tender.openingDate), 'p')}</span>
                                </div>
                            </div>

                            <div className="grid grid-cols-1 xl:grid-cols-3 gap-8">
                                <div className="xl:col-span-2 space-y-8">
                                    {/* Scope of Work */}
                                    <div className="bg-white rounded-xl border shadow-sm overflow-hidden">
                                        <div className="px-6 py-4 border-b bg-gray-50/30 flex items-center justify-between">
                                            <h3 className="font-semibold text-gray-900 flex items-center gap-2">
                                                <FileText className="h-4 w-4 text-blue-500" />
                                                Scope of Work
                                            </h3>
                                        </div>
                                         <div className="p-6 prose prose-gray max-w-none">
                                            <p className="whitespace-pre-wrap text-sm leading-relaxed text-gray-600">{tender.scopeOfWork}</p>
                                        </div>
                                    </div>

                                    {/* Instructions */}
                                    {tender.instructions && (
                                        <div className="bg-white rounded-xl border shadow-sm overflow-hidden">
                                            <div className="px-6 py-4 border-b bg-gray-50/30 flex items-center justify-between">
                                                <h3 className="font-semibold text-gray-900 flex items-center gap-2">
                                                    <Building className="h-4 w-4 text-blue-500" />
                                                    Instructions to Bidders
                                                </h3>
                                            </div>
                                             <div className="p-6 prose prose-gray max-w-none">
                                                <p className="whitespace-pre-wrap text-sm leading-relaxed text-gray-600">{tender.instructions}</p>
                                            </div>
                                        </div>
                                    )}
                                </div>

                                <div className="space-y-8">
                                     {/* Quick Details */}
                                     <div className="bg-white rounded-xl border shadow-sm overflow-hidden">
                                        <div className="px-6 py-4 border-b bg-gray-50/30">
                                            <h3 className="font-semibold text-gray-900">Evaluation & Category</h3>
                                        </div>
                                        <div className="p-6 space-y-4">
                                            <div>
                                                <label className="text-xs font-medium text-muted-foreground uppercase tracking-wide">Category</label>
                                                <div className="mt-1 font-medium text-gray-900">{tender.tenderCategoryRelation?.tenderCategory || 'General'}</div>
                                            </div>
                                             <div className="pt-4 border-t">
                                                <div className="flex items-start gap-3">
                                                    <div className="p-2 bg-blue-50 rounded text-blue-600"><FileText className="h-4 w-4"/></div>
                                                    <div>
                                                        <div className="font-medium text-sm text-gray-900">Documents Required</div>
                                                        <div className="text-xs text-muted-foreground mt-0.5">Please check the Documents tab for full requirements.</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                     </div>

                                     {/* Action Card */}
                                     {(!invitation?.ResponseStatus || invitation.ResponseStatus === 'pending') && (
                                         <div className="bg-blue-50 rounded-xl border border-blue-100 p-6">
                                            <h3 className="font-semibold text-blue-900 mb-2">Ready to Respond?</h3>
                                            <p className="text-sm text-blue-700 mb-4">You have not yet responded to this invitation. Please accept or decline to proceed.</p>
                                            <Button className="w-full" onClick={() => setActiveTab('response')}>
                                                Submit Response
                                            </Button>
                                         </div>
                                     )}
                                </div>
                            </div>
                        </div>
                    </TabsContent>

                    <TabsContent value="response" className="p-8 pb-20 mt-0 focus-visible:outline-none">
                        <div className="max-w-5xl mx-auto">
                            <TenderResponseForm tender={tender} invitation={invitation} onUpdate={onInvitationUpdate} />
                        </div>
                    </TabsContent>
                    
                    <TabsContent value="clarifications" className="p-8 pb-20 mt-0 focus-visible:outline-none">
                         <div className="max-w-5xl mx-auto">
                            <TenderClarifications tenderId={tender.id.toString()} />
                         </div>
                    </TabsContent>

                    <TabsContent value="bidding" className="p-8 pb-20 mt-0 focus-visible:outline-none">
                         <div className="max-w-screen-xl mx-auto">
                             <TenderBidForm 
                                tender={{ ...tender, id: tender.id.toString() }}
                                onFinalSubmitSuccess={() => onClose()}
                            />
                         </div>
                    </TabsContent>

            <TabsContent value="documents" className="p-8 pb-20 mt-0 focus-visible:outline-none">
                        <div className="max-w-5xl mx-auto">
                            <Card className="shadow-sm border-0 ring-1 ring-gray-200">
                                <CardHeader>
                                    <CardTitle>Tender Documents</CardTitle> 
                                </CardHeader>
                                <CardContent>
                                    {tender.documents && tender.documents.length > 0 ? (
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            {tender.documents.map((doc) => {
                                                const ext = doc.extension?.toLowerCase() || 'file';
                                                let iconColor = "text-gray-600";
                                                let bgColor = "bg-gray-50";
                                                
                                                if (['pdf'].includes(ext)) {
                                                    iconColor = "text-red-600";
                                                    bgColor = "bg-red-50";
                                                } else if (['xls', 'xlsx', 'csv'].includes(ext)) {
                                                    iconColor = "text-green-600";
                                                    bgColor = "bg-green-50";
                                                } else if (['doc', 'docx'].includes(ext)) {
                                                    iconColor = "text-blue-600";
                                                    bgColor = "bg-blue-50";
                                                } else if (['jpg', 'jpeg', 'png'].includes(ext)) {
                                                    iconColor = "text-purple-600";
                                                    bgColor = "bg-purple-50";
                                                }

                                                return (
                                                    <div key={doc.id} className={`flex items-center justify-between p-4 border rounded-lg bg-white hover:border-gray-300 transition-colors group cursor-pointer`}>
                                                        <div className="flex items-center gap-3 overflow-hidden">
                                                            <div className={`h-10 w-10 ${bgColor} rounded flex items-center justify-center ${iconColor} flex-shrink-0`}>
                                                                <FileText className="h-5 w-5" />
                                                            </div>
                                                            <div className="min-w-0">
                                                                <p className="font-medium truncate group-hover:text-blue-600 transition-colors" title={doc.fileName}>{doc.fileName}</p>
                                                                <p className="text-xs text-muted-foreground uppercase">{doc.fileSize || 'Unknown Size'} • {ext}</p>
                                                            </div>
                                                        </div>
                                                        <Button variant="ghost" size="icon" className="flex-shrink-0"><Download className="h-4 w-4" /></Button>
                                                    </div>
                                                );
                                            })}
                                        </div>
                                    ) : (
                                        <div className="mt-8 text-center p-8 bg-gray-50 border border-dashed rounded-lg">
                                            <div className="mx-auto h-12 w-12 text-gray-300 mb-2"><FileText className="h-full w-full" /></div>
                                            <h3 className="text-sm font-medium text-gray-900">No additional documents</h3>
                                            <p className="text-xs text-muted-foreground mt-1">All available documents are listed above.</p>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        </div>
                    </TabsContent>
                </div>
            </Tabs>
        </div>
      </DialogContent>
    </Dialog>
  );
}
