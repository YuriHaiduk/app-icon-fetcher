import type { FetchAppIconsData } from '../types/app-icon-fetcher';

export type StoreKey = keyof FetchAppIconsData['icons'];

export type StoreDisplayConfig = {
    key: StoreKey;
    name: string;
};

export const stores: StoreDisplayConfig[] = [
    {
        key: 'apple',
        name: 'Apple App Store',
    },
    {
        key: 'google',
        name: 'Google Play',
    },
];
