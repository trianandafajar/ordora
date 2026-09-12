@props(['name' => 'image', 'existing' => null, 'previewId' => null])

<div x-data="{
        preview: @js($existing),
        dragging: false,
        openFile() { $refs.fileInput.click(); },
        handleFile(e) {
            const file = e.target.files?.[0] || e.dataTransfer?.files?.[0];
            if (!file || !file.type.startsWith('image/')) return;
            const dt = new DataTransfer();
            dt.items.add(file);
            this.$refs.fileInput.files = dt.files;
            const reader = new FileReader();
            reader.onload = (ev) => { this.preview = ev.target.result; };
            reader.readAsDataURL(file);
        }
    }" class="space-y-2">

    <div class="relative flex flex-col items-center justify-center w-full h-40 rounded-lg border-2 border-dashed cursor-pointer
                transition-colors duration-150
                {{ $existing ? 'border-border' : 'border-muted-foreground/25 hover:border-muted-foreground/50' }}"
        :class="{ 'border-primary bg-primary/5': dragging, 'border-muted-foreground/50': !dragging && !preview }"
        @click="openFile()" @dragover.prevent="dragging = true" @dragleave.prevent="dragging = false"
        @drop.prevent="handleFile($event); dragging = false">

        <input type="file" accept="image/*" name="{{ $name }}" class="hidden" x-ref="fileInput"
            @change="handleFile($event)">

        <template x-if="!preview">
            <div class="flex flex-col items-center gap-2 text-muted-foreground">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 opacity-40" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z" />
                </svg>
                <span class="text-sm font-medium">Drop image here or click to browse</span>
                <span class="text-xs text-muted-foreground/70">PNG, JPG up to 2 MB</span>
            </div>
        </template>

        <template x-if="preview">
            <div class="relative w-full h-full p-2 flex items-center justify-center" @click.stop>
                <img :src="preview" class="max-h-36 max-w-full rounded-md object-contain">
                <button type="button" @click="preview = null; $refs.fileInput.value = ''"
                    class="absolute top-3 right-3 rounded-full bg-black/60 p-1 text-white hover:bg-black/80 transition-colors cursor-pointer"
                    @click.stop>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </template>
    </div>
</div>