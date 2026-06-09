import { beforeEach, describe, expect, it, vi } from 'vitest';
import { nextTick } from 'vue';
import { fetchAppIcons } from '../api/fetch-app-icons';
import type { FetchAppIconsData } from '../types/app-icon-fetcher';
import { useAppIconFetcher } from './useAppIconFetcher';

vi.mock('../api/fetch-app-icons', () => ({
    fetchAppIcons: vi.fn(),
}));

const fetchAppIconsMock = vi.mocked(fetchAppIcons);

const successfulResult: FetchAppIconsData = {
    input: {
        original: 'com.example.app',
        type: 'bundle_id',
        bundle_id: 'com.example.app',
        apple_app_id: null,
    },
    icons: {
        apple: {
            found: true,
            icon_url: 'https://example.com/apple.png',
            message: null,
        },
        google: {
            found: true,
            icon_url: 'https://example.com/google.png',
            message: null,
        },
    },
};

describe('useAppIconFetcher', () => {
    beforeEach(() => {
        fetchAppIconsMock.mockReset();
    });

    it('fetches app icons successfully', async () => {
        fetchAppIconsMock.mockResolvedValue(successfulResult);

        const fetcher = useAppIconFetcher();
        fetcher.input.value = 'com.example.app';

        await fetcher.fetchIcons();

        expect(fetchAppIconsMock).toHaveBeenCalledWith('com.example.app');
        expect(fetcher.result.value).toEqual(successfulResult);
        expect(fetcher.error.value).toBeNull();
    });

    it('stores validation and API errors', async () => {
        fetchAppIconsMock.mockRejectedValue(
            new Error('The input field is required.'),
        );

        const fetcher = useAppIconFetcher();
        fetcher.input.value = 'invalid input';

        await fetcher.fetchIcons();

        expect(fetcher.result.value).toBeNull();
        expect(fetcher.error.value).toBe('The input field is required.');
    });

    it('validates empty input without calling the API', async () => {
        const fetcher = useAppIconFetcher();
        fetcher.input.value = '   ';

        await fetcher.fetchIcons();

        expect(fetchAppIconsMock).not.toHaveBeenCalled();
        expect(fetcher.error.value).toBe(
            'Please enter an app store URL or bundle/package id.',
        );
        expect(fetcher.loading.value).toBe(false);
    });

    it('resets loading after the request settles', async () => {
        let resolveFetch: (result: FetchAppIconsData) => void = () => {};
        fetchAppIconsMock.mockReturnValue(
            new Promise((resolve) => {
                resolveFetch = resolve;
            }),
        );

        const fetcher = useAppIconFetcher();
        fetcher.input.value = 'com.example.app';

        const request = fetcher.fetchIcons();
        await nextTick();

        expect(fetcher.loading.value).toBe(true);

        resolveFetch(successfulResult);
        await request;

        expect(fetcher.loading.value).toBe(false);
    });
});
