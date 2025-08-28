import SignInForm from "@/components/signin/login-form";
// import Image from "next/image";

export default function Home() {
  return (
    <div className="min-h-screen flex">
      {/* <div className="flex-1 bg-gradient-to-br from-blue-500 to-blue-600 flex flex-col justify-center items-center p-8 relative">
        <div className="max-w-md text-center">
          <h1 className="text-white text-4xl md:text-5xl font-light mb-4">Third Parties Portal</h1>
          <div className="w-32 h-1 bg-white mx-auto rounded-full"></div>
        </div>
      </div> */}
      <div className="flex-1 flex items-center justify-center p-8">
        <SignInForm />
      </div>
    </div>
  );
}