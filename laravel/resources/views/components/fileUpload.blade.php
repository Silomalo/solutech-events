@props(['model', 'label' => null, 'acceptedTypes' => null])
<div class="w-full">
    <label x-data="{
        fileUrl: null,
        fileName: null,
        fileType: null,
        progress: 0,
        uploading: false
    }" x-on:livewire-upload-start="uploading = true"
        x-on:livewire-upload-finish="setTimeout(() => { uploading = false; progress = 0 }, 500)"
        x-on:livewire-upload-error="uploading = false" x-on:livewire-upload-progress="progress = $event.detail.progress"
        class="relative flex flex-col w-full h-32 overflow-hidden border border-dashed rounded-2xl hover:bg-gray-100 hover:border-secondary group">

        <!-- Progress Bar -->
        <div x-show="uploading" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-300"
            class="absolute inset-0 z-20 bg-gray-100 bg-opacity-80">
            <div class="h-full transition-all duration-700 ease-out bg-green-500 bg-opacity-20"
                :style="`width: ${progress}%`">
            </div>
            <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-sm font-medium text-gray-600 animate-bounce " x-text="`Uploading... ${progress}%`"></span>
            </div>
        </div>

        <!-- Empty State -->
        <template x-if="!fileUrl && !fileName">
            <div class="flex flex-col items-center justify-center w-full pt-7">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-10 h-10 text-secondary">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" />
                </svg>

                <p class="pt-1 tracking-wider text-gray-400 group-hover:text-warning">
                    {{ $label ?? 'Select a file' }}
                </p>
            </div>
        </template>

        <!-- File Preview -->
        <template x-if="fileUrl || fileName">
            <div class="absolute inset-0 w-full h-full group">
                <!-- Image Preview -->
                <template x-if="fileType?.startsWith('image/')">
                    <img :src="fileUrl" class="object-contain w-full h-full rounded-lg" />
                </template>
                <!-- Non-image Preview -->
                <template x-if="!fileType?.startsWith('image/')">
                    <div class="flex flex-col items-center justify-center w-full h-full">
                        <svg class="w-8 h-8 mb-2 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-sm font-medium text-gray-600" x-text="fileName"></p>
                    </div>
                </template>

                <!-- Delete Button -->
                <div class="absolute z-10 top-2 right-2">
                    <button @click.prevent="fileUrl = null; fileName = null; fileType = null"
                        class="p-2 bg-white rounded-full shadow-lg hover:bg-red-600">
                        {{-- <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg> --}}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor"  class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>

                    </button>
                </div>
            </div>
        </template>

        <input type="file" x-ref="input" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
            wire:model.debounce.500ms.live="{{ $model }}" accept="{{ $acceptedTypes ?? '*' }}"
            x-on:change="
                    const file = $event.target.files[0];
                    if (file) {
                        fileName = file.name;
                        fileType = file.type;
                        fileUrl = file.type.startsWith('image/') ? URL.createObjectURL(file) : null;
                    }
                " />
    </label>
    <div class="text-xs text-red-600 dark:text-red-400">
        @error($model)
            {{ $message }}
        @enderror
    </div>
</div>
