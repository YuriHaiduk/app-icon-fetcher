import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';
import type { FetchAppIconsData } from '../types/app-icon-fetcher';
import AppIconFetcherResult from './AppIconFetcherResult.vue';

const result: FetchAppIconsData = {
    input: {
        original: 'https://apps.apple.com/ua/app/example/id123456789',
        type: 'apple_url',
        bundle_id: 'com.example.app',
        apple_app_id: '123456789',
    },
    icons: {
        apple: {
            found: true,
            icon_url: 'https://example.com/apple.png',
            message: null,
        },
        google: {
            found: false,
            icon_url: null,
            message: 'App was not found in Google Play.',
        },
    },
};

function mountResult() {
    return mount(AppIconFetcherResult, {
        props: {
            result,
        },
        global: {
            stubs: {
                Badge: {
                    template: '<span><slot /></span>',
                },
                StoreIconCard: {
                    props: ['title', 'result'],
                    template: '<article>{{ title }}: {{ result.message }}</article>',
                },
            },
        },
    });
}

describe('AppIconFetcherResult', () => {
    it('renders Apple and Google result cards', () => {
        const wrapper = mountResult();

        expect(wrapper.text()).toContain('Apple App Store');
        expect(wrapper.text()).toContain('Google Play');
    });

    it('renders normalized input data', () => {
        const wrapper = mountResult();

        expect(wrapper.text()).toContain(
            'https://apps.apple.com/ua/app/example/id123456789',
        );
        expect(wrapper.text()).toContain('apple_url');
        expect(wrapper.text()).toContain('com.example.app');
        expect(wrapper.text()).toContain('Apple ID 123456789');
    });
});
