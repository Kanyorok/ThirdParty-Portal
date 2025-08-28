// 'use client';

// import React, { useState, useEffect } from 'react';
// import { useSession } from 'next-auth/react';
// import { useForm } from 'react-hook-form';
// import { zodResolver } from '@hookform/resolvers/zod';
// import { z } from 'zod';
// import { toast } from 'sonner';
// import { Save, Loader2, Info, Edit, XCircle } from 'lucide-react';
// import { motion } from 'framer-motion';

// import { Button } from '@/components/common/button';
// import { Input } from '@/components/common/input';
// import {
//     Form,
//     FormControl,
//     FormField,
//     FormItem,
//     FormLabel,
//     FormMessage,
// } from '@/components/common/form';
// import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/common/select';

// export enum BusinessTypeEnum {
//     SoleProprietorship = 1,
//     Partnership = 2,
//     LimitedCompany = 3,
//     NonProfit = 4,
//     Other = 5,
// }

// const businessTypeOptions = [
//     { value: BusinessTypeEnum.SoleProprietorship, label: 'Sole Proprietorship' },
//     { value: BusinessTypeEnum.Partnership, label: 'Partnership' },
//     { value: BusinessTypeEnum.LimitedCompany, label: 'Limited Company' },
//     { value: BusinessTypeEnum.NonProfit, label: 'Non-Profit' },
//     { value: BusinessTypeEnum.Other, label: 'Other' },
// ];

// const thirdPartySchema = z.object({
//     id: z.number().optional(),
//     thirdPartyName: z.string().min(1, { message: "Legal Name is required." }),
//     tradingName: z.string().nullable().optional().transform(e => e === "" ? null : e),
//     businessType: z.coerce.number().min(1, { message: "Business Type is required." }).int({ message: "Invalid business type." }),
//     registrationNumber: z.string().min(1, { message: "Registration Number is required." }),
//     taxPIN: z.string().min(1, { message: "Tax PIN is required." }),
//     vatNumber: z.string().nullable().optional().transform(e => e === "" ? null : e),
//     country: z.string().min(1, { message: "Country is required." }),
//     physicalAddress: z.string().min(1, { message: "Physical Address is required." }),
//     email: z.string().email({ message: "Invalid email address." }),
//     phone: z.string().min(1, { message: "Phone number is required." }).regex(/^\+?[0-9()\s-]+$/, { message: "Invalid phone number format." }),
//     website: z.string().url({ message: "Invalid URL format." }).nullable().optional().transform(e => e === "" ? null : e),
// });

// type ThirdPartyInputs = z.infer<typeof thirdPartySchema>;

// interface ThirdPartyProfile {
//     id: number;
//     thirdPartyName: string;
//     tradingName: string | null;
//     businessType: BusinessTypeEnum;
//     registrationNumber: string;
//     taxPIN: string;
//     vatNumber: string | null;
//     country: string;
//     physicalAddress: string;
//     email: string;
//     phone: string;
//     website: string | null;
//     createdOn: string;
//     modifiedOn: string | null;
//     status: number;
//     thirdPartyType: number;
//     approvalStatus: number;
// }

// export default function ThirdPartyDetailsForm() {
//     const { data: session, status } = useSession();
//     const [thirdPartyDetails, setThirdPartyDetails] = useState<ThirdPartyProfile | null>(null);
//     const [isLoading, setIsLoading] = useState(true);
//     const [isEditing, setIsEditing] = useState(false);

//     const form = useForm<ThirdPartyInputs>({
//         resolver: zodResolver(thirdPartySchema),
//         defaultValues: {
//             thirdPartyName: '',
//             tradingName: '',
//             businessType: BusinessTypeEnum.SoleProprietorship,
//             registrationNumber: '',
//             taxPIN: '',
//             vatNumber: '',
//             country: '',
//             physicalAddress: '',
//             email: '',
//             phone: '',
//             website: '',
//         },
//     });

//     const thirdPartyId = session?.user?.thirdParty?.id;

