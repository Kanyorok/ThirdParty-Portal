// "use client"
// import { useForm } from "react-hook-form"
// import { zodResolver } from "@hookform/resolvers/zod"
// import { z } from "zod"
// import { useThirdPartyProfile } from "@/hooks/use-third-party-profile"
// import { useEffect } from "react"

// const thirdPartySchema = z.object({
//     thirdPartyName: z.string().min(1),
//     tradingName: z.string().nullable().optional(),
//     businessType: z.number(),
//     registrationNumber: z.string(),
//     taxPIN: z.string(),
//     vatNumber: z.string().nullable().optional(),
//     countryId: z.number(),
//     physicalAddress: z.string(),
//     email: z.string().email(),
//     phone: z.string(),
//     website: z.string().nullable().optional(),
// })

// type ThirdPartyInputs = z.infer<typeof thirdPartySchema>

// export default function ThirdPartyDashboard() {
//     const { profile, loading, error, updateProfile } = useThirdPartyProfile()

//     const form = useForm<ThirdPartyInputs>({
//         resolver: zodResolver(thirdPartySchema),
//         defaultValues: profile ?? {},
//     })

//     useEffect(() => {
//         if (profile) form.reset(profile)
//     }, [profile, form])

//     const onSubmit = (data: ThirdPartyInputs) => {
//         updateProfile(data)
//     }

//     if (loading) return <p>Loading...</p>
//     if (error) return <p>{error}</p>
//     if (!profile) return <p>No profile found</p>

//     return (
//         <form onSubmit={form.handleSubmit(onSubmit)}>
//             <input {...form.register("thirdPartyName")} placeholder="Legal Name" />
//             <input {...form.register("tradingName")} placeholder="Trading Name" />
//             <button type="submit">Save</button>
//         </form>
//     )
// }
