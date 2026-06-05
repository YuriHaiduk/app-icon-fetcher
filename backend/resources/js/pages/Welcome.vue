<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { login, register } from '@/routes';
import { index as appIconFetcher } from '@/routes/app-icon-fetcher';
</script>

<template>
    <Head title="App Icon Fetcher" />

    <div
        class="min-h-screen bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]"
    >
        <header class="mx-auto flex w-full max-w-6xl items-center justify-end px-6 py-6 lg:px-8">
            <nav class="flex items-center gap-3 text-sm">
                <Link
                    v-if="$page.props.auth.user"
                    :href="appIconFetcher()"
                    class="inline-flex rounded-md border border-[#19140035] px-4 py-2 font-medium hover:border-[#1915014a] dark:border-[#3E3E3A] dark:hover:border-[#62605b]"
                >
                    Open App Icon Fetcher
                </Link>

                <template v-else>
                    <Link
                        :href="login()"
                        class="inline-flex rounded-md border border-transparent px-4 py-2 font-medium hover:border-[#19140035] dark:hover:border-[#3E3E3A]"
                    >
                        Log in
                    </Link>

                    <Link
                        :href="register()"
                        class="inline-flex rounded-md border border-[#19140035] px-4 py-2 font-medium hover:border-[#1915014a] dark:border-[#3E3E3A] dark:hover:border-[#62605b]"
                    >
                        Create account
                    </Link>
                </template>
            </nav>
        </header>

        <main class="mx-auto flex min-h-[calc(100vh-96px)] w-full max-w-6xl items-center px-6 py-12 lg:px-8">
            <section class="grid w-full items-center gap-10 lg:grid-cols-[1.1fr_0.9fr]">
                <div>
                    <div
                        class="mb-6 inline-flex rounded-full border border-[#19140020] px-4 py-2 text-sm text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                    >
                        App Store & Google Play icon lookup
                    </div>

                    <h1 class="max-w-3xl text-4xl font-semibold tracking-tight lg:text-6xl">
                        Fetch high-resolution app icons in seconds.
                    </h1>

                    <p class="mt-6 max-w-2xl text-base leading-7 text-[#706f6c] lg:text-lg dark:text-[#A1A09A]">
                        Paste a Google Play URL, Apple App Store URL, bundle/package ID,
                        or Apple App ID. The service detects the input type, fetches
                        available icons, and shows a clear result for each store.
                    </p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="appIconFetcher()"
                            class="inline-flex items-center justify-center rounded-md bg-[#1b1b18] px-5 py-3 text-sm font-medium text-white hover:bg-black dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white"
                        >
                            Open App Icon Fetcher
                        </Link>

                        <template v-else>
                            <Link
                                :href="register()"
                                class="inline-flex items-center justify-center rounded-md bg-[#1b1b18] px-5 py-3 text-sm font-medium text-white hover:bg-black dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white"
                            >
                                Create account
                            </Link>

                            <Link
                                :href="login()"
                                class="inline-flex items-center justify-center rounded-md border border-[#19140035] px-5 py-3 text-sm font-medium hover:border-[#1915014a] dark:border-[#3E3E3A] dark:hover:border-[#62605b]"
                            >
                                Log in
                            </Link>
                        </template>
                    </div>
                </div>

                <div
                    class="rounded-2xl border border-[#19140020] bg-white p-6 shadow-sm dark:border-[#3E3E3A] dark:bg-[#161615]"
                >
                    <div class="mb-5">
                        <h2 class="text-lg font-medium">Supported inputs</h2>
                        <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                            Use the format you already have. The service will normalize it.
                        </p>
                    </div>

                    <div class="space-y-3 text-sm">
                        <div class="rounded-lg bg-[#FDFDFC] p-4 dark:bg-[#0a0a0a]">
                            <p class="mb-1 font-medium">Google Play URL</p>
                            <code class="break-all text-[#706f6c] dark:text-[#A1A09A]">
                                https://play.google.com/store/apps/details?id=com.example.app
                            </code>
                        </div>

                        <div class="rounded-lg bg-[#FDFDFC] p-4 dark:bg-[#0a0a0a]">
                            <p class="mb-1 font-medium">Apple App Store URL</p>
                            <code class="break-all text-[#706f6c] dark:text-[#A1A09A]">
                                https://apps.apple.com/app/example/id6503284107
                            </code>
                        </div>

                        <div class="rounded-lg bg-[#FDFDFC] p-4 dark:bg-[#0a0a0a]">
                            <p class="mb-1 font-medium">Bundle / package ID</p>
                            <code class="break-all text-[#706f6c] dark:text-[#A1A09A]">
                                com.example.app
                            </code>
                        </div>

                        <div class="rounded-lg bg-[#FDFDFC] p-4 dark:bg-[#0a0a0a]">
                            <p class="mb-1 font-medium">Apple App ID</p>
                            <code class="break-all text-[#706f6c] dark:text-[#A1A09A]">
                                6503284107 or id6503284107
                            </code>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</template>