//     const fetchThirdPartyDetails = async () => {
//         if (status === 'loading' || !thirdPartyId) {
//             setIsLoading(false);
//             return;
//         }

//         setIsLoading(true);
//         try {
//             const response = await fetch(`/api/third-party-details`);
//             if (!response.ok) {
//                 const errorData = await response.json();
//                 throw new Error(errorData.message || 'Failed to fetch third party details.');
//             }
//             const responseData: ThirdPartyProfile = await response.json();
//             setThirdPartyDetails(responseData);
//             form.reset({
//                 id: responseData.id,
//                 thirdPartyName: responseData.thirdPartyName || '',
//                 tradingName: responseData.tradingName || '',
//                 businessType: responseData.businessType,
//                 registrationNumber: responseData.registrationNumber || '',
//                 taxPIN: responseData.taxPIN || '',
//                 vatNumber: responseData.vatNumber || '',
//                 country: responseData.country || '',
//                 physicalAddress: responseData.physicalAddress || '',
//                 email: responseData.email || '',
//                 phone: responseData.phone || '',
//                 website: responseData.website || '',
//             });
//         } catch (error: any) {
//             toast.error(error.message || 'Error fetching third party details.');
//             setThirdPartyDetails(null);
//         } finally {
//             setIsLoading(false);
//         }
//     };

//     useEffect(() => {
//         if (status === 'authenticated') {
//             fetchThirdPartyDetails();
//         } else if (status === 'unauthenticated') {
//             setIsLoading(false);
//         }
//     }, [thirdPartyId, status]);

//     const handleFormSubmit = async (data: ThirdPartyInputs) => {
//         if (!thirdPartyId || !session?.accessToken) {
//             toast.error('Authentication or Third Party ID missing. Cannot save details.');
//             return;
//         }

//         const payload = { ...data };

//         toast.promise(
//             (async () => {
//                 const response = await fetch(`/api/third-party-details`, {
//                     method: 'PUT',
//                     headers: {
//                         'Content-Type': 'application/json',
//                         'Accept': 'application/json',
//                         'Authorization': `Bearer ${session.accessToken}`,
//                     },
//                     body: JSON.stringify(payload),
//                 });

//                 const responseData = await response.json();

//                 if (!response.ok) {
//                     const errorMessage = responseData.message || 'Failed to update details.';
//                     const errorDetails = responseData.errors ? Object.values(responseData.errors).flat().join('\n') : '';
//                     throw new Error(`${errorMessage}\n${errorDetails}`);
//                 }
//                 await fetchThirdPartyDetails();
//                 setIsEditing(false);
//                 return responseData.message || 'Third party details updated successfully!';
//             })(),
//             {
//                 loading: 'Updating details...',
//                 success: (message) => message,
//                 error: (error) => error.message,
//             }
//         );
//     };

//     const handleCancelEdit = () => {
//         setIsEditing(false);
//         if (thirdPartyDetails) {
//             form.reset({
//                 id: thirdPartyDetails.id,
//                 thirdPartyName: thirdPartyDetails.thirdPartyName || '',
//                 tradingName: thirdPartyDetails.tradingName || '',
//                 businessType: thirdPartyDetails.businessType,
//                 registrationNumber: thirdPartyDetails.registrationNumber || '',
//                 taxPIN: thirdPartyDetails.taxPIN || '',
//                 vatNumber: thirdPartyDetails.vatNumber || '',
//                 country: thirdPartyDetails.country || '',
//                 physicalAddress: thirdPartyDetails.physicalAddress || '',
//                 email: thirdPartyDetails.email || '',
//                 phone: thirdPartyDetails.phone || '',
//                 website: thirdPartyDetails.website || '',
//             });
//         }
//     };

//     const getBusinessTypeName = (value: BusinessTypeEnum) => {
//         const option = businessTypeOptions.find(opt => opt.value === value);
//         return option ? option.label : 'N/A';
//     };

