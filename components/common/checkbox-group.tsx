import { Checkbox } from "@/components/common/checkbox";
import {
    FormControl,
    FormField,
    FormItem,
    FormLabel,
    FormMessage,
} from "@/components/common/form";
import { Control } from "react-hook-form";

interface Option {
    id?: string;
    value?: string;
    label: string;
}

interface CheckboxGroupProps {
    name: string;
    label: string;
    options: Option[];
    control: Control<any>;
    required?: boolean;
}

export function CheckboxGroup({
    name,
    label,
    options,
    control,
    required,
}: CheckboxGroupProps) {
    return (
        <FormField
            control={control}
            name={name}
            render={() => (
                <FormItem>
                    <div className="mb-4">
                        <FormLabel className="text-base">
                            {label} {required && <span className="text-red-500">*</span>}
                        </FormLabel>
                    </div>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {options.map((item) => {
                            const itemValue = item.value || item.id || "";
                            return (
                                <FormField
                                    key={itemValue}
                                    control={control}
                                    name={name}
                                    render={({ field }) => {
                                        return (
                                            <FormItem
                                                key={itemValue}
                                                className="flex flex-row items-start space-x-3 space-y-0 rounded-md border p-4 shadow-sm"
                                            >
                                                <FormControl>
                                                    <Checkbox
                                                        checked={field.value?.includes(itemValue)}
                                                        onCheckedChange={(checked) => {
                                                            return checked
                                                                ? field.onChange([...(field.value || []), itemValue])
                                                                : field.onChange(
                                                                    field.value?.filter(
                                                                        (value: string) => value !== itemValue
                                                                    )
                                                                )
                                                        }}
                                                    />
                                                </FormControl>
                                                <FormLabel className="font-normal cursor-pointer">
                                                    {item.label}
                                                </FormLabel>
                                            </FormItem>
                                        )
                                    }}
                                />
                            )
                        })}
                    </div>
                    <FormMessage />
                </FormItem>
            )}
        />
    );
}

