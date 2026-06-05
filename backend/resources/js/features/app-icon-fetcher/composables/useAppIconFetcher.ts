import { computed, ref } from 'vue';
import { fetchAppIcons } from '../api/fetch-app-icons';
import type { FetchAppIconsData } from '../types/app-icon-fetcher';

export function useAppIconFetcher() {
    const input = ref('');
    const result = ref<FetchAppIconsData | null>(null);
    const loading = ref(false);
    const error = ref<string | null>(null);

    const hasInput = computed(() => input.value.trim().length > 0);

    async function fetchIcons(): Promise<void> {
        error.value = null;

        if (!hasInput.value) {
            error.value = 'Please enter an app store URL or bundle/package id.';

            return;
        }

        loading.value = true;
        result.value = null;

        try {
            result.value = await fetchAppIcons(input.value);
        } catch (caughtError) {
            error.value =
                caughtError instanceof Error
                    ? caughtError.message
                    : 'Unable to fetch icons right now. Please try again.';
        } finally {
            loading.value = false;
        }
    }

    function reset(): void {
        input.value = '';
        result.value = null;
        error.value = null;
        loading.value = false;
    }

    return {
        input,
        result,
        loading,
        error,
        fetchIcons,
        reset,
    };
}
