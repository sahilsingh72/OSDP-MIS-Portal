@extends('layouts.app', ['title' => "Evidence Upload Center - {$batch->batch_code}", 'headerTitle' => 'Evidence Management'])

@section('content')
<script>
function evidenceManager() {
    return {
        // Edit modal state
        editModalOpen: false,
        editForm: {
            id: '',
            evidence_type: '',
            type_label: '',
            original_filename: '',
            formatted_size: '',
            remarks: '',
            actionUrl: ''
        },
        editFile: null,
        editFileName: '',
        editFileSize: '',
        editFileType: '',
        editFilePreview: null,
        editError: '',
        editInputAccept: '.pdf,application/pdf',
        editAllowedDesc: 'PDF document only (.pdf)',
        editMaxMb: {{ $maxPdfMb }},

        // Upload form state
        uploadType: '{{ old('evidence_type', '') }}',
        uploadFile: null,
        uploadFileName: '',
        uploadFileSize: '',
        uploadFileType: '',
        uploadFilePreview: null,
        uploadError: '',
        uploadInputAccept: '.pdf,application/pdf,image/jpeg,image/png,video/mp4,video/quicktime,video/x-msvideo',
        uploadAllowedDesc: 'Select an evidence item type first',
        uploadMaxMb: {{ $maxPdfMb }},

        // 6 Evidence Item Rules
        rules: {
            attendance_sheet: {
                label: 'Attendance Sheet (Form 2)',
                category: 'pdf',
                accept: '.pdf,application/pdf',
                extensions: ['pdf'],
                maxMb: {{ $maxPdfMb }},
                desc: 'PDF document only (.pdf)',
                error: 'Attendance Sheet must be a PDF file (.pdf).'
            },
            photo_1: {
                label: 'Photograph 1 (Group Banner)',
                category: 'image',
                accept: '.jpg,.jpeg,.png,image/jpeg,image/png',
                extensions: ['jpg', 'jpeg', 'png'],
                maxMb: {{ $maxPhotoMb }},
                desc: 'Image file only (.jpg, .jpeg, .png)',
                error: 'Photograph 1 must be an image file (.jpg, .jpeg, .png).'
            },
            photo_2: {
                label: 'Photograph 2 (Trainer Teaching)',
                category: 'image',
                accept: '.jpg,.jpeg,.png,image/jpeg,image/png',
                extensions: ['jpg', 'jpeg', 'png'],
                maxMb: {{ $maxPhotoMb }},
                desc: 'Image file only (.jpg, .jpeg, .png)',
                error: 'Photograph 2 must be an image file (.jpg, .jpeg, .png).'
            },
            video: {
                label: 'Short Video Clip (MP4)',
                category: 'video',
                accept: '.mp4,.mov,.avi,video/mp4,video/quicktime,video/x-msvideo',
                extensions: ['mp4', 'mov', 'avi'],
                maxMb: {{ $maxVideoMb }},
                desc: 'Video format only (.mp4, .mov, .avi)',
                error: 'Short Video Clip must be a video format (.mp4, .mov, .avi).'
            },
            feedback_forms: {
                label: 'Feedback Forms (Form 1)',
                category: 'pdf',
                accept: '.pdf,application/pdf',
                extensions: ['pdf'],
                maxMb: {{ $maxPdfMb }},
                desc: 'PDF document only (.pdf)',
                error: 'Feedback Forms must be a PDF file (.pdf).'
            },
            batch_completion_report: {
                label: 'Batch Completion Report (Form 3)',
                category: 'pdf',
                accept: '.pdf,application/pdf',
                extensions: ['pdf'],
                maxMb: {{ $maxPdfMb }},
                desc: 'PDF document only (.pdf)',
                error: 'Batch Completion Report must be a PDF file (.pdf).'
            }
        },

        init() {
            if (this.uploadType && this.rules[this.uploadType]) {
                this.onUploadTypeChange();
            }
        },

        onUploadTypeChange() {
            this.uploadError = '';
            const rule = this.rules[this.uploadType];
            if (rule) {
                this.uploadInputAccept = rule.accept;
                this.uploadAllowedDesc = rule.desc;
                this.uploadMaxMb = rule.maxMb;

                // Re-validate if file was already chosen
                if (this.uploadFile) {
                    this.validateFile(this.uploadFile, this.uploadType, 'upload');
                }
            } else {
                this.uploadInputAccept = '.pdf,application/pdf,image/jpeg,image/png,video/mp4,video/quicktime,video/x-msvideo';
                this.uploadAllowedDesc = 'Select an evidence item type first';
                this.clearUploadFile();
            }
        },

        handleUploadFileSelect(e) {
            const file = e.target.files && e.target.files[0];
            if (!file) return;

            if (!this.uploadType) {
                this.uploadError = 'Please select an Evidence Item Type first before choosing a file.';
                e.target.value = '';
                this.clearUploadFile();
                return;
            }

            this.validateFile(file, this.uploadType, 'upload', e.target);
        },

        handleEditFileSelect(e) {
            const file = e.target.files && e.target.files[0];
            if (!file) return;

            this.validateFile(file, this.editForm.evidence_type, 'edit', e.target);
        },

        validateFile(file, evidenceType, context, inputElement = null) {
            const rule = this.rules[evidenceType];
            if (!rule) return true;

            const filename = file.name || '';
            const ext = filename.split('.').pop().toLowerCase();
            const fileSizeMb = file.size / (1024 * 1024);

            // Format check
            if (!rule.extensions.includes(ext)) {
                const errMsg = `Invalid file format! ${rule.error} You selected "${filename}".`;
                if (context === 'upload') {
                    this.uploadError = errMsg;
                    this.clearUploadFile(inputElement);
                } else {
                    this.editError = errMsg;
                    this.clearEditFile(inputElement);
                }
                return false;
            }

            // Size check
            if (fileSizeMb > rule.maxMb) {
                const errMsg = `File size too large! Maximum allowed size for ${rule.label} is ${rule.maxMb} MB. Selected file is ${fileSizeMb.toFixed(2)} MB.`;
                if (context === 'upload') {
                    this.uploadError = errMsg;
                    this.clearUploadFile(inputElement);
                } else {
                    this.editError = errMsg;
                    this.clearEditFile(inputElement);
                }
                return false;
            }

            // Valid file
            const formattedSize = fileSizeMb >= 1 
                ? (fileSizeMb.toFixed(2) + ' MB') 
                : ((file.size / 1024).toFixed(1) + ' KB');

            if (context === 'upload') {
                this.uploadError = '';
                this.uploadFile = file;
                this.uploadFileName = filename;
                this.uploadFileSize = formattedSize;
                this.uploadFileType = rule.category;

                if (this.uploadFilePreview && typeof this.uploadFilePreview === 'string' && this.uploadFilePreview.startsWith('blob:')) {
                    try { URL.revokeObjectURL(this.uploadFilePreview); } catch(e) {}
                }

                if (rule.category === 'image' || rule.category === 'video') {
                    this.uploadFilePreview = URL.createObjectURL(file);
                } else {
                    this.uploadFilePreview = null;
                }
            } else {
                this.editError = '';
                this.editFile = file;
                this.editFileName = filename;
                this.editFileSize = formattedSize;
                this.editFileType = rule.category;

                if (this.editFilePreview && typeof this.editFilePreview === 'string' && this.editFilePreview.startsWith('blob:')) {
                    try { URL.revokeObjectURL(this.editFilePreview); } catch(e) {}
                }

                if (rule.category === 'image' || rule.category === 'video') {
                    this.editFilePreview = URL.createObjectURL(file);
                } else {
                    this.editFilePreview = null;
                }
            }

            return true;
        },

        clearUploadFile(inputElement = null) {
            if (this.uploadFilePreview && typeof this.uploadFilePreview === 'string' && this.uploadFilePreview.startsWith('blob:')) {
                try { URL.revokeObjectURL(this.uploadFilePreview); } catch(e) {}
            }
            this.uploadFile = null;
            this.uploadFileName = '';
            this.uploadFileSize = '';
            this.uploadFileType = '';
            this.uploadFilePreview = null;
            const el = inputElement || document.getElementById('upload-file-input');
            if (el) el.value = '';
        },

        clearEditFile(inputElement = null) {
            if (this.editFilePreview && typeof this.editFilePreview === 'string' && this.editFilePreview.startsWith('blob:')) {
                try { URL.revokeObjectURL(this.editFilePreview); } catch(e) {}
            }
            this.editFile = null;
            this.editFileName = '';
            this.editFileSize = '';
            this.editFileType = '';
            this.editFilePreview = null;
            const el = inputElement || document.getElementById('edit-file-input');
            if (el) el.value = '';
        },

        openEdit(upload, actionUrl) {
            this.editForm = {
                id: upload.id,
                evidence_type: upload.evidence_type,
                type_label: upload.type_label || (upload.evidence_type ? upload.evidence_type.replace(/_/g, ' ').toUpperCase() : ''),
                original_filename: upload.original_filename || '',
                formatted_size: upload.formatted_size || '',
                remarks: upload.remarks || '',
                actionUrl: actionUrl
            };
            this.editError = '';
            this.clearEditFile();

            const rule = this.rules[upload.evidence_type];
            if (rule) {
                this.editInputAccept = rule.accept;
                this.editAllowedDesc = rule.desc;
                this.editMaxMb = rule.maxMb;
            }

            this.editModalOpen = true;
        }
    };
}
</script>

