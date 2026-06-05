<script setup lang="ts">
import { computed } from 'vue';
import { Badge } from '@/components/ui/badge';
import type {
    FetchAppIconsData,
    StoreIconResult,
} from '../types/app-icon-fetcher';
import StoreIconCard from './StoreIconCard.vue';

type StoreCard = {
    key: keyof FetchAppIconsData['icons'];
    name: string;
    result: StoreIconResult;
};

const props = defineProps<{
    result: FetchAppIconsData;
}>();

const resultCards = computed<StoreCard[]>(() => [
    {
        key: 'apple',
        name: 'Apple App Store',
        result: props.result.icons.apple,
    },
    {
        key: 'google',
        name: 'Google Play',
        result: props.result.icons.google,
    },
]);
</script>

<template>
    <section class="max-w-5xl space-y-4">
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
            <StoreIconCard
                v-for="card in resultCards"
                :key="card.key"
                :title="card.name"
                :result="card.result"
            />
        </div>
    </section>
</template>
