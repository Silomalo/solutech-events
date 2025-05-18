<div>
    @section('title')
        Tenants
    @endsection
    <div class="container mx-auto p-4">
        <div class="bg-white rounded-lg shadow-md">
            <div class="flex justify-between items-center p-4 border-b">
                <h1 class="text-xl font-bold uppercase">Registered Tenants</h1>
                <div class="flex space-x-2">
                    <a href="{{ route('central.manage-tenant') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition">New Tenant</a>
                </div>
            </div>

            <div class="p-4">
                <div class="flex flex-col md:flex-row justify-between items-center pb-4">
                    <div class="w-full md:w-1/3 mb-4 md:mb-0">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search tenants</label>
                        <input type="search" wire:model.live.debounce.300ms="search" id="search" name="search"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Search tenant by name or domain">
                    </div>

                    <div class="flex items-center space-x-2">
                        <div>
                            <h3 class="text-sm font-medium text-gray-600">
                                No of Records
                            </h3>
                        </div>
                        <div class="bg-white border rounded">
                            <select name="pagination" id="pagination" wire:model.live="pagination"
                                class="border-none rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="100">100</option>
                                <option value="1000">1000</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden min-h-[70vh]">
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
                                <tr class="{{ ($key + 1) % 2 == 0 ? 'bg-gray-50' : 'bg-white' }} hover:bg-gray-100">
                                    <td class="px-4 py-3 border-b">
                                        <div class="flex items-center justify-center gap-2">
                                            <span>
                                                {{ $key + 1 }}
                                            </span>
                                            @if ($item->company_logo)
                                                <img src="{{ $item->company_logo }}" alt="{{ $item->tenant_name }}"
                                                    class="object-cover border rounded-full w-10 h-10">
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 border-b">
                                        <div>
                                            <span class="font-semibold">
                                                {{ $item->tenant_name }} </span>
                                            <div class="text-sm text-gray-500">
                                                DB: {{ $item->database_name }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 border-b">
                                        <div>
                                            <span class="font-semibold">
                                                {{ $item->tenant_domain }}
                                            </span>
                                            <div class="text-sm text-gray-500">
                                                @php
                                                    $fullDomain = \App\Models\Tenant::getFullDomainProperty(
                                                        $item->tenant_domain,
                                                    );
                                                @endphp

                                                @if ($item->status)
                                                    <a href="{{ $fullDomain }}" target="_blank" class="text-blue-600 hover:underline">
                                                        {{ $fullDomain }}
                                                    </a>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-semibold text-white bg-red-500 rounded-full">Inactive</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 border-b">
                                        <div>
                                            <div class="font-semibold">
                                                {{ $item->phone }} </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $item->email }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 border-b">
                                        <a href="{{ route('central.view-tenant-staffs', $item->tenant_domain) }}" class="text-blue-600 hover:underline">
                                            Staff
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 border-b">
                                        <div>
                                            <div class="font-semibold">
                                                {{ \Carbon\Carbon::parse($item->created_at)->toDayDateTimeString() ?? 'Missing Creation Timestamp' }}
                                                <div class="text-sm">
                                                    @if ($item->status)
                                                        <span class="px-2 py-1 text-xs font-semibold text-white bg-green-500 rounded-full">Active</span>
                                                    @else
                                                        <span class="px-2 py-1 text-xs font-semibold text-white bg-red-500 rounded-full">Inactive</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 border-b text-center">
                                        <div class="relative inline-block text-left" x-data="{ open: false }">
                                            <button @click="open = !open" type="button" class="p-2 rounded-full hover:bg-gray-100">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                                                </svg>
                                            </button>
                                            <div x-show="open"
                                                 @click.outside="open = false"
                                                 class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none"
                                                 role="menu">
                                                <div class="py-1" role="none">
                                                    <a href="{{ route('central.manage-tenant', $item->serial_number) }}"
                                                        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                        </svg>
                                                        Edit
                                                    </a>
                                                    <a href="#"
                                                        wire:click="toggleStatus('{{ $item->serial_number }}')"
                                                        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                        </svg>
                                                        {{ $item->status ? 'Deactivate' : 'Activate' }}
                                                    </a>
                                                    <a href="#"
                                                        wire:click="deleteTenant('{{ $item->serial_number }}')"
                                                        wire:confirm.prompt="Are you sure?\n\nType DELETE to confirm|DELETE"
                                                        class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
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
                                    <td colspan="7" class="px-4 py-3 text-center text-gray-500">No companies found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="py-4">
                    {!! $tenants->links() !!}
                </div>
            </div>
        </div>
    </div>
</div>
