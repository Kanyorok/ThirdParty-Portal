import { CLIENT_APP_NAME_STRING } from "@/config/client-config";

interface AuthHeaderProps {
  isRegistration?: boolean;
}

export function AuthHeader({ isRegistration = false }: AuthHeaderProps) {
  const descriptionText = "Self-Service Portal";

  return (
    <header className="text-center mb-10 space-y-5">
      <h1 className="text-4xl md:text-5xl font-black text-indigo-800 tracking-tight leading-tight">
        {CLIENT_APP_NAME_STRING}
      </h1>
      <div className="pt-6 border-t border-gray-200/70 mt-6">
        <p className="text-lg text-gray-700 max-w-md mx-auto mt-3">
          {descriptionText}
        </p>
      </div>
    </header>
  );
}