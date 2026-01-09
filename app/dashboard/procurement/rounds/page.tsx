import { getOpenRounds } from "@/lib/api";
import { RoundsListClient } from "./rounds-list-client";

export default async function RoundsPage() {
    const response = await getOpenRounds();

    return (
        <div className="container mx-auto py-10 px-4">
            <div className="mb-8">
                <h1 className="text-3xl font-bold tracking-tight">Prequalification Rounds</h1>
                <p className="text-muted-foreground">Browse opportunities and register your interest.</p>
            </div>

            <RoundsListClient initialData={response.data} />
        </div>
    );
}