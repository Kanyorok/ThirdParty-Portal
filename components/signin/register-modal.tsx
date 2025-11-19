"use client"

import { useState, useCallback, useEffect } from "react"
import UserTypeStep from "./usertype-step"
import RegistrationFormStep from "./register-form"
import { UserTypeValue } from "@/types/types"
import { X, ArrowLeft } from "lucide-react"
import { RegisterFormInputs } from "@/lib/validation"
import { motion } from "framer-motion"

interface ModalProps {
    isOpen: boolean
    onClose: () => void
    title: string
    children: React.ReactNode
    className?: string
    onBack?: () => void
    currentStep: 1 | 2
    totalSteps: 2
}

function Modal({ isOpen, onClose, title, children, className, onBack, currentStep, totalSteps }: ModalProps) {
    useEffect(() => {
        const handleEscape = (event: KeyboardEvent) => {
            if (event.key === 'Escape') onClose()
        }
        if (isOpen) document.addEventListener('keydown', handleEscape)
        return () => document.removeEventListener('keydown', handleEscape)
    }, [isOpen, onClose])

    if (!isOpen) return null

    const progressPercentage = (currentStep / totalSteps) * 100

    return (
        <div className="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4" onClick={onClose}>
            <motion.div
                initial={{ scale: 0.9, opacity: 0 }}
                animate={{ scale: 1, opacity: 1 }}
                exit={{ scale: 0.9, opacity: 0 }}
                className={`bg-white dark:bg-gray-900 rounded-3xl shadow-2xl w-full ${className} relative overflow-hidden`}
                onClick={(e) => e.stopPropagation()}
            >
                <div className="absolute top-0 left-0 w-full h-1 bg-gray-200 dark:bg-gray-700">
                    <motion.div
                        className="h-full bg-indigo-500"
                        style={{ width: `${progressPercentage}%` }}
                        transition={{ duration: 0.5, ease: "easeOut" }}
                        role="progressbar"
                        aria-valuenow={currentStep}
                        aria-valuemin={1}
                        aria-valuemax={totalSteps}
                    />
                </div>

                <div className="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-800">
                    <div className="flex items-center">
                        {currentStep === 2 && onBack && (
                            <button onClick={onBack} className="mr-3 p-2 rounded-full text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                                <ArrowLeft className="w-5 h-5" />
                            </button>
                        )}
                        <h2 className="text-2xl font-bold text-gray-900 dark:text-white">{title}</h2>
                    </div>
                    <button onClick={onClose} className="p-2 rounded-full text-gray-400 dark:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        <X className="w-6 h-6" />
                    </button>
                </div>

                <div className="p-8 max-h-[75vh] overflow-y-auto">
                    {children}
                </div>
            </motion.div>
        </div>
    )
}

export default function RegisterModal() {
    const [isOpen, setIsOpen] = useState(false)
    const [step, setStep] = useState<1 | 2>(1)
    const [selectedType, setSelectedType] = useState<UserTypeValue | "">("")
    const TOTAL_STEPS = 2

    const openModal = useCallback(() => {
        setIsOpen(true)
        setStep(1)
        setSelectedType("")
    }, [])

    const closeModal = useCallback(() => {
        setIsOpen(false)
        setStep(1)
        setSelectedType("")
    }, [])

    const handleLoginRedirect = useCallback(() => {
        closeModal()
    }, [closeModal])

    const selectType = useCallback((t: UserTypeValue) => {
        setSelectedType(t)
        setStep(2)
    }, [])

    const back = useCallback(() => setStep(1), [])

    const handleSubmit = useCallback((data: RegisterFormInputs) => {
        console.log("Submitting registration:", data, "for user type:", selectedType)
    }, [selectedType])

    const modalTitle = step === 1 ? "Select Account Type" : "Create Account"
    const isStepTwo = step === 2

    return (
        <>
            <button
                onClick={openModal}
                className="px-6 py-3 bg-indigo-600 text-white rounded-xl font-medium shadow-lg shadow-indigo-500/30 hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/50 transition duration-150 ease-in-out"
            >
                Start Registration
            </button>

            <Modal
                isOpen={isOpen}
                onClose={closeModal}
                title={modalTitle}
                className={isStepTwo ? "max-w-3xl" : "max-w-lg"}
                onBack={isStepTwo ? back : undefined}
                currentStep={step}
                totalSteps={TOTAL_STEPS}
            >
                {step === 1 && <UserTypeStep onSelect={selectType} />}
                {step === 2 && selectedType && (
                    <RegistrationFormStep
                        userType={selectedType}
                        onBack={back}
                        onSubmit={handleSubmit}
                        onLoginRedirect={handleLoginRedirect}
                    />
                )}
            </Modal>
        </>
    )
}
