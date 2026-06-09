<script setup lang="ts">
import { CheckCircle2, Clipboard, Image as ImageIcon } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { useClipboard } from '../composables/useClipboard';
import type { StoreIconResult } from '../types/app-icon-fetcher';

const props = defineProps<{
    title: string;
    result: StoreIconResult;
}>();

const { copiedText, copyError, copy } = useClipboard();
</script>

<template>
    <Card class="rounded-lg">
        <CardHeader>
            <div class="flex items-center justify-between gap-3">
                <CardTitle class="text-base">
                    {{ props.title }}
                </CardTitle>
                <Badge :variant="result.found ? 'default' : 'secondary'">
                    {{ result.found ? 'Found' : 'No icon' }}
                </Badge>
            </div>
        </CardHeader>

        <CardContent>
            <div v-if="result.found && result.icon_url" class="space-y-4">
                <div class="flex items-start gap-4">
                    <div
                        class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-lg border bg-muted"
                    >
                        <img
                            :src="result.icon_url"
                            :alt="`${props.title} icon preview`"
                            class="size-full object-cover"
                            loading="lazy"
                        />
                    </div>
                    <div class="min-w-0 flex-1 space-y-2">
                        <p class="break-all text-sm text-muted-foreground">
                            {{ result.icon_url }}
                        </p>
                        <Button
                            type="button"
                            variant="outline"
                            size="sm"
                            @click="copy(result.icon_url)"
                        >
                            <CheckCircle2
                                v-if="copiedText === result.icon_url"
                                class="size-4"
                            />
                            <Clipboard v-else class="size-4" />
                            <span>{{
                                copiedText === result.icon_url
                                    ? 'Copied'
                                    : 'Copy URL'
                            }}</span>
                        </Button>
                        <p
                            v-if="copyError"
                            class="text-sm text-destructive"
                        >
                            {{ copyError }}
                        </p>
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
                    {{ result.message ?? 'Icon was not found.' }}
                </p>
            </div>
        </CardContent>
    </Card>
</template>
