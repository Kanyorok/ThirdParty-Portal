interface EnumOption {
    value: string;
    label: string;
}

export const businessTypes: EnumOption[] = [
    { value: "Sole", label: "Sole Proprietorship" },
    { value: "Partnership", label: "Partnership" },
    { value: "Corporation", label: "Corporation" },
    { value: "LLC", label: "Limited Liability Company" },
    { value: "NGO", label: "Non-Profit Organization" },
];

export const thirdPartyTypes: EnumOption[] = [
    { value: "S", label: "Supplier" },
    { value: "T", label: "Tenant" },
];