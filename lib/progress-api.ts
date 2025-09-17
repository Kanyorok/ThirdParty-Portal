import { CategoryProgress } from "@/types/types";

export interface ProgressApiResponse {
    data: {
        overall_status: 'DRAFT' | 'SUBMITTED' | 'PARTIAL' | 'COMPLETE';
        categories: CategoryProgress[];
        summary: {
            total_categories: number;
            approved_categories: number;
            rejected_categories: number;
            pending_categories: number;
            overall_progress: number;
        };
    };
}

/**
 * Fetches application progress for a specific round
 */
export async function fetchApplicationProgress(roundId: string): Promise<ProgressApiResponse | null> {
    try {
        const response = await fetch(`/api/prequalification/applications/${roundId}/progress`, {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            cache: 'no-store', // Always fetch fresh data
        });

        if (!response.ok) {
            console.error(`Failed to fetch progress for round ${roundId}: ${response.status}`);
            return null;
        }

        return await response.json();
    } catch (error) {
        console.error('Error fetching application progress:', error);
        return null;
    }
}

/**
 * Updates the progress data for rounds that have applications
 */
export async function enrichRoundsWithProgress(rounds: any[]): Promise<any[]> {
    const enrichedRounds = await Promise.all(
        rounds.map(async (round) => {
            // Only fetch progress for rounds where user has applied
            if (!round.hasApplied || !round.applicationId) {
                return round;
            }

            const progressData = await fetchApplicationProgress(round.id);
            
            if (progressData) {
                return {
                    ...round,
                    applicationProgress: progressData.data
                };
            }

            return round;
        })
    );

    return enrichedRounds;
}


