<div>
    @section('title')
        Tenants
    @endsection
    <div class="container mx-auto p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md">
            <div class="flex justify-between items-center p-4 border-b dark:border-gray-700">
                <h1 class="text-xl font-bold uppercase dark:text-gray-100">Registered Tenants</h1>
                <div class="flex space-x-2">
                    <a href="{{ route('central.manage-tenant') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600 transition">New Tenant</a>
                </div>
            </div>

            <div class="p-4">
                <div class="flex flex-col md:flex-row justify-between items-center pb-4">
                    <div class="w-full md:w-1/3 mb-4 md:mb-0">
                        <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search tenants</label>
                        <input type="search" wire:model.live.debounce.300ms="search" id="search" name="search"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                            placeholder="Search tenant by name or domain">
                    </div>

                    <div class="flex items-center space-x-2">
                        <div>
                            <h3 class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                No of Records
                            </h3>
                        </div>
                        <div class="bg-white dark:bg-gray-700 border dark:border-gray-600 rounded">
                            <select name="pagination" id="pagination" wire:model.live="pagination"
                                class="border-none rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="100">100</option>
                                <option value="1000">1000</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden min-h-[70vh]">
                        <thead>
                            <tr class="bg-gray-700 text-white">
                                <th class="px-4 py-2 text-left">#NO</th>
                                <th class="px-4 py-2 text-left">Name</th>
                                <th class="px-4 py-2 text-left">Domain</th>
                                <th class="px-4 py-2 text-left">Contact</th>
                                <th class="px-4 py-2 text-left">Status</th>
                                <th class="px-4 py-2 text-left">Created At</th>
                                <th class="px-4 py-2 text-center w-24">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tenants as $key => $item)
                                <tr class="{{ ($key + 1) % 2 == 0 ? 'bg-gray-50 dark:bg-gray-700' : 'bg-white dark:bg-gray-800' }} hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3 border-b dark:border-gray-700">
                                        <div class="flex items-center justify-center gap-2">
                                            <span class="dark:text-gray-200">
                                                {{ $key + 1 }}
                                            </span>
                                            @if ($item->company_logo)
                                                <img src="{{ $item->company_logo }}" alt="{{ $item->tenant_name }}"
                                                    class="object-cover border dark:border-gray-600 rounded-full w-10 h-10">
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 border-b dark:border-gray-700">
                                        <div>
                                            <span class="font-semibold dark:text-white">
                                                {{ $item->tenant_name }} </span>
                                            <div class="text-sm text-gray-500 dark:text-gray-300">
                                                DB: {{ $item->database_name }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 border-b dark:border-gray-700">
                                        <div>
                                            <span class="font-semibold dark:text-white">
                                                {{ $item->tenant_domain }}
                                            </span>
                                            <div class="text-sm text-gray-500 dark:text-gray-300">
                                                @php
                                                    $fullDomain = \App\Models\Tenant::getFullDomainProperty(
                                                        $item->tenant_domain,
                                                    );
                                                @endphp

                                                @if ($item->status)
                                                    <a href="{{ $fullDomain }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">
                                                        {{ $fullDomain }}
                                                    </a>
                                                @else
                                                    <span class="text-gray-500 dark:text-gray-400">{{ $fullDomain }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 border-b dark:border-gray-700">
                                        <div>
                                            <div class="font-semibold dark:text-white">
                                                {{ $item->phone }} </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-300">
                                                {{ $item->email }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 border-b dark:border-gray-700">
                                        <div class="flex items-center">
                                            @if ($item->status)
                                                <span class="px-2 py-1 text-xs font-semibold text-white bg-green-500 rounded-full">Active</span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-semibold text-white bg-red-500 rounded-full">Inactive</span>
                                            @endif
                                            <a href="{{ route('central.view-tenant-staffs', $item->tenant_domain) }}" class="ml-2 text-blue-600 dark:text-blue-400 hover:underline text-xs">
                                                <span class="inline-flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                    Staff
                                                </span>
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 border-b dark:border-gray-700">
                                        <div>
                                            <div class="font-semibold dark:text-white">
                                                {{ \Carbon\Carbon::parse($item->created_at)->toDayDateTimeString() ?? 'Missing Creation Timestamp' }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 border-b dark:border-gray-700 text-center">
                                        <div class="relative inline-block text-left" x-data="{ open: false }">
                                            <button @click="open = !open" type="button" class="p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                                                </svg>
                                            </button>
                                            <div x-show="open"
                                                 @click.outside="open = false"
                                                 class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 focus:outline-none z-10"
                                                 role="menu">
                                                <div class="py-1" role="none">
                                                    <a href="{{ route('central.manage-tenant', $item->serial_number) }}"
                                                        class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                        Edit
                                                    </a>
                                                    <a href="#"
                                                        wire:click="toggleStatus('{{ $item->serial_number }}')"
                                                        class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        {{ $item->status ? 'Deactivate' : 'Activate' }}
                                                    </a>
                                                    <a href="#"
                                                        wire:click="deleteTenant('{{ $item->serial_number }}')"
                                                        wire:confirm.prompt="Are you sure?\n\nType DELETE to confirm|DELETE"
                                                        class="flex items-center px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        Delete
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">No companies found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="py-4 dark:text-white">
                    {!! $tenants->links() !!}
                </div>
            </div>
        </div>
    </div>
</div>