<div class="space-y-6" x-data="evidenceManager()" x-init="init()">

    <!-- Top Header & SLA Tracker -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center space-x-3 mb-1">
                <span class="text-xl font-extrabold text-slate-900 tracking-tight font-mono">{{ $batch->batch_code }}</span>
                <span class="text-xs px-2.5 py-0.5 rounded-full font-bold {{ $evidenceStatus['is_complete'] ? 'bg-emerald-100 text-emerald-800' : 'bg-orange-100 text-orange-800' }}">
                    {{ $evidenceStatus['completed_count'] }}/6 Items Attached
                </span>
            </div>
            <p class="text-xs text-slate-500">
                Official Evidence Pack. Files stored privately and securely outside public webroot.
            </p>
        </div>

        <div class="flex items-center space-x-3">
            @if(!auth()->user()->hasRole(['super-admin', 'pmu', 'ocac']) && $evidenceStatus['is_complete'] && in_array($batch->status, ['scheduled', 'in_progress', 'awaiting_evidence', 'correction_required']))
                @can('submit', $batch)
                <form action="{{ route('batches.submit', $batch) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" onclick="return confirm('Confirm submission of batch evidence pack for verification?')"
                        class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold text-xs shadow-md shadow-orange-500/20 transition">
                        ✓ Ready - Submit for Verification
                    </button>
                </form>
                @endcan
            @endif
            <a href="{{ route('batches.show', $batch) }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900">
                ← Return to Batch
            </a>
        </div>
    </div>

    <!-- Upload Form / Locked State Card -->
    @php
        $evidenceTypes = [
            'attendance_sheet' => [
                'number' => 1,
                'name' => 'Attendance Sheet (Form 2)',
                'desc' => 'Physical signature attendance sheet (both sides)',
            ],
            'photo_1' => [
                'number' => 2,
                'name' => 'Photograph 1 (Group Banner)',
                'desc' => 'Wide angle photograph of all participants with 8x5 banner',
            ],
            'photo_2' => [
                'number' => 3,
                'name' => 'Photograph 2 (Trainer Teaching)',
                'desc' => 'Field Trainer actively delivering module at podium/board',
            ],
            'video' => [
                'number' => 4,
                'name' => 'Short Video Clip (MP4)',
                'desc' => 'Landscape video (~1 min) capturing active session training',
            ],
            'feedback_forms' => [
                'number' => 5,
                'name' => 'Feedback Forms (Form 1)',
                'desc' => 'Merged PDF scan of all collected physical Form 1 evaluations',
            ],
            'batch_completion_report' => [
                'number' => 6,
                'name' => 'Batch Completion Report (Form 3)',
                'desc' => 'Form 3 Part C summary signed by DLC/Trainer',
            ],
        ];

        $uploadedMap = $batch->uploads->keyBy('evidence_type');
        $uploadedCount = $uploadedMap->count();
        $allSixUploaded = $uploadedCount >= 6;
    @endphp

    @can('uploadEvidence', $batch)
        @if($allSixUploaded)
            <!-- All 6 Uploaded Locked State -->
            <div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50 rounded-2xl p-6 border border-emerald-200/90 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-start space-x-3.5">
                        <div class="w-11 h-11 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shadow-md shadow-emerald-600/20 shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <div class="flex items-center space-x-2">
                                <h2 class="text-sm font-bold text-emerald-950 uppercase tracking-wider">
                                    All 6 Evidence Items Uploaded — Upload Form Locked
                                </h2>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-200 text-emerald-900 border border-emerald-300">
                                    6 of 6 Complete
                                </span>
                            </div>
                            <p class="text-xs text-emerald-800 leading-relaxed max-w-2xl">
                                All six mandatory evidence files have been successfully uploaded. 
                                Direct uploading is now <strong>locked</strong>. If you want to replace, update, or change any uploaded file, 
                                please click the <a href="#uploaded-files-table" class="font-bold underline text-emerald-950 hover:text-emerald-700">Edit</a> button 
                                next to that file in the table below.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2 shrink-0">
                        <a href="#uploaded-files-table" class="px-4 py-2.5 rounded-xl bg-white hover:bg-emerald-100 text-emerald-900 border border-emerald-300 font-semibold text-xs transition shadow-xs flex items-center space-x-1.5">
                            <svg class="w-4 h-4 text-emerald-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <span>Go to Edit Files</span>
                        </a>
                    </div>
                </div>
            </div>
        @else
            <!-- Upload Form Card with Individual Item Locking -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-slate-100">
                    <div>
                        <div class="flex items-center space-x-2">
                            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                                Upload Evidence Item
                            </h2>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $uploadedCount }}/6 Uploaded
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Select a pending evidence item below. Already uploaded items are <strong>locked</strong>; to change them, use <strong>Edit</strong> in the table below.
                        </p>
                    </div>
                    <div class="text-[11px] text-slate-400 font-medium">
                        Allowed: Photos (≤ {{ $maxPhotoMb }}MB) • Video (≤ {{ $maxVideoMb }}MB) • PDF (≤ {{ $maxPdfMb }}MB)
                    </div>
                </div>

                <!-- 6-Item Status Tracker Cards -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2">
                    @foreach($evidenceTypes as $key => $typeInfo)
                        @php $isUploaded = isset($uploadedMap[$key]); @endphp
                        <div class="p-2.5 rounded-xl border text-xs transition {{ $isUploaded ? 'bg-emerald-50/80 border-emerald-200 text-emerald-900' : 'bg-slate-50 border-slate-200 text-slate-600' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-[11px]">Item {{ $typeInfo['number'] }}</span>
                                @if($isUploaded)
                                    <span class="inline-flex items-center space-x-0.5 px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-200 text-emerald-800">
                                        <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        <span>Locked</span>
                                    </span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-semibold bg-amber-100 text-amber-800">
                                        Pending
                                    </span>
                                @endif
                            </div>
                            <div class="font-semibold truncate text-[11px]" title="{{ $typeInfo['name'] }}">
                                {{ $typeInfo['name'] }}
                            </div>
                            <div class="text-[10px] mt-0.5 {{ $isUploaded ? 'text-emerald-700 font-medium' : 'text-slate-400' }}">
                                @if($isUploaded)
                                    ✓ Uploaded (Use Edit to replace)
                                @else
                                    Ready to upload
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Form for Pending Items with Dynamic Validation & Live Preview -->
                <form action="{{ route('batches.evidence.store', $batch) }}" method="POST" enctype="multipart/form-data" class="text-xs pt-1 space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-start">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block font-semibold text-slate-700">Evidence Item Type <span class="text-rose-500">*</span></label>
                                <span x-show="uploadType && rules[uploadType]" 
                                      :class="{
                                          'bg-rose-100 text-rose-700': rules[uploadType]?.category === 'pdf',
                                          'bg-blue-100 text-blue-700': rules[uploadType]?.category === 'image',
                                          'bg-purple-100 text-purple-700': rules[uploadType]?.category === 'video'
                                      }"
                                      class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                                      x-text="rules[uploadType]?.category + ' only'">
                                </span>
                            </div>
                            <select name="evidence_type" x-model="uploadType" @change="onUploadTypeChange()" required 
                                class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none">
                                <option value="">-- Choose Item Type --</option>
                                @foreach($evidenceTypes as $key => $typeInfo)
                                    @php $isUploaded = isset($uploadedMap[$key]); @endphp
                                    @if($isUploaded)
                                        <option value="{{ $key }}" disabled class="text-slate-400 bg-slate-100 font-normal">
                                            🔒 {{ $typeInfo['number'] }}. {{ $typeInfo['name'] }} — [Already Uploaded - Locked: Use Edit Below]
                                        </option>
                                    @else
                                        <option value="{{ $key }}" {{ old('evidence_type') === $key ? 'selected' : '' }} class="text-slate-900 font-medium">
                                            {{ $typeInfo['number'] }}. {{ $typeInfo['name'] }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <p class="text-[10px] text-slate-400 mt-1" x-show="!uploadType">
                                Select an item type to automatically filter file picker to matching formats.
                            </p>
                            <p class="text-[10px] text-slate-600 mt-1 font-medium" x-show="uploadType && rules[uploadType]">
                                <span class="text-slate-400">Required:</span> <span class="text-blue-700 font-semibold" x-text="rules[uploadType]?.desc"></span> 
                                (Max <span x-text="rules[uploadType]?.maxMb"></span> MB)
                            </p>
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">
                                Select File <span class="text-rose-500">*</span>
                            </label>
                            <input type="file" id="upload-file-input" name="file" required
                                :accept="uploadInputAccept"
                                :disabled="!uploadType"
                                @change="handleUploadFileSelect($event)"
                                :class="!uploadType ? 'bg-slate-100 cursor-not-allowed opacity-60' : 'bg-white'"
                                class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none file:mr-3 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-[10px] text-slate-400 mt-1" x-show="!uploadType">
                                🔒 Select evidence item type first to enable file selection.
                            </p>
                            <p class="text-[10px] text-slate-500 mt-1" x-show="uploadType && rules[uploadType]">
                                File picker dialog will filter to allowed <span class="font-semibold text-slate-700" x-text="rules[uploadType]?.category.toUpperCase()"></span> files only.
                            </p>
                        </div>

                        <div class="flex items-end">
                            <button type="submit" 
                                :disabled="!uploadType || !uploadFile || !!uploadError"
                                :class="(!uploadType || !uploadFile || !!uploadError) ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-500/20'"
                                class="w-full py-2.5 px-4 rounded-xl text-white font-semibold transition text-center flex items-center justify-center space-x-1.5">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                <span>Upload File</span>
                            </button>
                        </div>
                    </div>

                    <!-- Client-side Validation Error Banner -->
                    <div x-show="uploadError" x-cloak class="p-3 bg-rose-50 border border-rose-200 rounded-xl flex items-start space-x-2 text-rose-800">
                        <svg class="w-4 h-4 text-rose-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="flex-1 text-xs">
                            <span class="font-bold">File Selection Error:</span>
                            <span x-text="uploadError" class="ml-1"></span>
                        </div>
                        <button type="button" @click="uploadError = ''" class="text-rose-400 hover:text-rose-600 text-xs">✕</button>
                    </div>

                    <!-- Selected File Live Preview Card -->
                    <div x-show="uploadFile && !uploadError" x-cloak class="p-3.5 bg-slate-50 border border-blue-200 rounded-xl flex items-center justify-between gap-4">
                        <div class="flex items-center space-x-3.5 min-w-0">
                            <!-- Visual Thumbnail / Preview -->
                            <template x-if="uploadFileType === 'image' && uploadFilePreview">
                                <img :src="uploadFilePreview" alt="Preview" class="w-14 h-14 object-cover rounded-lg border border-blue-200 shrink-0 bg-white shadow-2xs">
                            </template>
                            <template x-if="uploadFileType === 'video' && uploadFilePreview">
                                <video :src="uploadFilePreview" class="w-20 h-14 object-cover rounded-lg border border-purple-200 shrink-0 bg-black" controls></video>
                            </template>
                            <template x-if="uploadFileType === 'pdf'">
                                <div class="w-14 h-14 rounded-lg bg-rose-100 text-rose-700 flex flex-col items-center justify-center shrink-0 border border-rose-200 shadow-2xs">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-[9px] font-extrabold uppercase mt-0.5">PDF</span>
                                </div>
                            </template>

                            <div class="min-w-0">
                                <div class="flex items-center space-x-2">
                                    <span class="text-[9px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded"
                                          :class="{
                                              'bg-rose-100 text-rose-800 border border-rose-200': uploadFileType === 'pdf',
                                              'bg-blue-100 text-blue-800 border border-blue-200': uploadFileType === 'image',
                                              'bg-purple-100 text-purple-800 border border-purple-200': uploadFileType === 'video'
                                          }"
                                          x-text="uploadFileType.toUpperCase()">
                                    </span>
                                    <span class="text-[11px] text-emerald-700 font-semibold flex items-center space-x-1">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Format & Size Verified</span>
                                    </span>
                                </div>
                                <div class="font-mono text-xs font-semibold text-slate-800 truncate mt-1 max-w-sm" x-text="uploadFileName"></div>
                                <div class="text-[10px] text-slate-500 mt-0.5">
                                    Size: <strong x-text="uploadFileSize" class="text-slate-700 font-mono"></strong>
                                </div>
                            </div>
                        </div>

                        <div class="shrink-0 flex items-center space-x-2">
                            <button type="button" @click="clearUploadFile()" class="px-2.5 py-1 text-xs font-semibold text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition border border-slate-200 flex items-center space-x-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                <span>Remove</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @endif
    @endcan

    <!-- Attached Files Table -->
    <div id="uploaded-files-table" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden scroll-mt-6">
        <div class="px-6 py-4 border-b border-slate-200/80 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                    Current Uploaded Evidence Items
                </h3>
                <p class="text-[11px] text-slate-500 mt-0.5">
                    Uploaded files are locked from direct re-upload. If you need to change or replace any file, click <strong>Edit / Replace</strong>.
                </p>
            </div>
            <span class="text-xs px-2.5 py-1 rounded-full font-bold {{ $batch->uploads->count() >= 6 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                {{ $batch->uploads->count() }}/6 Active Files
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-3.5">Evidence Item</th>
                        <th class="px-6 py-3.5">Original File Name</th>
                        <th class="px-6 py-3.5">Size</th>
                        <th class="px-6 py-3.5">Version</th>
                        <th class="px-6 py-3.5">Uploaded By</th>
                        <th class="px-6 py-3.5">Timestamp</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse($batch->uploads as $upload)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-6 py-4 font-bold text-slate-900">
                            {{ $upload->type_label }}
                        </td>
                        <td class="px-6 py-4 text-slate-600 font-mono text-[11px] truncate max-w-xs">
                            {{ $upload->original_filename }}
                        </td>
                        <td class="px-6 py-4 text-slate-500 whitespace-nowrap">
                            {{ $upload->formatted_size }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">v{{ $upload->version }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $upload->uploader?->name ?? 'User' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-slate-500">
                            {{ $upload->created_at->format('d M Y, h:i A') }}
                        </td>
                        <td class="px-6 py-4 text-right whitespace-nowrap space-x-2">
                            <a href="{{ route('evidence.download', $upload) }}" target="_blank" rel="noopener noreferrer"
                                class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 font-semibold transition">
                                <span>⬇ Download</span>
                                <svg class="w-3 h-3 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            @can('update', $upload)
                            <button type="button"
                                @click="openEdit({{ json_encode($upload) }}, '{{ route('evidence.update', $upload) }}')"
                                class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200/80 font-semibold transition shadow-2xs"
                                title="Edit or replace this evidence file">
                                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                <span>Edit / Replace</span>
                            </button>
                            @endcan
                            @can('delete', $upload)
                            <form action="{{ route('evidence.destroy', $upload) }}" method="POST" class="inline" onsubmit="return confirm('Remove this evidence item?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 font-semibold transition">
                                    Delete
                                </button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                            No evidence items uploaded yet. Please attach all 6 items to complete the pack.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Structured Data Entry / Read-Only View -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Summary Card -->
        @can('manageSummary', $batch)
            <!-- Editable Training Summary Form (Field Operators) -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                        Training Summary Entry
                    </h3>
                    <span class="text-[11px] font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200">
                        Field Form 3
                    </span>
                </div>

                <form action="{{ route('batches.summary.store', $batch) }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Registered</label>
                            <input type="number" name="registered_count" required value="{{ old('registered_count', $batch->summary?->registered_count ?? $batch->registered_participants) }}"
                                class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Present</label>
                            <input type="number" name="present_count" required value="{{ old('present_count', $batch->summary?->present_count ?? $batch->present_participants) }}"
                                class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Feedback Forms</label>
                            <input type="number" name="feedback_forms_received" required value="{{ old('feedback_forms_received', $batch->summary?->feedback_forms_received ?? $batch->present_participants) }}"
                                class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Actual Start Time</label>
                            <input type="time" name="actual_start_time" value="{{ old('actual_start_time', $batch->summary?->actual_start_time ? substr($batch->summary->actual_start_time, 0, 5) : substr($batch->start_time, 0, 5)) }}"
                                class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Actual End Time</label>
                            <input type="time" name="actual_end_time" value="{{ old('actual_end_time', $batch->summary?->actual_end_time ? substr($batch->summary->actual_end_time, 0, 5) : substr($batch->end_time, 0, 5)) }}"
                                class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">What went well (2-3 lines)</label>
                        <textarea name="what_went_well" rows="2" class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">{{ old('what_went_well', $batch->summary?->what_went_well) }}</textarea>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Problems faced (venue, power, projector, etc.)</label>
                        <textarea name="problems_faced" rows="2" class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">{{ old('problems_faced', $batch->summary?->problems_faced) }}</textarea>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Follow-up actions needed</label>
                        <textarea name="follow_up_actions" rows="2" class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">{{ old('follow_up_actions', $batch->summary?->follow_up_actions) }}</textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold transition text-center">
                        Save Summary
                    </button>
                </form>
            </div>
        @else
            <!-- Read-Only Training Summary View (Admin / PMU / OCAC / Reviewers) -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                        Training Summary
                    </h3>
                    <div class="flex items-center space-x-2">
                        <span class="text-[11px] font-semibold text-slate-600 bg-slate-100 px-2.5 py-0.5 rounded-full border border-slate-200">
                            Read-Only
                        </span>
                        @if($batch->summary)
                            <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                                ✓ Submitted
                            </span>
                        @else
                            <span class="text-[11px] font-semibold text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200">
                                ⏳ Pending Entry
                            </span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                        <span class="text-slate-500 block text-[11px] mb-0.5 font-medium">Registered</span>
                        <span class="text-base font-bold text-slate-900">
                            {{ $batch->summary?->registered_count ?? $batch->registered_participants }}
                        </span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                        <span class="text-slate-500 block text-[11px] mb-0.5 font-medium">Present</span>
                        <span class="text-base font-bold text-emerald-600">
                            {{ $batch->summary?->present_count ?? $batch->present_participants }}
                        </span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                        <span class="text-slate-500 block text-[11px] mb-0.5 font-medium">Feedback Forms</span>
                        <span class="text-base font-bold text-blue-600">
                            {{ $batch->summary?->feedback_forms_received ?? '-' }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                        <span class="text-slate-500 block text-[11px] mb-0.5 font-medium">Actual Start Time</span>
                        <span class="text-xs font-bold text-slate-800">
                            {{ $batch->summary?->actual_start_time ? substr($batch->summary->actual_start_time, 0, 5) : ($batch->actual_start_time ? substr($batch->actual_start_time, 0, 5) : 'Not recorded') }}
                        </span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                        <span class="text-slate-500 block text-[11px] mb-0.5 font-medium">Actual End Time</span>
                        <span class="text-xs font-bold text-slate-800">
                            {{ $batch->summary?->actual_end_time ? substr($batch->summary->actual_end_time, 0, 5) : ($batch->actual_end_time ? substr($batch->actual_end_time, 0, 5) : 'Not recorded') }}
                        </span>
                    </div>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="font-semibold text-slate-700 block mb-1">What went well</span>
                        <p class="text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-200/80">
                            {{ $batch->summary?->what_went_well ?: 'No narrative recorded yet.' }}
                        </p>
                    </div>
                    <div>
                        <span class="font-semibold text-slate-700 block mb-1">Problems faced</span>
                        <p class="text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-200/80">
                            {{ $batch->summary?->problems_faced ?: 'No issues reported.' }}
                        </p>
                    </div>
                    <div>
                        <span class="font-semibold text-slate-700 block mb-1">Follow-up actions needed</span>
                        <p class="text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-200/80">
                            {{ $batch->summary?->follow_up_actions ?: 'No follow-up actions recorded.' }}
                        </p>
                    </div>
                </div>

                @if($batch->summary)
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                    <span>Submitted by: <strong class="text-slate-600">{{ $batch->summary->submitted_by_name ?? 'Field Trainer' }}</strong></span>
                    @if($batch->summary->updated_at)
                        <span>{{ $batch->summary->updated_at->format('d M Y, h:i A') }}</span>
                    @endif
                </div>
                @else
                <div class="pt-2 border-t border-slate-100 text-[11px] text-slate-400 text-center">
                    Field training summary narrative pending submission by trainer.
                </div>
                @endif
            </div>
        @endcan

        <!-- Feedback Aggregates Card -->
        @can('manageSummary', $batch)
            <!-- Editable Feedback Form (Field Operators) -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                        Feedback Aggregates Entry
                    </h3>
                    <span class="text-[11px] font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200">
                        Field Form 1
                    </span>
                </div>

                <form action="{{ route('batches.feedback.store', $batch) }}" method="POST" class="space-y-3 text-xs">
                    @csrf
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Q1 Clarity (1-5)</label>
                            <input type="number" step="0.01" min="1" max="5" name="q1_clarity_avg" required value="{{ old('q1_clarity_avg', $batch->feedback?->q1_clarity_avg ?? '4.50') }}"
                                class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Q2 Knowledge (1-5)</label>
                            <input type="number" step="0.01" min="1" max="5" name="q2_knowledge_avg" required value="{{ old('q2_knowledge_avg', $batch->feedback?->q2_knowledge_avg ?? '4.80') }}"
                                class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Q3 Relevance (1-5)</label>
                            <input type="number" step="0.01" min="1" max="5" name="q3_relevance_avg" required value="{{ old('q3_relevance_avg', $batch->feedback?->q3_relevance_avg ?? '4.60') }}"
                                class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Q4 Handbook (1-5)</label>
                            <input type="number" step="0.01" min="1" max="5" name="q4_handbook_avg" required value="{{ old('q4_handbook_avg', $batch->feedback?->q4_handbook_avg ?? '4.70') }}"
                                class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Q5 Overall (1-5)</label>
                            <input type="number" step="0.01" min="1" max="5" name="q5_overall_avg" required value="{{ old('q5_overall_avg', $batch->feedback?->q5_overall_avg ?? '4.75') }}"
                                class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Forms Count</label>
                            <input type="number" min="1" name="total_forms_analyzed" required value="{{ old('total_forms_analyzed', $batch->feedback?->total_forms_analyzed ?? $batch->present_participants) }}"
                                class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Key Qualitative Highlights</label>
                        <textarea name="qualitative_highlights" rows="2" class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">{{ old('qualitative_highlights', $batch->feedback?->qualitative_highlights) }}</textarea>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Improvement Suggestions</label>
                        <textarea name="improvement_suggestions" rows="2" class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none">{{ old('improvement_suggestions', $batch->feedback?->improvement_suggestions) }}</textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold transition text-center">
                        Save Feedback Evaluation
                    </button>
                </form>
            </div>
        @else
            <!-- Read-Only Feedback View (Admin / PMU / OCAC / Reviewers) -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-slate-200">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider">
                        Feedback Evaluation
                    </h3>
                    <div class="flex items-center space-x-2">
                        <span class="text-[11px] font-semibold text-slate-600 bg-slate-100 px-2.5 py-0.5 rounded-full border border-slate-200">
                            Read-Only
                        </span>
                        @if($batch->feedback)
                            <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                                ✓ Submitted
                            </span>
                        @else
                            <span class="text-[11px] font-semibold text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200">
                                ⏳ Pending Entry
                            </span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-center text-xs">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                        <span class="text-slate-500 block text-[11px] mb-1 font-medium">Q1. Clarity</span>
                        <span class="text-base font-extrabold text-blue-600">
                            {{ $batch->feedback ? number_format($batch->feedback->q1_clarity_avg, 2) : '-' }}
                        </span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                        <span class="text-slate-500 block text-[11px] mb-1 font-medium">Q2. Knowledge</span>
                        <span class="text-base font-extrabold text-blue-600">
                            {{ $batch->feedback ? number_format($batch->feedback->q2_knowledge_avg, 2) : '-' }}
                        </span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                        <span class="text-slate-500 block text-[11px] mb-1 font-medium">Q3. Relevance</span>
                        <span class="text-base font-extrabold text-blue-600">
                            {{ $batch->feedback ? number_format($batch->feedback->q3_relevance_avg, 2) : '-' }}
                        </span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                        <span class="text-slate-500 block text-[11px] mb-1 font-medium">Q4. Handbook</span>
                        <span class="text-base font-extrabold text-blue-600">
                            {{ $batch->feedback ? number_format($batch->feedback->q4_handbook_avg, 2) : '-' }}
                        </span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                        <span class="text-slate-500 block text-[11px] mb-1 font-medium">Overall (Q5)</span>
                        <span class="text-base font-extrabold text-emerald-600">
                            {{ $batch->feedback ? '★ ' . number_format($batch->feedback->q5_overall_avg, 2) : '-' }}
                        </span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/80">
                        <span class="text-slate-500 block text-[11px] mb-1 font-medium">Forms Count</span>
                        <span class="text-base font-extrabold text-slate-800">
                            {{ $batch->feedback?->total_forms_analyzed ?? '-' }}
                        </span>
                    </div>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="font-semibold text-slate-700 block mb-1">Key Qualitative Highlights</span>
                        <p class="text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-200/80">
                            {{ $batch->feedback?->qualitative_highlights ?: 'No qualitative highlights recorded yet.' }}
                        </p>
                    </div>
                    <div>
                        <span class="font-semibold text-slate-700 block mb-1">Improvement Suggestions</span>
                        <p class="text-slate-600 bg-slate-50 p-2.5 rounded-xl border border-slate-200/80">
                            {{ $batch->feedback?->improvement_suggestions ?: 'No suggestions recorded yet.' }}
                        </p>
                    </div>
                </div>

                @if(!$batch->feedback)
                <div class="pt-2 border-t border-slate-100 text-[11px] text-slate-400 text-center">
                    Participant feedback aggregate evaluation pending submission by field team.
                </div>
                @endif
            </div>
        @endcan

    </div>

    <!-- Edit Evidence Modal -->
    <div x-show="editModalOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         style="display: none;"
         @keydown.escape.window="editModalOpen = false">

        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200 relative"
             @click.away="editModalOpen = false">

            <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Edit / Replace Evidence Item</h3>
                        <p class="text-[11px] text-slate-500">
                            Editing: <strong class="text-amber-700 font-semibold" x-text="editForm.type_label"></strong>
                        </p>
                    </div>
                </div>
                <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="editForm.actionUrl" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4 text-xs">
                @csrf
                @method('PUT')

                <!-- Current file preview -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block">Current Attached File</span>
                        <span class="font-mono text-xs font-semibold text-slate-800 truncate block max-w-xs" x-text="editForm.original_filename"></span>
                    </div>
                    <span class="text-[11px] font-semibold text-slate-500 font-mono" x-text="editForm.formatted_size"></span>
                </div>

                <!-- Hidden evidence_type to lock to this item -->
                <input type="hidden" name="evidence_type" :value="editForm.evidence_type">

                <!-- Evidence Type Display (Locked) -->
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Evidence Item Type</label>
                    <div class="w-full rounded-xl bg-slate-100 border border-slate-200 p-2.5 text-xs text-slate-800 font-semibold flex items-center justify-between">
                        <span x-text="editForm.type_label"></span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-700">🔒 Locked to this item</span>
                    </div>
                </div>

                <!-- Replace File (Optional) -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block font-semibold text-slate-700">
                            Replace File <span class="text-slate-400 font-normal">(Optional — select new file to replace)</span>
                        </label>
                        <span x-show="editForm.evidence_type && rules[editForm.evidence_type]"
                              :class="{
                                  'bg-rose-100 text-rose-700': rules[editForm.evidence_type]?.category === 'pdf',
                                  'bg-blue-100 text-blue-700': rules[editForm.evidence_type]?.category === 'image',
                                  'bg-purple-100 text-purple-700': rules[editForm.evidence_type]?.category === 'video'
                              }"
                              class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                              x-text="rules[editForm.evidence_type]?.category + ' only'">
                        </span>
                    </div>
                    <input type="file" id="edit-file-input" name="file"
                        :accept="editInputAccept"
                        @change="handleEditFileSelect($event)"
                        class="w-full rounded-xl border border-slate-300 p-2 text-xs focus:border-blue-500 focus:outline-none file:mr-3 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-[11px] text-slate-500 mt-1 font-medium" x-show="editForm.evidence_type && rules[editForm.evidence_type]">
                        Required format: <strong class="text-blue-700" x-text="rules[editForm.evidence_type]?.desc"></strong> 
                        (Max <strong x-text="rules[editForm.evidence_type]?.maxMb"></strong> MB)
                    </p>
                </div>

                <!-- Client-side Error Banner for Edit Modal -->
                <div x-show="editError" x-cloak class="p-3 bg-rose-50 border border-rose-200 rounded-xl flex items-start space-x-2 text-rose-800">
                    <svg class="w-4 h-4 text-rose-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1 text-xs">
                        <span class="font-bold">Invalid File:</span>
                        <span x-text="editError" class="ml-1"></span>
                    </div>
                    <button type="button" @click="editError = ''" class="text-rose-400 hover:text-rose-600 text-xs">✕</button>
                </div>

                <!-- New Replacement File Preview Card -->
                <div x-show="editFile && !editError" x-cloak class="p-3 bg-blue-50/70 border border-blue-200 rounded-xl flex items-center justify-between gap-4">
                    <div class="flex items-center space-x-3 min-w-0">
                        <template x-if="editFileType === 'image' && editFilePreview">
                            <img :src="editFilePreview" alt="Preview" class="w-12 h-12 object-cover rounded-lg border border-blue-200 shrink-0 bg-white shadow-2xs">
                        </template>
                        <template x-if="editFileType === 'video' && editFilePreview">
                            <video :src="editFilePreview" class="w-16 h-12 object-cover rounded-lg border border-purple-200 shrink-0 bg-black" controls></video>
                        </template>
                        <template x-if="editFileType === 'pdf'">
                            <div class="w-12 h-12 rounded-lg bg-rose-100 text-rose-700 flex flex-col items-center justify-center shrink-0 border border-rose-200 shadow-2xs">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span class="text-[8px] font-extrabold uppercase mt-0.5">PDF</span>
                            </div>
                        </template>
                        <div class="min-w-0">
                            <div class="flex items-center space-x-1.5">
                                <span class="text-[9px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-800">
                                    New File Ready
                                </span>
                            </div>
                            <div class="font-mono text-xs font-semibold text-slate-800 truncate mt-0.5 max-w-xs" x-text="editFileName"></div>
                            <div class="text-[10px] text-slate-500 mt-0.5">
                                Size: <strong x-text="editFileSize" class="text-slate-700 font-mono"></strong>
                            </div>
                        </div>
                    </div>
                    <button type="button" @click="clearEditFile()" class="px-2 py-1 text-xs font-semibold text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition border border-slate-200">
                        Clear
                    </button>
                </div>

                <!-- Remarks -->
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Remarks / Notes</label>
                    <textarea name="remarks" x-model="editForm.remarks" rows="2" placeholder="Optional remarks on this evidence item..."
                        class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-blue-500 focus:outline-none"></textarea>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-200">
                    <button type="button" @click="editModalOpen = false"
                        class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-semibold text-xs hover:bg-slate-50 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        :disabled="!!editError"
                        :class="editError ? 'opacity-50 cursor-not-allowed bg-slate-400' : 'bg-blue-600 hover:bg-blue-700 shadow-md shadow-blue-600/20'"
                        class="px-5 py-2 rounded-xl text-white font-semibold text-xs transition flex items-center space-x-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
