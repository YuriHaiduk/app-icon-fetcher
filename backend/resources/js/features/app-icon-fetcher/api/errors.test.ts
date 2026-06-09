import { describe, expect, it } from 'vitest';
import {
    errorFromPayload,
    genericFetchErrorMessage,
} from './errors';

describe('errorFromPayload', () => {
    it('uses the first Laravel input validation error', () => {
        expect(
            errorFromPayload({
                errors: {
                    input: ['The input field is required.'],
                },
            }),
        ).toBe('The input field is required.');
    });

    it('uses a generic backend message', () => {
        expect(
            errorFromPayload({
                message: 'Unable to resolve this app.',
            }),
        ).toBe('Unable to resolve this app.');
    });

    it('falls back when payload is empty or unknown', () => {
        expect(errorFromPayload({})).toBe(genericFetchErrorMessage);
    });
});
