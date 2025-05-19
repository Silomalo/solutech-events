<div class="bg-gray-50 dark:bg-transparent min-h-screen py-6">
    @section('title')
        Manage Tenant
    @endsection

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-dark rounded-lg shadow-md overflow-hidden">
            <!-- Header -->
            <div class="flex justify-between items-center px-6 py-4 bg-gray-50 dark:bg-lightDark border-b border-gray dark:border-borderGray">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                    @if ($serial_number)
                        Update
                    @else
                        New
                    @endif
                    Organization
                </h1>
                <div>
                    <a href="{{ route('central.tenants') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-600 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-gray-700 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:focus:ring-offset-gray-800 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        View Organizations
                    </a>
                </div>
            </div>


            <div class="p-6">
                <form wire:submit.prevent="post">
                    <!-- Company Details Section -->
                    <div class="mb-8">
                        <div class="bg-primary/30 border-l-4 border-primary dark:border-primary p-4 mb-6">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400 dark:text-blue-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-blue-700 dark:text-blue-300">
                                        These are company specific details that are registered with the company registrar.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Company Logo -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Company Logo</label>

                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-md bg-white dark:bg-gray-800">
                                @if ($company_logo_temp == null)
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600 dark:text-gray-400">
                                            <label for="company-logo" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-blue-600 dark:text-blue-400 hover:text-blue-500 dark:hover:text-blue-300 focus-within:outline-none">
                                                <span>Upload a file</span>
                                                <input id="company-logo" type="file" class="sr-only" wire:model="company_logo_temp">
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG, GIF up to 10MB</p>
                                    </div>
                                @else
                                    <div class="relative w-full flex items-center justify-center">
                                        <img src="{{ asset($company_logo_temp) }}"
                                            class="max-h-32 object-contain" alt="company-logo">

                                        <button type="button" wire:click="clearField('company_logo_temp')"
                                            class="absolute top-0 right-0 p-1 bg-white dark:bg-gray-700 rounded-full shadow-md hover:bg-red-100 dark:hover:bg-red-900 transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                @endif
                            </div>

                            @error('company_logo')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Company Name and Domain - 2 columns -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Company Legal Name</label>
                                <input type="text"
                                    class="mt-1 p-2 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md"
                                    placeholder="Company name" wire:model="tenant_name" required>
                                @error('tenant_name')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Company Sub-Domain</label>
                                <input type="text"
                                    class="mt-1 p-2 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md"
                                    placeholder="eg: sireet" wire:model.live.debounce.500ms="tenant_domain" required>
                                @error('tenant_domain')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                        {{ $message }}
                                    </p>
                                @enderror

                                <div class="mt-2 text-sm font-medium text-blue-600 dark:text-blue-400">
                                    {{ $fullDomain }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Company Contact Information Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-4 border-b pb-2 border-gray-200 dark:border-gray-700">
                            Company Contact Information
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- Phone -->
                            <div class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        Phone
                                    </div>
                                </label>
                                <input type="text"
                                    class="mt-1 p-2 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md"
                                    placeholder="+254 ... ..." wire:model="phone" required>
                                @error('phone')
                                    <span class="text-red-500 dark:text-red-400 text-sm mt-1 block">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        Email
                                    </div>
                                </label>
                                <input type="email"
                                    class="mt-1 p-2 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md"
                                    placeholder="company@example.com" wire:model="email" required>
                                @error('email')
                                    <span class="text-red-500 dark:text-red-400 text-sm mt-1 block">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <!-- Website -->
                            <div class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                        </svg>
                                        Website
                                    </div>
                                </label>
                                <input type="text"
                                    class="mt-1 p-2 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md"
                                    placeholder="www.example.com" wire:model="website" required>
                                @error('website')
                                    <span class="text-red-500 dark:text-red-400 text-sm mt-1 block">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Location & Registration Section -->
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-4 border-b pb-2 border-gray-200 dark:border-gray-700">
                            Location & Registration
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- Address -->
                            <div class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Address
                                    </div>
                                </label>
                                <input type="text"
                                    class="mt-1 p-2 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md"
                                    placeholder="e.g: Nairobi, Kenya" wire:model="address" required>
                                @error('address')
                                    <span class="text-red-500 dark:text-red-400 text-sm mt-1 block">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <!-- City -->
                            <div class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        City
                                    </div>
                                </label>
                                <input type="text"
                                    class="mt-1 p-2 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md"
                                    placeholder="e.g: Nairobi" wire:model="city">
                                @error('city')
                                    <span class="text-red-500 dark:text-red-400 text-sm mt-1 block">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <!-- Postal Code -->
                            <div class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        Postal Code
                                    </div>
                                </label>
                                <input type="text"
                                    class="mt-1 p-2 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md"
                                    placeholder="e.g: 00100" wire:model="postal_code" required>
                                @error('postal_code')
                                    <span class="text-red-500 dark:text-red-400 text-sm mt-1 block">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <!-- Registration Number -->
                            <div class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Registration Number
                                    </div>
                                </label>
                                <input type="text"
                                    class="mt-1 p-2 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md"
                                    placeholder="#FABT1234" wire:model="registration_no">
                                @error('registration_no')
                                    <span class="text-red-500 dark:text-red-400 text-sm mt-1 block">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <!-- KRA PIN -->
                            <div class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                        </svg>
                                        KRA PIN
                                    </div>
                                </label>
                                <input type="text"
                                    class="mt-1 p-2 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md"
                                    placeholder="A123456789P" wire:model="kra_pin">
                                @error('kra_pin')
                                    <span class="text-red-500 dark:text-red-400 text-sm mt-1 block">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>


                    <!-- Contact Person Section -->
                    <div class="mb-8">
                        <div class="flex items-center mb-4">
                            <div class="flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-500 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <h3 class="ml-2 text-lg font-medium text-gray-800 dark:text-gray-200 border-b border-indigo-200 dark:border-indigo-700 flex-grow pb-2">
                                CONTACT PERSON
                            </h3>
                        </div>

                        <div class="p-4 mb-6 border border-indigo-200 dark:border-indigo-700 rounded-lg bg-indigo-50 dark:bg-indigo-900/30">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-indigo-700 dark:text-indigo-300">
                                        These are the details of the person who will be contacted in case of any issues.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Contact Name & Title -->
                            <div class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-indigo-500 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        Contact Person Name
                                    </div>
                                </label>
                                <input type="text"
                                    class="mt-1 p-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md"
                                    placeholder="John Doe" required wire:model="contact_name">
                                @error('contact_name')
                                    <span class="text-red-500 dark:text-red-400 text-sm mt-1 block">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-indigo-500 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        Contact Person Title
                                    </div>
                                </label>
                                <input type="text"
                                    class="mt-1 p-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md"
                                    placeholder="CEO, Manager, etc." required wire:model="contact_title">
                                @error('contact_title')
                                    <span class="text-red-500 dark:text-red-400 text-sm mt-1 block">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <!-- Contact Email & Phone -->
                            <div class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-indigo-500 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        Contact Person Email
                                    </div>
                                </label>
                                <input type="email"
                                    class="mt-1 p-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md"
                                    placeholder="contact@example.com" required wire:model="contact_email">
                                @error('contact_email')
                                    <span class="text-red-500 dark:text-red-400 text-sm mt-1 block">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div class="p-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-indigo-500 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        Contact Person Phone
                                    </div>
                                </label>
                                <input type="text"
                                    class="mt-1 p-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md"
                                    placeholder="+254 712 345 678" required wire:model="contact_phone">
                                @error('contact_phone')
                                    <span class="text-red-500 dark:text-red-400 text-sm mt-1 block">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>




                        <h3 class="uppercase text-secondary dark:text-gray-300 text-lg font-medium my-4">
                            LEGAL ENTITY
                        </h3>
                        <p class="p-2 border border-indigo-200 dark:border-indigo-700 rounded bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 mb-4">
                            This are the details of the legal entity of the company.
                        </p>



                        <div class="mb-3 col-sm-12 group">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Details Regarding Organization
                            </label>
                            <div class="">
                                <textarea wire:model="description"
                                    class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md"
                                    rows="3" placeholder="Description" style="height: 100px;"></textarea>
                            </div>
                        </div>
                        <div class="mb-3 col-sm-6 group">
                            <label for="legal-entity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Legal Entity
                            </label>
                            <select wire:model="legal_entity" name="legal_entity" id="legal-entity"
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md"
                                required>
                                <option value="Sole Proprietorship">Sole Proprietorship</option>
                                <option value="Partnership">Partnership</option>
                                <option value="Limited Liability Corporation">Limited Liability Corporation</option>
                                <option value="Non Profit">Non Profit</option>
                                <option value="Foreign Corporation">Foreign Corporation</option>
                                <option value="Non Profit Organization">Non Profit Organization</option>
                                <option value="Community Based Organization">Community Based Organization</option>
                                <option value="Faith Based Organization">Faith Based Organization</option>
                                <option value="Government Institution">Government Institution</option>
                            </select>
                            @error('legal_entity')
                                <span class="text-red-500 dark:text-red-400 text-sm mt-1 block">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>

                        <div class="mb-3 col-sm-3 group">
                            <label for="active" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Active
                            </label>
                            <select wire:model.live="active" name="active" id="active"
                                class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-md">
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                            @error('active')
                                <span class="text-red-500 dark:text-red-400 text-sm mt-1 block">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>
                    {{-- loop through errors --}}
                    @if ($errors->any())
                        <div class="mb-4">
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                <strong class="font-bold">Whoops!</strong>
                                <ul class="list-disc pl-5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-center mt-6 mb-3">
                        <button type="submit" class="px-6 py-2 bg-gray-600 dark:bg-gray-700 text-white rounded-md hover:bg-gray-700 dark:hover:bg-gray-600 transition inline-flex items-center focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 dark:focus:ring-offset-gray-800">
                            @if ($serial_number)
                                Update Company
                            @else
                                Create Company
                            @endif
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.707l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13a1 1 0 102 0V9.414l1.293 1.293a1 1 0 001.414-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
