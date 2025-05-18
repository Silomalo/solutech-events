<script setup lang="ts">
import type { OrganizationalType } from '~/lib/types'
import { generateUrl, getSubdomain } from '~/lib/utils'

const url = useRuntimeConfig().public.apiUrl
const subdomain = getSubdomain()
// const { data, error, status } = await useFetch<OrganizationalType[]>(url + '/api/organizations')
const { data, error, status } = await useFetch<OrganizationalType[]>(
  subdomain ? `${url}/api/organizations/${subdomain}` : `${url}/api/organizations`
)
    if (error.value) {
        console.error('Error fetching events:', error.value)
        // return
    }
    console.log('data', data.value)
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header Navigation -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8">
                <nav class="flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-[var(--color-primary)]">Organization Portal</h2>
                    <div class="flex space-x-6">
                        <NuxtLink to="/register"
                            class="text-sm font-medium text-[var(--color-tertiary)] hover:text-opacity-80 px-3 py-2 rounded-md">
                            Register</NuxtLink>
                        <NuxtLink to="/login"
                            class="text-sm font-medium bg-[var(--color-primary)] text-white hover:bg-opacity-90 px-3 py-2 rounded-md">
                            Login</NuxtLink>
                    </div>
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="border-b border-gray-200 pb-5 mb-5">
                <h1 class="text-3xl font-bold text-[var(--color-primary)]">
                    Organizations
                </h1>
                <div class="mt-3 flex flex-wrap items-center">
                    <NuxtLink to="/events"
                        class="mr-4 mt-2 inline-flex items-center px-4 py-2 border border-[var(--color-tertiary)] bg-white rounded-md shadow-sm text-sm font-medium text-[var(--color-tertiary)] hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-tertiary)]">
                        <span>View Events</span>
                    </NuxtLink>
                    <NuxtLink to="/events/create"
                        class="mt-2 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[var(--color-primary)] hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-primary)]">
                        <span>Create Event</span>
                    </NuxtLink>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="status === 'pending'" class="flex justify-center py-12">
                <div class="animate-pulse flex space-x-4">
                    <div class="h-12 w-12 bg-[var(--color-primary)] opacity-50 rounded-full"></div>
                    <div class="flex-1 space-y-4 py-1">
                        <div class="h-4 bg-[var(--color-primary)] opacity-30 rounded w-3/4"></div>
                        <div class="space-y-2">
                            <div class="h-4 bg-[var(--color-primary)] opacity-30 rounded"></div>
                            <div class="h-4 bg-[var(--color-primary)] opacity-30 rounded w-5/6"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="rounded-md bg-red-50 p-4 my-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">An error occurred while loading organizations</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <p>{{ error }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Display -->
            <div v-else class="mt-6">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    <div v-for="org in data" :key="org.id" class="bg-white overflow-hidden shadow-md rounded-lg border-l-4 border-l-[var(--color-secondary)] border border-gray-100">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 bg-[var(--color-primary)] rounded-md p-3">
                                    <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h2 class="text-lg font-medium text-[var(--color-primary)]">{{ org.tenant_name }}</h2>
                                    <p class="text-sm text-gray-500">{{ org.tenant_domain }}</p>
                                </div>
                            </div>

                            <div class="mt-6">
                                <h3 class="text-sm font-medium text-[var(--color-tertiary)]">Events</h3>
                                <ul v-if="org.events && org.events.length > 0" class="mt-3 pl-5 list-disc space-y-2">
                                    <li v-for="event in org.events" :key="event.id" class="text-sm">
                                        <div class="flex flex-col">
                                            <span class="font-medium text-[var(--color-primary)]">{{ event.title }}</span>
                                            <span class="font-medium text-[var(--color-primary)]">{{ event.venue }}</span>
                                            <div class="flex justify-between items-center mt-1">
                                                <span class="text-gray-500">{{ event.date }}</span>
                                                <span class="font-medium text-[var(--color-secondary)]">{{ event.price }}</span>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                                <div v-else class="mt-2 text-sm text-gray-500">
                                    No events scheduled
                                </div>
                            </div>

                            <div class="mt-6">
                                <NuxtLink :to="generateUrl(org.tenant_domain)"
                                target="_blank"
                                rel="noopener noreferrer"
                                    class="inline-flex items-center px-3 py-2 border border-[var(--color-secondary)] text-sm leading-4 font-medium rounded-md text-[var(--color-secondary)] bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-secondary)]">
                                    Visit Organization
                                    {{ getSubdomain() }}
                                </NuxtLink>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