//     if (status === 'loading') {
//         return (
//             <div className="flex justify-center items-center h-48 bg-white rounded-lg border border-gray-200">
//                 <Loader2 className="animate-spin h-8 w-8 text-gray-500" />
//                 <p className="ml-2 text-gray-600">Loading user session...</p>
//             </div>
//         );
//     }

//     if (!thirdPartyDetails && !isLoading) {
//         return (
//             <motion.div
//                 initial={{ opacity: 0, y: 20 }}
//                 animate={{ opacity: 1, y: 0 }}
//                 className="flex flex-col items-center justify-center p-8 border border-dashed rounded-lg bg-gray-50 text-gray-600"
//             >
//                 <Info className="h-10 w-10 mb-3 text-gray-400" />
//                 <p className="text-lg font-medium">No third party details found.</p>
//                 <p className="text-sm mt-1">Please contact support if this is unexpected.</p>
//             </motion.div>
//         );
//     }

//     return (
//         <div className="p-6 bg-white rounded-lg border border-gray-200">
//             <div className="flex justify-between items-center mb-6">
//                 <h2 className="text-2xl font-semibold text-gray-800">My Company Details</h2>
//                 {!isLoading && !isEditing && (
//                     <Button onClick={() => setIsEditing(true)} variant="outline" className="flex items-center">
//                         <Edit className="mr-2 h-4 w-4" /> Edit
//                     </Button>
//                 )}
//             </div>

