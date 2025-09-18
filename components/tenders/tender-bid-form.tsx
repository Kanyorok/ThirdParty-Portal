"use client";

import React, { useState, useRef } from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/common/card";
import { Button } from "@/components/common/button";
import { Input } from "@/components/common/input";
import { Textarea } from "@/components/common/textarea";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select";
import { Badge } from "@/components/common/badge";
import { Alert, AlertDescription } from "@/components/common/alert";
import { Separator } from "@/components/common/separator";
import { toast } from "sonner";
import { 
  Upload,
  File,
  X,
  DollarSign,
  Calendar,
  Shield,
  FileText,
  CheckCircle,
  AlertTriangle,
  Send,
  Save,
  Lock
} from "lucide-react";
import { cn } from "@/lib/utils";

interface Tender {
  id: string;
  title: string;
  tenderNo: string;
  submissionDeadline: string;
  currency?: {
    code: string;
    symbol: string;
  };
}

interface DocumentUpload {
  file: File;
  documentType: string;
  id: string;
}

interface TenderBidFormProps {
  tender: Tender;
}

export default function TenderBidForm({ tender }: TenderBidFormProps) {
  const [bidData, setBidData] = useState({
    bidAmount: "",
    currency: tender.currency?.code || "KES",
    validityPeriod: "90",
    deliveryPeriod: "30",
    paymentTerms: "",
  });
  
  const [documents, setDocuments] = useState<DocumentUpload[]>([]);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitType, setSubmitType] = useState<'draft' | 'final' | null>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const documentTypes = [
    { value: "technical", label: "Technical Proposal" },
    { value: "financial", label: "Financial Proposal" },
    { value: "compliance", label: "Compliance Documents" },
    { value: "bond", label: "Bid Bond" },
    { value: "other", label: "Other" },
  ];

  const currencies = [
    { code: "KES", symbol: "KSh", name: "Kenyan Shilling" },
    { code: "USD", symbol: "$", name: "US Dollar" },
    { code: "EUR", symbol: "€", name: "Euro" },
    { code: "GBP", symbol: "£", name: "British Pound" },
  ];

  const handleFileUpload = (event: React.ChangeEvent<HTMLInputElement>) => {
    const files = event.target.files;
    if (!files) return;

    Array.from(files).forEach(file => {
      // Check file size (max 50MB)
      if (file.size > 50 * 1024 * 1024) {
        toast.error(`File ${file.name} is too large. Maximum size is 50MB.`);
        return;
      }

      // Check file type
      const allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/jpeg',
        'image/png',
        'application/zip'
      ];

      if (!allowedTypes.includes(file.type)) {
        toast.error(`File ${file.name} has an unsupported format.`);
        return;
      }

      const newDocument: DocumentUpload = {
        file,
        documentType: "other", // Default type
        id: Math.random().toString(36).substring(2, 15),
      };

      setDocuments(prev => [...prev, newDocument]);
    });

    // Reset file input
    if (fileInputRef.current) {
      fileInputRef.current.value = "";
    }
  };

  const removeDocument = (id: string) => {
    setDocuments(prev => prev.filter(doc => doc.id !== id));
  };

  const updateDocumentType = (id: string, type: string) => {
    setDocuments(prev => 
      prev.map(doc => 
        doc.id === id ? { ...doc, documentType: type } : doc
      )
    );
  };

  const formatFileSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
  };

  const validateBid = () => {
    if (!bidData.bidAmount || parseFloat(bidData.bidAmount) <= 0) {
      toast.error("Please enter a valid bid amount");
      return false;
    }

    if (!bidData.validityPeriod || parseInt(bidData.validityPeriod) <= 0) {
      toast.error("Please enter a valid validity period");
      return false;
    }

    if (!bidData.deliveryPeriod || parseInt(bidData.deliveryPeriod) <= 0) {
      toast.error("Please enter a valid delivery period");
      return false;
    }

    if (documents.length === 0) {
      toast.error("Please upload at least one document");
      return false;
    }

    // Check for required document types
    const hasFinancial = documents.some(doc => doc.documentType === "financial");
    const hasTechnical = documents.some(doc => doc.documentType === "technical");
    
    if (!hasFinancial) {
      toast.error("Financial proposal document is required");
      return false;
    }

    if (!hasTechnical) {
      toast.error("Technical proposal document is required");
      return false;
    }

    return true;
  };

  const handleSubmit = async (type: 'draft' | 'final') => {
    if (type === 'final' && !validateBid()) {
      return;
    }

    setIsSubmitting(true);
    setSubmitType(type);

    try {
      const formData = new FormData();
      formData.append('tenderId', tender.id);
      formData.append('bidAmount', bidData.bidAmount);
      formData.append('currency', bidData.currency);
      formData.append('validityPeriod', bidData.validityPeriod);
      formData.append('deliveryPeriod', bidData.deliveryPeriod);
      formData.append('paymentTerms', bidData.paymentTerms);
      formData.append('status', type === 'draft' ? 'draft' : 'submitted');

      // Add documents
      documents.forEach((doc, index) => {
        formData.append('documents', doc.file);
        formData.append('documentTypes', doc.documentType);
      });

      const response = await fetch('/api/tender-bids', {
        method: 'POST',
        body: formData,
      });

      const data = await response.json();

      if (!response.ok) {
        throw new Error(data.message || 'Failed to submit bid');
      }

      toast.success(
        type === 'draft' 
          ? "Bid saved as draft successfully!" 
          : "Bid submitted successfully! Your documents have been encrypted and stored securely."
      );

      // Reset form if final submission
      if (type === 'final') {
        setBidData({
          bidAmount: "",
          currency: tender.currency?.code || "KES",
          validityPeriod: "90",
          deliveryPeriod: "30",
          paymentTerms: "",
        });
        setDocuments([]);
      }

    } catch (error) {
      console.error('Error submitting bid:', error);
      toast.error(
        error instanceof Error 
          ? error.message 
          : "Failed to submit bid"
      );
    } finally {
      setIsSubmitting(false);
      setSubmitType(null);
    }
  };

  return (
    <div className="space-y-6">
      {/* Security Notice */}
      <Alert className="border-blue-200 bg-blue-50">
        <Shield className="h-4 w-4" />
        <AlertDescription>
          <strong>Secure Bidding Process:</strong> All uploaded documents will be encrypted at rest and 
          remain secure until the tender opening ceremony when decryption keys will be made available 
          to the evaluation committee.
        </AlertDescription>
      </Alert>

      {/* Bid Information */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <DollarSign className="h-5 w-5" />
            Bid Information
          </CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div className="space-y-2">
              <label className="text-sm font-medium">Bid Amount *</label>
              <div className="flex">
                <Select 
                  value={bidData.currency} 
                  onValueChange={(value) => setBidData(prev => ({ ...prev, currency: value }))}
                >
                  <SelectTrigger className="w-[100px]">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    {currencies.map(currency => (
                      <SelectItem key={currency.code} value={currency.code}>
                        {currency.code}
                      </SelectItem>
                    ))}
                  </SelectContent>
                </Select>
                <Input
                  type="number"
                  placeholder="Enter bid amount"
                  value={bidData.bidAmount}
                  onChange={(e) => setBidData(prev => ({ ...prev, bidAmount: e.target.value }))}
                  className="flex-1 ml-2"
                  step="0.01"
                  min="0"
                />
              </div>
            </div>

            <div className="space-y-2">
              <label className="text-sm font-medium">Bid Validity Period (days) *</label>
              <Input
                type="number"
                placeholder="90"
                value={bidData.validityPeriod}
                onChange={(e) => setBidData(prev => ({ ...prev, validityPeriod: e.target.value }))}
                min="1"
              />
            </div>

            <div className="space-y-2">
              <label className="text-sm font-medium">Delivery Period (days) *</label>
              <Input
                type="number"
                placeholder="30"
                value={bidData.deliveryPeriod}
                onChange={(e) => setBidData(prev => ({ ...prev, deliveryPeriod: e.target.value }))}
                min="1"
              />
            </div>
          </div>

          <div className="space-y-2">
            <label className="text-sm font-medium">Payment Terms</label>
            <Textarea
              placeholder="Describe your preferred payment terms..."
              value={bidData.paymentTerms}
              onChange={(e) => setBidData(prev => ({ ...prev, paymentTerms: e.target.value }))}
              className="min-h-[80px]"
            />
          </div>
        </CardContent>
      </Card>

      {/* Document Upload */}
      <Card>
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <FileText className="h-5 w-5" />
            Supporting Documents
          </CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="border-2 border-dashed border-gray-300 rounded-lg p-6">
            <div className="text-center">
              <Upload className="h-8 w-8 text-gray-400 mx-auto mb-2" />
              <p className="text-sm text-gray-600 mb-2">
                Drop files here or click to upload
              </p>
              <Button
                type="button"
                variant="outline"
                onClick={() => fileInputRef.current?.click()}
                disabled={isSubmitting}
              >
                <Upload className="h-4 w-4 mr-2" />
                Select Files
              </Button>
              <input
                ref={fileInputRef}
                type="file"
                multiple
                className="hidden"
                onChange={handleFileUpload}
                accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip"
              />
            </div>
          </div>

          <Alert>
            <Lock className="h-4 w-4" />
            <AlertDescription className="text-xs">
              <strong>Required Documents:</strong> Financial Proposal, Technical Proposal. 
              <strong> Supported formats:</strong> PDF, Word, Excel, Images, ZIP (Max 50MB per file)
            </AlertDescription>
          </Alert>

          {/* Uploaded Documents */}
          {documents.length > 0 && (
            <div className="space-y-3">
              <Separator />
              <h4 className="text-sm font-medium">Uploaded Documents ({documents.length})</h4>
              <div className="space-y-2">
                {documents.map((doc) => (
                  <div key={doc.id} className="flex items-center gap-3 p-3 border rounded-lg">
                    <File className="h-8 w-8 text-blue-600" />
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-medium truncate">{doc.file.name}</p>
                      <p className="text-xs text-muted-foreground">
                        {formatFileSize(doc.file.size)} • {doc.file.type}
                      </p>
                    </div>
                    <Select 
                      value={doc.documentType} 
                      onValueChange={(value) => updateDocumentType(doc.id, value)}
                    >
                      <SelectTrigger className="w-[160px]">
                        <SelectValue />
                      </SelectTrigger>
                      <SelectContent>
                        {documentTypes.map(type => (
                          <SelectItem key={type.value} value={type.value}>
                            {type.label}
                          </SelectItem>
                        ))}
                      </SelectContent>
                    </Select>
                    <Button
                      type="button"
                      variant="ghost"
                      size="sm"
                      onClick={() => removeDocument(doc.id)}
                      disabled={isSubmitting}
                    >
                      <X className="h-4 w-4" />
                    </Button>
                  </div>
                ))}
              </div>
            </div>
          )}
        </CardContent>
      </Card>

      {/* Submission Actions */}
      <Card>
        <CardContent className="pt-6">
          <div className="flex flex-col sm:flex-row gap-4">
            <Button
              onClick={() => handleSubmit('draft')}
              variant="outline"
              disabled={isSubmitting}
              className="flex-1"
            >
              {isSubmitting && submitType === 'draft' ? (
                <div className="flex items-center">
                  <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-gray-600 mr-2" />
                  Saving...
                </div>
              ) : (
                <>
                  <Save className="h-4 w-4 mr-2" />
                  Save as Draft
                </>
              )}
            </Button>
            
            <Button
              onClick={() => handleSubmit('final')}
              disabled={isSubmitting}
              className="flex-1"
            >
              {isSubmitting && submitType === 'final' ? (
                <div className="flex items-center">
                  <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2" />
                  Submitting...
                </div>
              ) : (
                <>
                  <Send className="h-4 w-4 mr-2" />
                  Submit Final Bid
                </>
              )}
            </Button>
          </div>
          
          <Alert className="mt-4">
            <AlertTriangle className="h-4 w-4" />
            <AlertDescription className="text-xs">
              <strong>Important:</strong> Once you submit your final bid, it cannot be modified. 
              You can save as draft to continue working on it later.
            </AlertDescription>
          </Alert>
        </CardContent>
      </Card>
    </div>
  );
}
