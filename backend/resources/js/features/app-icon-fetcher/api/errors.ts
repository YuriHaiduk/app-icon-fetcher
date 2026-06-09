import type { FetchAppIconsResponse } from '../types/app-icon-fetcher';

export type ErrorResponse = {
    message?: string;
    errors?: Record<string, string[]>;
};

export const genericFetchErrorMessage =
    'Unable to fetch icons right now. Please try again.';

export function errorFromPayload(
    payload: FetchAppIconsResponse | ErrorResponse,
): string {
    if ('errors' in payload && payload.errors?.input?.[0]) {
        return payload.errors.input[0];
    }

    if ('message' in payload && payload.message) {
        return payload.message;
    }

    return genericFetchErrorMessage;
}
