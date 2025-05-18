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
    <main class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8 min-h-[85vh]">

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
        <div v-else class="mt-6 ">
            <h2 class="text-xl font-semibold text-[var(--color-primary)] py-4">Organization Events</h2>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div v-for="org in data" :key="org.id"
                    class="bg-white overflow-hidden shadow-md rounded-lg border-l-4 border-l-[var(--color-secondary)] border border-gray-100">
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
                            <ul v-if="org.events && org.events.length > 0" class="mt-3 space-y-4">
                                <li v-for="event in org.events" :key="event.id"
                                    class="bg-gray-50 rounded-lg p-4 border border-gray-100 transition-all hover:shadow-md">
                                    <div class="flex flex-col">
                                        <h4 class="font-semibold text-lg text-[var(--color-primary)]">{{ event.title }}
                                        </h4>

                                        <div class="mt-2 flex items-center text-gray-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span class="font-medium">{{ event.venue }}</span>
                                        </div>

                                        <div class="mt-2 flex items-center text-gray-600">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span>{{ event.date }}</span>
                                        </div>

                                        <div class="mt-3 flex justify-between items-center">
                                            <span class="font-bold text-[var(--color-secondary)] text-lg">{{ event.price
                                                }}</span>
                                            <button
                                                class="px-3 py-1 bg-[var(--color-primary)] text-white rounded-md text-sm hover:bg-opacity-90 transition-colors">
                                                View Details
                                            </button>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <div v-else class="mt-2 text-sm text-gray-500 bg-gray-50 p-4 rounded-lg">
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-400"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>No events scheduled yet</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6" v-if="!subdomain">
                            <NuxtLink :to="generateUrl(org.tenant_domain)" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-[var(--color-secondary)] hover:bg-opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-secondary)] transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                                Visit Organization
                            </NuxtLink>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>
