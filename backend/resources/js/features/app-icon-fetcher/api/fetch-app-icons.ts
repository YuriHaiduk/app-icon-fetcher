import type {
    FetchAppIconsData,
    FetchAppIconsResponse,
} from '../types/app-icon-fetcher';

type ErrorResponse = {
    message?: string;
    errors?: Record<string, string[]>;
};

export async function fetchAppIcons(
    input: string,
): Promise<FetchAppIconsData> {
    try {
        const response = await fetch(
            `/api/v1/app-icons?input=${encodeURIComponent(input)}`,
            {
                headers: {
                    Accept: 'application/json',
                },
            },
        );

        const payload = (await response.json()) as
            | FetchAppIconsResponse
            | ErrorResponse;

        if (!response.ok) {
            throw new Error(errorFromPayload(payload));
        }

        return (payload as FetchAppIconsResponse).data;
    } catch (error) {
        if (error instanceof Error) {
            throw error;
        }

        throw new Error('Unable to fetch icons right now. Please try again.');
    }
}

function errorFromPayload(payload: FetchAppIconsResponse | ErrorResponse): string {
    if ('errors' in payload && payload.errors?.input?.[0]) {
        return payload.errors.input[0];
    }

    if ('message' in payload && payload.message) {
        return payload.message;
    }

    return 'Unable to fetch icons right now. Please try again.';
}
