"use client"

import React, { useEffect, useState, useMemo } from "react"
import useSWR from "swr"
import { Loader2 } from "lucide-react"
import { useForm } from "react-hook-form"
import { ThirdPartyInputs, CountryOption } from "@/types/third-party"
import { useThirdPartyProfile } from "@/hooks/use-third-party-profile"
import { ProfileModal } from "@/components/thirdParty/party-profile-modal"
import { Building2, FileText, Globe, Mail, MapPin, Percent, Phone } from "lucide-react"
import { FieldRow } from "@/components/thirdParty/party-dashboard-fields"
import { DashboardHeader } from "@/components/thirdParty/party-dashboard-header"
import { AnimatePresence, motion } from "framer-motion"

const fetcher = (url: string) => fetch(url, { cache: "no-store" }).then(res => res.json())

export default function ThirdPartyDashboard() {
    const [isModalOpen, setIsModalOpen] = useState(false)
    const { profile, updateProfile, createProfile, isLoading } = useThirdPartyProfile()
    const { data: countriesData } = useSWR<{ data: CountryOption[] }>("/api/countries", fetcher)
    const countries = countriesData?.data ?? []

    const form = useForm<ThirdPartyInputs>({
        defaultValues: profile ?? {}
    })

    useEffect(() => { if (profile) form.reset(profile) }, [profile, form])
    useEffect(() => {
        if (!isLoading && !profile) setIsModalOpen(true)
    }, [isLoading, profile])

    const countryName = useMemo(() => countries.find(c => c.id === profile?.countryId)?.name ?? "N/A", [countries, profile?.countryId])

    const handleSubmit = async (values: ThirdPartyInputs) => {
        try {
            if (profile) {
                await updateProfile(values)
            } else {
                await createProfile(values)
            }
            setIsModalOpen(false)
        } catch { }
    }

    return (
        <div className="min-h-screen bg-gray-50 dark:bg-background">
            <div className="max-w-3xl mx-auto p-4 sm:p-6 lg:p-8">
                {isLoading ? <Loader2 className="animate-spin h-12 w-12 text-primary m-auto" /> : (
                    <>
                        <DashboardHeader
                            name={profile?.thirdPartyName}
                            approvalStatus={profile?.approvalStatus}
                            status={profile?.status}
                            onEdit={() => setIsModalOpen(true)}
                        />

                        <AnimatePresence mode="wait">
                            {profile ? (
                                <motion.div
                                    key="profile-fields"
                                    initial={{ opacity: 0 }}
                                    animate={{ opacity: 1 }}
                                    exit={{ opacity: 0 }}
                                    transition={{ duration: 0.4 }}
                                    className="space-y-6 bg-white dark:bg-card rounded-lg shadow p-4"
                                >
                                    <h2 className="text-lg font-semibold mb-2">General Information</h2>
                                    <FieldRow label="Legal Name" value={profile.thirdPartyName ?? null} icon={Building2} />
                                    <FieldRow label="Trading Name" value={profile.tradingName ?? null} icon={Building2} />
                                    <FieldRow label="Business Type" value={profile.businessType ?? null} icon={FileText} />

                                    <h2 className="text-lg font-semibold mt-4 mb-2">Registration & Tax</h2>
                                    <FieldRow label="Registration Number" value={profile.registrationNumber ?? null} icon={FileText} />
                                    <FieldRow label="Tax PIN" value={profile.taxPIN ?? null} icon={Percent} />
                                    <FieldRow label="VAT Number" value={profile.vatNumber ?? null} icon={Percent} />

                                    <h2 className="text-lg font-semibold mt-4 mb-2">Contact & Location</h2>
                                    <FieldRow label="Country" value={countryName ?? null} icon={MapPin} />
                                    <FieldRow label="Address" value={profile.physicalAddress ?? null} icon={MapPin} />
                                    <FieldRow label="Email" value={profile.email ?? null} icon={Mail} />
                                    <FieldRow label="Phone" value={profile.phone ?? null} icon={Phone} />
                                    <FieldRow label="Website" value={profile.website ?? null} icon={Globe} />
                                </motion.div>
                            ) : (
                                <motion.div
                                    key="no-profile"
                                    initial={{ opacity: 0 }}
                                    animate={{ opacity: 1 }}
                                    exit={{ opacity: 0 }}
                                    className="text-center py-16"
                                >
                                    No profile found. Create one to get started.
                                </motion.div>
                            )}
                        </AnimatePresence>
                    </>
                )}
            </div>

            <ProfileModal
                isOpen={isModalOpen}
                onOpenChange={setIsModalOpen}
                form={form}
                countries={countries}
                isEditing={!!profile}
                onSubmit={handleSubmit}
            />
        </div>
    )
}
