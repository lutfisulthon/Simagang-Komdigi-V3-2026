{{--
    Uploader lampiran (PDF, DOC/DOCX, XLS/XLSX, PNG/JPG, ZIP; maks. 10 MB per file).
    Variabel: $model — nama properti Alpine induk yang menampung daftar file (x-model).
              $workspaceId — id workspace tujuan unggahan (wajib di server).
--}}
<div x-data="wsUploader(@js($workspaceId ?? null))" x-modelable="items" x-model="{{ $model }}">
    <input type="file" class="sr-only" multiple x-ref="input" :accept="accept" x-on:change="onInput($event)" tabindex="-1" aria-hidden="true">
    <button type="button" class="ws-dropzone w-full" :class="dragging && 'ws-dropzone--active'" x-on:click="pick()"
            x-on:dragover.prevent="dragging = true" x-on:dragleave.prevent="dragging = false" x-on:drop.prevent="onDrop($event)">
        <i class="fas fa-cloud-arrow-up text-xl text-indigo-400"></i>
        <span><span class="font-semibold text-indigo-600">Pilih berkas</span> atau seret ke sini</span>
        <span class="text-xs ws-text-faint">PDF, DOC, XLS, PNG/JPG, ZIP · maks. 10 MB per file</span>
    </button>
    <ul class="mt-2 space-y-2" x-show="items.length">
        <template x-for="item in items" :key="item.key">
            <li class="ws-tile !py-2 flex items-center gap-3" :class="item.status === 'error' && '!border-red-200 !bg-red-50'">
                <i class="fas" :class="item.status === 'error' ? 'fa-circle-exclamation text-red-500' : (item.status === 'done' ? 'fa-circle-check text-emerald-500' : 'fa-file ws-text-faint')"></i>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-800 truncate" x-text="item.name"></p>
                    <p class="text-xs" :class="item.status === 'error' ? 'text-red-600' : 'ws-text-faint'"
                       x-text="item.status === 'error' ? item.error : (item.status === 'done' ? size(item.size) : 'Mengunggah… ' + item.progress + '%')"></p>
                    <div class="ws-progress mt-1.5 !h-1" x-show="item.status === 'uploading'">
                        <div class="ws-progress__bar transition-all" :style="{ width: item.progress + '%' }"></div>
                    </div>
                </div>
                <button type="button" class="ws-icon-btn ws-icon-btn--sm shrink-0" x-on:click="removeItem(item.key)" :aria-label="'Hapus ' + item.name"><i class="fas fa-xmark"></i></button>
            </li>
        </template>
    </ul>
</div>
