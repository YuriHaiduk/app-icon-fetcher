<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import {
    AppIconFetcherForm,
    AppIconFetcherResult,
    useAppIconFetcher,
} from '@/features/app-icon-fetcher';

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

const { input, result, loading, error, fetchIcons } = useAppIconFetcher();
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

        <AppIconFetcherForm
            v-model="input"
            :loading="loading"
            @submit="fetchIcons"
        />

        <Alert v-if="error" variant="destructive" class="max-w-4xl">
            <AlertTitle>Unable to fetch icons</AlertTitle>
            <AlertDescription>{{ error }}</AlertDescription>
        </Alert>

        <AppIconFetcherResult v-if="result" :result="result" />
    </div>
</template>
