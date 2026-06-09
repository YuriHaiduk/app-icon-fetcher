import { mount } from '@vue/test-utils';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import type { StoreIconResult } from '../types/app-icon-fetcher';
import StoreIconCard from './StoreIconCard.vue';

const foundResult: StoreIconResult = {
    found: true,
    icon_url: 'https://example.com/icon.png',
    message: null,
};

const missingResult: StoreIconResult = {
    found: false,
    icon_url: null,
    message: 'App was not found in this store.',
};

function mountCard(result: StoreIconResult) {
    return mount(StoreIconCard, {
        props: {
            title: 'Google Play',
            result,
        },
        global: {
            stubs: {
                Badge: {
                    template: '<span><slot /></span>',
                },
                Button: {
                    template: '<button><slot /></button>',
                },
                Card: {
                    template: '<section><slot /></section>',
                },
                CardContent: {
                    template: '<div><slot /></div>',
                },
                CardHeader: {
                    template: '<header><slot /></header>',
                },
                CardTitle: {
                    template: '<h3><slot /></h3>',
                },
                CheckCircle2: {
                    template: '<span />',
                },
                Clipboard: {
                    template: '<span />',
                },
                ImageIcon: {
                    template: '<span />',
                },
            },
        },
    });
}

describe('StoreIconCard', () => {
    beforeEach(() => {
        Object.assign(navigator, {
            clipboard: {
                writeText: vi.fn().mockResolvedValue(undefined),
            },
        });
    });

    it('renders the found icon state', () => {
        const wrapper = mountCard(foundResult);

        expect(wrapper.text()).toContain('Google Play');
        expect(wrapper.text()).toContain('Found');
        expect(wrapper.text()).toContain('https://example.com/icon.png');
        expect(wrapper.find('img').attributes('src')).toBe(
            'https://example.com/icon.png',
        );
        expect(wrapper.find('button').text()).toContain('Copy URL');
    });

    it('renders the not found message state', () => {
        const wrapper = mountCard(missingResult);

        expect(wrapper.text()).toContain('No icon');
        expect(wrapper.text()).toContain('App was not found in this store.');
        expect(wrapper.find('img').exists()).toBe(false);
    });

    it('copies the icon URL with clipboard logic', async () => {
        const wrapper = mountCard(foundResult);

        await wrapper.find('button').trigger('click');

        expect(navigator.clipboard.writeText).toHaveBeenCalledWith(
            'https://example.com/icon.png',
        );
        expect(wrapper.text()).toContain('Copied');
    });
});
