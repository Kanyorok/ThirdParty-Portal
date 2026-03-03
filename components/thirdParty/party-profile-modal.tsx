import React from "react"
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetDescription } from "@/components/common/sheet"
import { Form, FormField, FormItem, FormLabel, FormControl, FormMessage } from "@/components/common/form"
import { Input } from "@/components/common/input"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/common/select"
import { Textarea } from "@/components/common/textarea"
import { Button } from "@/components/common/button"
import { Separator } from "@/components/common/separator"
import { Edit, Plus, Save, X } from "lucide-react"
import { ThirdPartyInputs, businessTypeOptions, CountryOption } from "@/types/third-party"
import { UseFormReturn } from "react-hook-form"

export interface ProfileModalProps {
    isOpen: boolean
    onOpenChange: (open: boolean) => void
    form: UseFormReturn<ThirdPartyInputs>
    countries: CountryOption[]
    isEditing: boolean
    onSubmit: (values: ThirdPartyInputs) => void
}

export const ProfileModal: React.FC<ProfileModalProps> = ({ isOpen, onOpenChange, form, countries, isEditing, onSubmit }) => (
    <Sheet open={isOpen} onOpenChange={onOpenChange}>
        <SheetContent className="w-full sm:max-w-xl flex flex-col">
            <SheetHeader>
                <SheetTitle className="text-2xl font-bold flex items-center gap-2">
                    {isEditing ? <Edit className="h-6 w-6 text-primary" /> : <Plus className="h-6 w-6 text-primary" />}
                    {isEditing ? "Edit Company Profile" : "Create Company Profile"}
                </SheetTitle>
                <SheetDescription>
                    {isEditing ? "Update your business's information." : "Fill in details to create your business profile."}
                </SheetDescription>
            </SheetHeader>

            <Separator className="my-4" />

            <Form {...form}>
                <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4 overflow-y-auto pr-4 flex-grow">
                    <FormField control={form.control} name="thirdPartyName" render={({ field }) => <FormItem><FormLabel>Legal Name</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>} />
                    <FormField control={form.control} name="tradingName" render={({ field }) => <FormItem><FormLabel>Trading Name</FormLabel><FormControl><Input {...field} value={field.value ?? ""} /></FormControl><FormMessage /></FormItem>} />
                    <FormField control={form.control} name="businessType" render={({ field }) => <FormItem><FormLabel>Business Type</FormLabel><Select onValueChange={v => field.onChange(Number(v))} value={String(field.value ?? 0)}><SelectTrigger className="w-full"><SelectValue placeholder="Select type" /></SelectTrigger><SelectContent>{businessTypeOptions.map(b => <SelectItem key={b.value} value={String(b.value)}>{b.label}</SelectItem>)}</SelectContent></Select><FormMessage /></FormItem>} />
                    <FormField control={form.control} name="registrationNumber" render={({ field }) => <FormItem><FormLabel>Registration Number</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>} />
                    <FormField control={form.control} name="taxPIN" render={({ field }) => <FormItem><FormLabel>Tax PIN</FormLabel><FormControl><Input {...field} /></FormControl><FormMessage /></FormItem>} />
                    <FormField control={form.control} name="vatNumber" render={({ field }) => <FormItem><FormLabel>VAT Number</FormLabel><FormControl><Input {...field} value={field.value ?? ""} /></FormControl><FormMessage /></FormItem>} />
                    <FormField control={form.control} name="countryId" render={({ field }) => <FormItem><FormLabel>Country</FormLabel><Select onValueChange={v => field.onChange(Number(v))} value={String(field.value ?? 0)} disabled={!countries.length}><SelectTrigger className="w-full"><SelectValue placeholder="Select country" /></SelectTrigger><SelectContent>{countries.map(c => <SelectItem key={c.id} value={String(c.id)}>{c.flag ? `${c.flag} ${c.name}` : c.name}</SelectItem>)}</SelectContent></Select><FormMessage /></FormItem>} />
                    <FormField control={form.control} name="physicalAddress" render={({ field }) => <FormItem><FormLabel>Address</FormLabel><FormControl><Textarea {...field} /></FormControl><FormMessage /></FormItem>} />
                    <FormField control={form.control} name="email" render={({ field }) => <FormItem><FormLabel>Email</FormLabel><FormControl><Input type="email" {...field} /></FormControl><FormMessage /></FormItem>} />
                    <FormField control={form.control} name="phone" render={({ field }) => <FormItem><FormLabel>Phone</FormLabel><FormControl><Input {...field} placeholder="+254..." /></FormControl><FormMessage /></FormItem>} />
                    <FormField control={form.control} name="website" render={({ field }) => <FormItem><FormLabel>Website</FormLabel><FormControl><Input {...field} value={field.value ?? ""} /></FormControl><FormMessage /></FormItem>} />

                    <div className="sticky bottom-0 bg-background pt-4 pb-2 border-t border-border/60 flex justify-end gap-3 z-10">
                        <Button type="button" size="lg" variant="outline" onClick={() => { onOpenChange(false); form.reset() }}><X className="h-4 w-4 mr-2" /> Cancel</Button>
                        <Button type="submit" size="lg"><Save className="h-4 w-4 mr-2" /> {isEditing ? "Save Changes" : "Create Profile"}</Button>
                    </div>
                </form>
            </Form>
        </SheetContent>
    </Sheet>
)
