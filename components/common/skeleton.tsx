import { cn } from "@/lib/utils";

function Skeleton({
  className,
  ...props
}: React.HTMLAttributes<HTMLDivElement>) {
  return (
    <div
      className={cn("relative overflow-hidden rounded-md bg-muted", className)}
      {...props}
    >
      <div className="absolute inset-0 -translate-x-full animate-[shimmer_2s_infinite] bg-gradient-to-r from-transparent via-gray-300/20 to-transparent dark:via-gray-700/20"></div>
    </div>
  );
}

export { Skeleton };