import SignInForm from "@/components/signin/login-form";
import { ThemeToggle } from "@/app/dashboard/theme-toggle";

export default async function SignIn() {
  return (
    <div className="min-h-screen flex flex-col items-center justify-center p-4 bg-gray-100 dark:bg-zinc-950 relative">
      <div className="fixed top-4 right-4 z-50">
        <ThemeToggle />
      </div>

      <div className="w-full max-w-xl">
        <SignInForm />
      </div>
    </div>
  );
}
