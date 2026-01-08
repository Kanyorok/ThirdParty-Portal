"use client";

import { useState, useTransition } from "react";
import { Button } from "@/components/common/button";
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from "@/components/common/sheet";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/common/select";
import { Input } from "@/components/common/input";
import { Label } from "@/components/common/label";
import { Textarea } from "@/components/common/textarea";
import { toast } from "sonner";
import { Loader2, Plus, UploadCloud } from "lucide-react";
import { MAINTENANCE_CATEGORIES, PRIORITY_LEVELS, CreateMaintenanceRequestPayload } from "@/types/maintenance";
// import { maintenanceService } from "@/lib/api/maintenance"; // Assuming this is where it will be used

export function MaintenanceRequestSheet({ 
    children, 
    onSuccess 
}: { 
    children?: React.ReactNode;
    onSuccess?: () => void;
}) {
    const [open, setOpen] = useState(false);
    const [isPending, startTransition] = useTransition();
    
    // Mock properties for now - in real implementation fetch from useLeases hook
    const properties = [
        { id: 1, name: "Sunset Apartments - Unit 101" },
        { id: 2, name: "Downtown Loft - Unit 3B" }
    ];

    const [formData, setFormData] = useState<Partial<CreateMaintenanceRequestPayload>>({
        priority: "Medium",
        category: "Plumbing"
    });

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        
        if (!formData.title || !formData.description || !formData.propertyId) {
            toast.error("Please fill in all required fields");
            return;
        }

        startTransition(async () => {
            try {
                // await maintenanceService.createRequest(formData as CreateMaintenanceRequestPayload, accessToken);
                // Mock delay
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                toast.success("Maintenance request submitted successfully");
                setOpen(false);
                setFormData({ priority: "Medium", category: "Plumbing" });
                onSuccess?.();
            } catch (error) {
                toast.error("Failed to submit request");
            }
        });
    };

    return (
        <Sheet open={open} onOpenChange={setOpen}>
            <SheetTrigger asChild>
                {children || (
                    <Button className="rounded-2xl h-11 px-6 font-bold uppercase tracking-widest text-[11px] bg-primary hover:bg-primary/90 shadow-lg shadow-primary/25">
                        <Plus className="h-4 w-4 mr-2" />
                        New Request
                    </Button>
                )}
            </SheetTrigger>
            <SheetContent className="w-full sm:max-w-xl bg-background border-l-border/60 p-0 overflow-y-auto">
                <div className="p-8 border-b border-border/40 bg-secondary/10">
                    <SheetTitle className="text-2xl font-black tracking-tight mb-2">
                        New Maintenance Request
                    </SheetTitle>
                    <SheetDescription className="font-medium text-muted-foreground/80">
                        Submit a new service request for your property.
                    </SheetDescription>
                </div>

                <form onSubmit={handleSubmit} className="p-8 space-y-8">
                    <div className="space-y-4">
                        <div className="space-y-2">
                            <Label className="text-xs font-bold uppercase tracking-wider text-muted-foreground">Property <span className="text-red-500">*</span></Label>
                            <Select 
                                onValueChange={(val) => setFormData(prev => ({ ...prev, propertyId: parseInt(val) }))}
                            >
                                <SelectTrigger className="h-12 rounded-xl bg-secondary/20 border-border/40 font-medium">
                                    <SelectValue placeholder="Select Property" />
                                </SelectTrigger>
                                <SelectContent>
                                    {properties.map(prop => (
                                        <SelectItem key={prop.id} value={prop.id.toString()}>
                                            {prop.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="space-y-2">
                            <Label className="text-xs font-bold uppercase tracking-wider text-muted-foreground">Issue Title <span className="text-red-500">*</span></Label>
                            <Input 
                                placeholder="e.g. Leaking Kitchen Sink"
                                className="h-12 rounded-xl bg-secondary/20 border-border/40 font-medium"
                                value={formData.title || ""}
                                onChange={e => setFormData(prev => ({ ...prev, title: e.target.value }))}
                            />
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                           <div className="space-y-2">
                                <Label className="text-xs font-bold uppercase tracking-wider text-muted-foreground">Category</Label>
                                <Select 
                                    defaultValue="Plumbing"
                                    onValueChange={(val) => setFormData(prev => ({ ...prev, category: val }))}
                                >
                                    <SelectTrigger className="h-12 rounded-xl bg-secondary/20 border-border/40 font-medium">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {MAINTENANCE_CATEGORIES.map(cat => (
                                            <SelectItem key={cat} value={cat}>{cat}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div> 

                            <div className="space-y-2">
                                <Label className="text-xs font-bold uppercase tracking-wider text-muted-foreground">Priority</Label>
                                <Select 
                                    defaultValue="Medium"
                                    onValueChange={(val) => setFormData(prev => ({ ...prev, priority: val }))}
                                >
                                    <SelectTrigger className="h-12 rounded-xl bg-secondary/20 border-border/40 font-medium">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {PRIORITY_LEVELS.map(level => (
                                            <SelectItem key={level.value} value={level.value}>
                                                <div className="flex items-center gap-2">
                                                    <div className={`h-2 w-2 rounded-full ${level.color}`} />
                                                    {level.label}
                                                </div>
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label className="text-xs font-bold uppercase tracking-wider text-muted-foreground">Description <span className="text-red-500">*</span></Label>
                            <Textarea 
                                placeholder="Please describe the issue in detail..."
                                className="min-h-[120px] rounded-xl bg-secondary/20 border-border/40 font-medium resize-none p-4"
                                value={formData.description || ""}
                                onChange={e => setFormData(prev => ({ ...prev, description: e.target.value }))}
                            />
                        </div>
                        
                        <div className="space-y-2">
                             <Label className="text-xs font-bold uppercase tracking-wider text-muted-foreground">Attachments/Photos</Label>
                             <div className="border-2 border-dashed border-border/60 rounded-xl p-8 flex flex-col items-center justify-center text-center hover:bg-secondary/10 transition-colors cursor-pointer group">
                                <div className="h-10 w-10 rounded-full bg-secondary/30 flex items-center justify-center mb-3 group-hover:bg-primary/10 group-hover:text-primary transition-colors">
                                    <UploadCloud className="h-5 w-5 text-muted-foreground group-hover:text-primary" />
                                </div>
                                <p className="text-sm font-semibold text-muted-foreground max-w-xs">
                                    Click to upload photos or documents related to this request.
                                </p>
                             </div>
                        </div>
                    </div>

                    <div className="flex gap-4 pt-4">
                        <Button 
                            type="button" 
                            variant="outline" 
                            className="flex-1 h-12 rounded-xl font-bold uppercase tracking-wider text-[11px]"
                            onClick={() => setOpen(false)}
                            disabled={isPending}
                        >
                            Cancel
                        </Button>
                        <Button 
                            type="submit" 
                            className="flex-1 h-12 rounded-xl font-bold uppercase tracking-wider text-[11px] shadow-lg shadow-primary/20"
                            disabled={isPending}
                        >
                            {isPending && <Loader2 className="mr-2 h-4 w-4 animate-spin" />}
                            Submit Request
                        </Button>
                    </div>
                </form>
            </SheetContent>
        </Sheet>
    );
}
