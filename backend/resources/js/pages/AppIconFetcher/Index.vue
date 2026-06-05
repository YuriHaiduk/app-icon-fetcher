<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { CheckCircle2, Clipboard, Image as ImageIcon, Search } from '@lucide/vue';
import { computed, ref } from 'vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type StoreIconResult = {
    found: boolean;
    icon_url: string | null;
    message: string | null;
};

type FetchAppIconsResponse = {
    data: {
        input: {
            original: string;
            type: string;
            bundle_id: string | null;
            apple_app_id: string | null;
        };
        icons: {
            apple: StoreIconResult;
            google: StoreIconResult;
        };
    };
};

type ErrorResponse = {
    message?: string;
    errors?: Record<string, string[]>;
};

type StoreCard = {
    key: keyof FetchAppIconsResponse['data']['icons'];
    name: string;
    result: StoreIconResult;
};

const pageUrl = '/app-icon-fetcher';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'App Icon Fetcher',
                href: pageUrl,
            },
        ],
    },
});

const input = ref('');
const result = ref<FetchAppIconsResponse['data'] | null>(null);
const isLoading = ref(false);
const errorMessage = ref<string | null>(null);
const copiedUrl = ref<string | null>(null);

const resultCards = computed<StoreCard[]>(() => {
    if (result.value === null) {
        return [];
    }

    return [
        {
            key: 'apple',
            name: 'Apple App Store',
            result: result.value.icons.apple,
        },
        {
            key: 'google',
            name: 'Google Play',
            result: result.value.icons.google,
        },
    ];
});

const hasInput = computed(() => input.value.trim().length > 0);

async function fetchIcons(): Promise<void> {
    errorMessage.value = null;
    copiedUrl.value = null;

    if (!hasInput.value) {
        errorMessage.value = 'Please enter an app store URL or bundle/package id.';

        return;
    }

    isLoading.value = true;

    try {
        const response = await fetch(
            `/api/v1/app-icons?input=${encodeURIComponent(input.value)}`,
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
            errorMessage.value = errorFromPayload(payload);

            return;
        }

        result.value = (payload as FetchAppIconsResponse).data;
    } catch {
        errorMessage.value = 'Unable to fetch icons right now. Please try again.';
    } finally {
        isLoading.value = false;
    }
}

async function copyIconUrl(iconUrl: string): Promise<void> {
    if (!navigator.clipboard) {
        errorMessage.value = 'Copying is not available in this browser.';

        return;
    }

    try {
        await navigator.clipboard.writeText(iconUrl);
        copiedUrl.value = iconUrl;
    } catch {
        errorMessage.value = 'Copying is not available in this browser.';
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
</script>

<template>
    <Head title="App Icon Fetcher" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4">
        <section class="max-w-4xl space-y-2">
            <h1 class="text-2xl font-semibold tracking-normal">
                App Icon Fetcher
            </h1>
            <p class="max-w-2xl text-sm text-muted-foreground">
                Paste a Google Play URL, Apple App Store URL, or bundle/package
                id to fetch app icons.
            </p>
        </section>

        <Card class="max-w-4xl rounded-lg">
            <CardHeader>
                <CardTitle class="text-base">Lookup</CardTitle>
            </CardHeader>
            <CardContent>
                <form class="space-y-4" @submit.prevent="fetchIcons">
                    <div class="space-y-2">
                        <Label for="app-icon-input">App input</Label>
                        <Input
                            id="app-icon-input"
                            v-model="input"
                            type="text"
                            autocomplete="off"
                            placeholder="com.example.app or App Store / Google Play URL"
                            :disabled="isLoading"
                        />
                    </div>

                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center"
                    >
                        <Button
                            type="submit"
                            class="w-full sm:w-auto"
                            :disabled="isLoading"
                        >
                            <Search class="size-4" />
                            <span>{{
                                isLoading ? 'Fetching Icons...' : 'Fetch Icons'
                            }}</span>
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>

        <Alert v-if="errorMessage" variant="destructive" class="max-w-4xl">
            <AlertTitle>Unable to fetch icons</AlertTitle>
            <AlertDescription>{{ errorMessage }}</AlertDescription>
        </Alert>

        <section v-if="result" class="max-w-5xl space-y-4">
            <div class="space-y-1">
                <h2 class="text-lg font-medium">Results</h2>
                <p class="text-sm text-muted-foreground">
                    {{ result.input.original }}
                </p>
                <div class="flex flex-wrap gap-2 pt-1">
                    <Badge variant="secondary">{{ result.input.type }}</Badge>
                    <Badge v-if="result.input.bundle_id" variant="outline">
                        {{ result.input.bundle_id }}
                    </Badge>
                    <Badge v-if="result.input.apple_app_id" variant="outline">
                        Apple ID {{ result.input.apple_app_id }}
                    </Badge>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <Card
                    v-for="card in resultCards"
                    :key="card.key"
                    class="rounded-lg"
                >
                    <CardHeader>
                        <div class="flex items-center justify-between gap-3">
                            <CardTitle class="text-base">
                                {{ card.name }}
                            </CardTitle>
                            <Badge
                                :variant="
                                    card.result.found ? 'default' : 'secondary'
                                "
                            >
                                {{ card.result.found ? 'Found' : 'No icon' }}
                            </Badge>
                        </div>
                    </CardHeader>

                    <CardContent>
                        <div
                            v-if="card.result.found && card.result.icon_url"
                            class="space-y-4"
                        >
                            <div class="flex items-start gap-4">
                                <div
                                    class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-lg border bg-muted"
                                >
                                    <img
                                        :src="card.result.icon_url"
                                        :alt="`${card.name} icon preview`"
                                        class="size-full object-cover"
                                        loading="lazy"
                                    />
                                </div>
                                <div class="min-w-0 flex-1 space-y-2">
                                    <p
                                        class="break-all text-sm text-muted-foreground"
                                    >
                                        {{ card.result.icon_url }}
                                    </p>
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        @click="
                                            copyIconUrl(card.result.icon_url)
                                        "
                                    >
                                        <CheckCircle2
                                            v-if="
                                                copiedUrl ===
                                                card.result.icon_url
                                            "
                                            class="size-4"
                                        />
                                        <Clipboard v-else class="size-4" />
                                        <span>{{
                                            copiedUrl === card.result.icon_url
                                                ? 'Copied'
                                                : 'Copy URL'
                                        }}</span>
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <div
                            v-else
                            class="flex items-start gap-3 rounded-lg border bg-muted/40 p-4"
                        >
                            <ImageIcon
                                class="mt-0.5 size-5 shrink-0 text-muted-foreground"
                            />
                            <p class="text-sm text-muted-foreground">
                                {{
                                    card.result.message ??
                                    'Icon was not found.'
                                }}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </section>
    </div>
</template>