//             {isLoading ? (
//                 <div className="flex justify-center items-center h-96 bg-white rounded-lg border border-gray-200">
//                     <Loader2 className="animate-spin h-8 w-8 text-gray-500" />
//                     <p className="ml-2 text-gray-600">Loading company details...</p>
//                 </div>
//             ) : (
//                 <motion.div
//                     initial={{ opacity: 0, y: 20 }}
//                     animate={{ opacity: 1, y: 0 }}
//                     transition={{ duration: 0.3, ease: "easeInOut" }}
//                     className="overflow-hidden p-4 rounded-lg bg-gray-50 border"
//                 >
//                     {isEditing ? (
//                         <Form {...form}>
//                             <form onSubmit={form.handleSubmit(handleFormSubmit)} className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
//                                 <FormField
//                                     control={form.control}
//                                     name="thirdPartyName"
//                                     render={({ field }) => (
//                                         <FormItem>
//                                             <FormLabel>Legal Company Name</FormLabel>
//                                             <FormControl>
//                                                 <Input placeholder="e.g., Acme Innovations Ltd." {...field} />
//                                             </FormControl>
//                                             <FormMessage />
//                                         </FormItem>
//                                     )}
//                                 />
//                                 <FormField
//                                     control={form.control}
//                                     name="tradingName"
//                                     render={({ field }) => (
//                                         <FormItem>
//                                             <FormLabel>Trading Name (Optional)</FormLabel>
//                                             <FormControl>
//                                                 <Input placeholder="e.g., Acme Solutions" {...field} value={field.value ?? ''} />
//                                             </FormControl>
//                                             <FormMessage />
//                                         </FormItem>
//                                     )}
//                                 />
//                                 <FormField
//                                     control={form.control}
//                                     name="businessType"
//                                     render={({ field }) => (
//                                         <FormItem>
//                                             <FormLabel>Business Type</FormLabel>
//                                             <Select onValueChange={value => field.onChange(parseInt(value))} value={String(field.value)}>
//                                                 <FormControl>
//                                                     <SelectTrigger>
//                                                         <SelectValue placeholder="Select business type" />
//                                                     </SelectTrigger>
//                                                 </FormControl>
//                                                 <SelectContent>
//                                                     {businessTypeOptions.map((type) => (
//                                                         <SelectItem key={type.value} value={String(type.value)}>
//                                                             {type.label}
//                                                         </SelectItem>
//                                                     ))}
//                                                 </SelectContent>
//                                             </Select>
//                                             <FormMessage />
//                                         </FormItem>
//                                     )}
//                                 />
//                                 <FormField
//                                     control={form.control}
//                                     name="registrationNumber"
//                                     render={({ field }) => (
//                                         <FormItem>
//                                             <FormLabel>Registration Number</FormLabel>
//                                             <FormControl>
//                                                 <Input placeholder="e.g., PVT/20XX/XXXX" {...field} />
//                                             </FormControl>
//                                             <FormMessage />
//                                         </FormItem>
//                                     )}
//                                 />
//                                 <FormField
//                                     control={form.control}
//                                     name="taxPIN"
//                                     render={({ field }) => (
//                                         <FormItem>
//                                             <FormLabel>Tax PIN</FormLabel>
//                                             <FormControl>
//                                                 <Input placeholder="e.g., AXXXXXXXXX" {...field} />
//                                             </FormControl>
//                                             <FormMessage />
//                                         </FormItem>
//                                     )}
//                                 />
//                                 <FormField
//                                     control={form.control}
//                                     name="vatNumber"
//                                     render={({ field }) => (
//                                         <FormItem>
//                                             <FormLabel>VAT Number (Optional)</FormLabel>
//                                             <FormControl>
//                                                 <Input placeholder="e.g., 0123456K" {...field} value={field.value ?? ''} />
//                                             </FormControl>
//                                             <FormMessage />
//                                         </FormItem>
//                                     )}
//                                 />
//                                 <FormField
//                                     control={form.control}
//                                     name="country"
//                                     render={({ field }) => (
//                                         <FormItem>
//                                             <FormLabel>Country</FormLabel>
//                                             <FormControl>
//                                                 <Input placeholder="e.g., Kenya" {...field} />
//                                             </FormControl>
//                                             <FormMessage />
//                                         </FormItem>
//                                     )}
//                                 />
//                                 <FormField
//                                     control={form.control}
//                                     name="physicalAddress"
//                                     render={({ field }) => (
//                                         <FormItem>
//                                             <FormLabel>Physical Address</FormLabel>
//                                             <FormControl>
//                                                 <Input placeholder="e.g., 123 Main St, Nairobi" {...field} />
//                                             </FormControl>
//                                             <FormMessage />
//                                         </FormItem>
//                                     )}
//                                 />
//                                 <FormField
//                                     control={form.control}
//                                     name="email"
//                                     render={({ field }) => (
//                                         <FormItem>
//                                             <FormLabel>Email Address</FormLabel>
//                                             <FormControl>
//                                                 <Input type="email" placeholder="e.g., info@acme.com" {...field} />
//                                             </FormControl>
//                                             <FormMessage />
//                                         </FormItem>
//                                     )}
//                                 />
//                                 <FormField
//                                     control={form.control}
//                                     name="phone"
//                                     render={({ field }) => (
//                                         <FormItem>
//                                             <FormLabel>Phone Number</FormLabel>
//                                             <FormControl>
//                                                 <Input type="tel" placeholder="e.g., +2547XXXXXXXX" {...field} />
//                                             </FormControl>
//                                             <FormMessage />
//                                         </FormItem>
//                                     )}
//                                 />
//                                 <FormField
//                                     control={form.control}
//                                     name="website"
//                                     render={({ field }) => (
//                                         <FormItem>
//                                             <FormLabel>Website (Optional)</FormLabel>
//                                             <FormControl>
//                                                 <Input type="url" placeholder="e.g., https://www.acme.com" {...field} value={field.value ?? ''} />
//                                             </FormControl>
//                                             <FormMessage />
//                                         </FormItem>
//                                     )}
//                                 />
//                                 <div className="col-span-full flex justify-end space-x-2 mt-4">
//                                     <Button type="button" variant="outline" onClick={handleCancelEdit} disabled={form.formState.isSubmitting}>
//                                         <XCircle className="mr-2 h-4 w-4" /> Cancel
//                                     </Button>
//                                     <Button type="submit" disabled={form.formState.isSubmitting}>
//                                         {form.formState.isSubmitting ? (
//                                             <Loader2 className="mr-2 h-4 w-4 animate-spin" />
//                                         ) : (
//                                             <Save className="mr-2 h-4 w-4" />
//                                         )}
//                                         Save Details
//                                     </Button>
//                                 </div>
//                             </form>
//                         </Form>
//                     ) : (
//                         <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm text-gray-700">
//                             <div className="space-y-1">
//                                 <p className="font-medium text-gray-900">Legal Company Name</p>
//                                 <p>{thirdPartyDetails?.thirdPartyName || 'N/A'}</p>
//                             </div>
//                             <div className="space-y-1">
//                                 <p className="font-medium text-gray-900">Trading Name</p>
//                                 <p>{thirdPartyDetails?.tradingName || 'N/A'}</p>
//                             </div>
//                             <div className="space-y-1">
//                                 <p className="font-medium text-gray-900">Business Type</p>
//                                 <p>{thirdPartyDetails ? getBusinessTypeName(thirdPartyDetails.businessType) : 'N/A'}</p>
//                             </div>
//                             <div className="space-y-1">
//                                 <p className="font-medium text-gray-900">Registration Number</p>
//                                 <p>{thirdPartyDetails?.registrationNumber || 'N/A'}</p>
//                             </div>
//                             <div className="space-y-1">
//                                 <p className="font-medium text-gray-900">Tax PIN</p>
//                                 <p>{thirdPartyDetails?.taxPIN || 'N/A'}</p>
//                             </div>
//                             <div className="space-y-1">
//                                 <p className="font-medium text-gray-900">VAT Number</p>
//                                 <p>{thirdPartyDetails?.vatNumber || 'N/A'}</p>
//                             </div>
//                             <div className="space-y-1">
//                                 <p className="font-medium text-gray-900">Country</p>
//                                 <p>{thirdPartyDetails?.country || 'N/A'}</p>
//                             </div>
//                             <div className="space-y-1">
//                                 <p className="font-medium text-gray-900">Physical Address</p>
//                                 <p>{thirdPartyDetails?.physicalAddress || 'N/A'}</p>
//                             </div>
//                             <div className="space-y-1">
//                                 <p className="font-medium text-gray-900">Email Address</p>
//                                 <p>{thirdPartyDetails?.email || 'N/A'}</p>
//                             </div>
//                             <div className="space-y-1">
//                                 <p className="font-medium text-gray-900">Phone Number</p>
//                                 <p>{thirdPartyDetails?.phone || 'N/A'}</p>
//                             </div>
//                             <div className="space-y-1">
//                                 <p className="font-medium text-gray-900">Website</p>
//                                 <p>{thirdPartyDetails?.website || 'N/A'}</p>
//                             </div>
//                             <div className="space-y-1">
//                                 <p className="font-medium text-gray-900">Approval Status</p>
//                                 <p>{thirdPartyDetails?.approvalStatus === 1 ? 'Approved' : 'Pending/Rejected'}</p>
//                             </div>
//                             <div className="space-y-1">
//                                 <p className="font-medium text-gray-900">Status</p>
//                                 <p>{thirdPartyDetails?.status === 1 ? 'Active' : 'Inactive'}</p>
//                             </div>
//                             <div className="space-y-1">
//                                 <p className="font-medium text-gray-900">What is the business type?</p>
//                                 <p>{thirdPartyDetails?.thirdPartyType === 1 ? 'Supplier' : 'Other'}</p>
//                             </div>
//                             <div className="space-y-1">
//                                 <p className="font-medium text-gray-900">Created On</p>
//                                 <p>{thirdPartyDetails?.createdOn ? new Date(thirdPartyDetails.createdOn).toLocaleString() : 'N/A'}</p>
//                             </div>
//                             <div className="space-y-1">
//                                 <p className="font-medium text-gray-900">Last Modified On</p>
//                                 <p>{thirdPartyDetails?.modifiedOn ? new Date(thirdPartyDetails.modifiedOn).toLocaleString() : 'N/A'}</p>
//                             </div>
//                         </div>
//                     )}
//                 </motion.div>
//             )}
//         </div>
//     );
// }