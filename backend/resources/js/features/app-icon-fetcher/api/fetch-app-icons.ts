import type {
    FetchAppIconsData,
    FetchAppIconsResponse,
} from '../types/app-icon-fetcher';
import {
    errorFromPayload,
    genericFetchErrorMessage,
} from './errors';
import type { ErrorResponse } from './errors';

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

        throw new Error(genericFetchErrorMessage);
    }
}
