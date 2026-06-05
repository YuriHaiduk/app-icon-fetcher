<script setup lang="ts">
import { Search } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

defineProps<{
    modelValue: string;
    loading: boolean;
}>();

defineEmits<{
    'update:modelValue': [value: string];
    submit: [];
}>();
</script>

<template>
    <Card class="max-w-4xl rounded-lg">
        <CardHeader>
            <CardTitle class="text-base">Lookup</CardTitle>
        </CardHeader>
        <CardContent>
            <form class="space-y-4" @submit.prevent="$emit('submit')">
                <div class="space-y-2">
                    <Label for="app-icon-input">App input</Label>
                    <Input
                        id="app-icon-input"
                        :model-value="modelValue"
                        type="text"
                        autocomplete="off"
                        placeholder="com.example.app, 6503284107, id6503284107, or App Store / Google Play URL"
                        :disabled="loading"
                        @update:model-value="
                            $emit('update:modelValue', String($event))
                        "
                    />
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <Button
                        type="submit"
                        class="w-full sm:w-auto"
                        :disabled="loading"
                    >
                        <Search class="size-4" />
                        <span>{{
                            loading ? 'Fetching Icons...' : 'Fetch Icons'
                        }}</span>
                    </Button>
                </div>
            </form>
        </CardContent>
    </Card>
</template>
