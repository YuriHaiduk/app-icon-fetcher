import { ref } from 'vue';

const clipboardUnavailableMessage = 'Copying is not available in this browser.';

export function useClipboard() {
    const copiedText = ref<string | null>(null);
    const copyError = ref<string | null>(null);

    async function copy(text: string): Promise<void> {
        copyError.value = null;
        copiedText.value = null;

        if (!navigator.clipboard) {
            copyError.value = clipboardUnavailableMessage;

            return;
        }

        try {
            await navigator.clipboard.writeText(text);
            copiedText.value = text;
        } catch {
            copyError.value = clipboardUnavailableMessage;
        }
    }

    return {
        copiedText,
        copyError,
        copy,
    };
}
