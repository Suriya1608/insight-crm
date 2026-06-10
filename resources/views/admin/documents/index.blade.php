@extends('layouts.app')

@section('page_title', 'Document Management')

@push('styles')
<style>
    /* ── Upload Zone ── */
    .upload-drop-zone {
        border: 2px dashed #c7d2fe;
        border-radius: 14px;
        background: linear-gradient(135deg, #f0f1ff 0%, #faf5ff 100%);
        padding: 32px 20px;
        text-align: center;
        cursor: pointer;
        transition: border-color .2s, background .2s;
        position: relative;
    }
    .upload-drop-zone:hover,
    .upload-drop-zone.drag-over {
        border-color: #6366f1;
        background: linear-gradient(135deg, #e0e2ff 0%, #f3e8ff 100%);
    }
    .upload-drop-zone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }
    .upload-drop-icon {
        width: 56px; height: 56px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 12px;
        box-shadow: 0 4px 14px rgba(99,102,241,.3);
    }
    .upload-drop-icon .material-icons { color: #fff; font-size: 26px; }

    /* File chips row */
    .file-type-chips { display: flex; flex-wrap: wrap; gap: 6px; justify-content: center; margin-top: 10px; }
    .file-chip {
        font-size: 10px; font-weight: 600; letter-spacing: .4px;
        padding: 2px 8px; border-radius: 20px;
        background: #e0e7ff; color: #4338ca;
    }
    .file-chip.green  { background: #d1fae5; color: #065f46; }
    .file-chip.orange { background: #ffedd5; color: #9a3412; }
    .file-chip.blue   { background: #dbeafe; color: #1e40af; }
    .file-chip.pink   { background: #fce7f3; color: #9d174d; }
    .file-chip.yellow { background: #fef9c3; color: #854d0e; }

    /* selected file name pill */
    #fileNamePill {
        display: none;
        align-items: center; gap: 6px;
        background: #e0e7ff; border-radius: 8px;
        padding: 6px 12px; margin-top: 12px;
        font-size: 12px; font-weight: 500; color: #3730a3;
    }

    /* ── Stat chips ── */
    .doc-stat {
        display: flex; align-items: center; gap: 10px;
        background: #fff; border: 1px solid #e2e8f0;
        border-radius: 12px; padding: 14px 18px;
    }
    .doc-stat-icon {
        width: 40px; height: 40px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .doc-stat-icon .material-icons { font-size: 20px; color: #fff; }
    .doc-stat-val { font-size: 18px; font-weight: 700; color: #0f172a; line-height: 1; }
    .doc-stat-lbl { font-size: 11px; color: #64748b; margin-top: 2px; }

    /* ── Search ── */
    .search-wrap { position: relative; }
    .search-wrap .material-icons {
        position: absolute; left: 11px; top: 50%; transform: translateY(-50%);
        font-size: 17px; color: #94a3b8; pointer-events: none;
    }
    .search-wrap input { padding-left: 36px; }

    /* ── File type badge ── */
    .ft-badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 8px; border-radius: 6px;
        font-size: 10px; font-weight: 700; letter-spacing: .4px;
        text-transform: uppercase;
    }
    .ft-pdf   { background: #fee2e2; color: #b91c1c; }
    .ft-doc   { background: #dbeafe; color: #1e40af; }
    .ft-xls   { background: #d1fae5; color: #065f46; }
    .ft-ppt   { background: #ffedd5; color: #9a3412; }
    .ft-img   { background: #fae8ff; color: #7e22ce; }
    .ft-other { background: #f1f5f9; color: #475569; }

    /* ── Table tweaks ── */
    .doc-table td { vertical-align: middle; padding-top: 12px; padding-bottom: 12px; }
    .doc-table tbody tr:hover { background: #f8f9ff; }
    .doc-title-text { font-weight: 600; color: #0f172a; font-size: 13px; }
    .doc-filename   { font-size: 11.5px; color: #64748b; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: block; }

    /* avatar chip for uploader */
    .uploader-chip {
        display: inline-flex; align-items: center; gap: 6px;
        background: #f1f5f9; border-radius: 20px;
        padding: 3px 10px 3px 4px; font-size: 12px; color: #334155;
    }
    .uploader-chip .av {
        width: 22px; height: 22px; border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        display: flex; align-items: center; justify-content: center;
        font-size: 10px; font-weight: 700; color: #fff; flex-shrink: 0;
    }

    /* empty state */
    .empty-state { padding: 48px 20px; text-align: center; }
    .empty-state-icon {
        width: 72px; height: 72px; border-radius: 50%;
        background: #f1f5f9; display: flex; align-items: center;
        justify-content: center; margin: 0 auto 16px;
    }
    .empty-state-icon .material-icons { font-size: 34px; color: #cbd5e1; }
</style>
@endpush

@section('content')

    {{-- ── Stat Row ── --}}
    @php
        $totalSize = $documents->sum('file_size');
        $formattedTotal = $totalSize >= 1048576
            ? round($totalSize / 1048576, 1) . ' MB'
            : round($totalSize / 1024, 0) . ' KB';
        $pdfCount   = $documents->filter(fn($d) => str_ends_with(strtolower($d->file_name), '.pdf'))->count();
        $otherCount = $documents->count() - $pdfCount;
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="doc-stat">
                <div class="doc-stat-icon" style="background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                    <span class="material-icons">folder</span>
                </div>
                <div>
                    <div class="doc-stat-val">{{ $documents->count() }}</div>
                    <div class="doc-stat-lbl">Total Documents</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="doc-stat">
                <div class="doc-stat-icon" style="background:linear-gradient(135deg,#10b981,#06b6d4);">
                    <span class="material-icons">storage</span>
                </div>
                <div>
                    <div class="doc-stat-val">{{ $formattedTotal }}</div>
                    <div class="doc-stat-lbl">Total Storage Used</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="doc-stat">
                <div class="doc-stat-icon" style="background:linear-gradient(135deg,#ef4444,#f59e0b);">
                    <span class="material-icons">picture_as_pdf</span>
                </div>
                <div>
                    <div class="doc-stat-val">{{ $pdfCount }}</div>
                    <div class="doc-stat-lbl">PDF Files</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="doc-stat">
                <div class="doc-stat-icon" style="background:linear-gradient(135deg,#f59e0b,#f97316);">
                    <span class="material-icons">insert_drive_file</span>
                </div>
                <div>
                    <div class="doc-stat-val">{{ $otherCount }}</div>
                    <div class="doc-stat-lbl">Other Files</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">

        {{-- ── Upload Panel ── --}}
        <div class="col-lg-4">
            <div class="chart-card h-100">
                <div class="mb-4">
                    <h5 class="fw-700 mb-1" style="color:#0f172a;">Upload Document</h5>
                    <p class="text-muted small mb-0">Share files with your team instantly</p>
                </div>

                <form method="POST" action="{{ route('admin.documents.store') }}" enctype="multipart/form-data" id="uploadForm">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size:13px;">
                            Document Title <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            name="title"
                            class="form-control @error('title') is-invalid @enderror"
                            placeholder="e.g. MBA Brochure 2026"
                            value="{{ old('title') }}"
                            required
                        >
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="font-size:13px;">
                            File <span class="text-danger">*</span>
                        </label>

                        <div class="upload-drop-zone" id="dropZone">
                            <input
                                type="file"
                                name="file"
                                id="fileInput"
                                class="@error('file') is-invalid @enderror"
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png"
                                required
                            >
                            <div class="upload-drop-icon">
                                <span class="material-icons">cloud_upload</span>
                            </div>
                            <div class="fw-semibold" style="font-size:13px;color:#334155;">
                                Drag & drop your file here
                            </div>
                            <div class="text-muted" style="font-size:12px;margin-top:4px;">
                                or <span style="color:#6366f1;font-weight:600;">click to browse</span>
                            </div>
                            <div class="file-type-chips mt-3">
                                <span class="file-chip">PDF</span>
                                <span class="file-chip blue">DOC</span>
                                <span class="file-chip green">XLS</span>
                                <span class="file-chip orange">PPT</span>
                                <span class="file-chip pink">JPG</span>
                                <span class="file-chip yellow">PNG</span>
                            </div>
                            <div style="font-size:11px;color:#94a3b8;margin-top:8px;">Max file size: 20 MB</div>
                        </div>

                        <div id="fileNamePill">
                            <span class="material-icons" style="font-size:14px;">attach_file</span>
                            <span id="fileNameText"></span>
                        </div>

                        @error('file')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-semibold" style="border-radius:10px;padding:10px;">
                        <span class="material-icons me-1" style="font-size:16px;vertical-align:middle;">upload_file</span>
                        Upload Document
                    </button>
                </form>

                {{-- Session flash inside panel --}}
                @if(session('success'))
                    <div class="alert alert-success d-flex align-items-center gap-2 mt-3 mb-0" style="font-size:13px;border-radius:10px;">
                        <span class="material-icons" style="font-size:16px;">check_circle</span>
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger d-flex align-items-center gap-2 mt-3 mb-0" style="font-size:13px;border-radius:10px;">
                        <span class="material-icons" style="font-size:16px;">error</span>
                        {{ session('error') }}
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Document List ── --}}
        <div class="col-lg-8">
            <div class="custom-table" style="border-radius:16px;overflow:hidden;">
                <div class="table-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h5 class="fw-700 mb-0" style="color:#0f172a;">All Documents</h5>
                        <span class="text-muted" style="font-size:12px;">
                            {{ $documents->count() }} {{ Str::plural('file', $documents->count()) }} stored
                        </span>
                    </div>
                    <div class="search-wrap" style="min-width:200px;">
                        <span class="material-icons">search</span>
                        <input
                            type="text"
                            id="docSearch"
                            class="form-control form-control-sm"
                            placeholder="Search documents…"
                            style="border-radius:8px;"
                        >
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table mb-0 doc-table">
                        <thead>
                            <tr>
                                <th style="font-size:11px;text-transform:uppercase;letter-spacing:.6px;">Title</th>
                                <th style="font-size:11px;text-transform:uppercase;letter-spacing:.6px;">Type</th>
                                <th style="font-size:11px;text-transform:uppercase;letter-spacing:.6px;">Size</th>
                                <th style="font-size:11px;text-transform:uppercase;letter-spacing:.6px;">Uploaded By</th>
                                <th style="font-size:11px;text-transform:uppercase;letter-spacing:.6px;">Date</th>
                                <th class="text-end" style="font-size:11px;text-transform:uppercase;letter-spacing:.6px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="docTableBody">
                            @forelse($documents as $doc)
                                @php
                                    $ext = strtolower(pathinfo($doc->file_name, PATHINFO_EXTENSION));
                                    $ftClass = match(true) {
                                        $ext === 'pdf'                       => 'ft-pdf',
                                        in_array($ext, ['doc','docx'])       => 'ft-doc',
                                        in_array($ext, ['xls','xlsx'])       => 'ft-xls',
                                        in_array($ext, ['ppt','pptx'])       => 'ft-ppt',
                                        in_array($ext, ['jpg','jpeg','png']) => 'ft-img',
                                        default                              => 'ft-other',
                                    };
                                    $ftIcon = match(true) {
                                        $ext === 'pdf'                       => 'picture_as_pdf',
                                        in_array($ext, ['doc','docx'])       => 'description',
                                        in_array($ext, ['xls','xlsx'])       => 'table_chart',
                                        in_array($ext, ['ppt','pptx'])       => 'slideshow',
                                        in_array($ext, ['jpg','jpeg','png']) => 'image',
                                        default                              => 'insert_drive_file',
                                    };
                                    $uploaderName = $doc->uploader?->name ?? '—';
                                    $uploaderInitial = strtoupper(substr($uploaderName, 0, 1));
                                @endphp
                                <tr class="doc-row">
                                    <td>
                                        <div class="doc-title-text">{{ $doc->title }}</div>
                                        <span class="doc-filename" title="{{ $doc->file_name }}">{{ $doc->file_name }}</span>
                                    </td>
                                    <td>
                                        <span class="ft-badge {{ $ftClass }}">
                                            <span class="material-icons" style="font-size:11px;">{{ $ftIcon }}</span>
                                            {{ strtoupper($ext) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-secondary fw-normal" style="font-size:11px;border:1px solid #e2e8f0;">
                                            {{ $doc->file_size_formatted }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="uploader-chip">
                                            <div class="av">{{ $uploaderInitial }}</div>
                                            <span>{{ $uploaderName }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-size:12px;color:#334155;font-weight:500;">{{ $doc->created_at->format('d M Y') }}</div>
                                        <div style="font-size:11px;color:#94a3b8;">{{ $doc->created_at->format('h:i A') }}</div>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('documents.download', $doc->id) }}"
                                               class="btn btn-sm"
                                               title="Download"
                                               style="background:#e0e7ff;color:#4338ca;border:none;border-radius:8px;padding:5px 10px;">
                                                <span class="material-icons" style="font-size:14px;vertical-align:middle;">download</span>
                                            </a>
                                            <form method="POST" action="{{ route('admin.documents.destroy', $doc->id) }}"
                                                  onsubmit="return confirm('Delete «{{ $doc->title }}»? This cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm"
                                                        title="Delete"
                                                        style="background:#fee2e2;color:#b91c1c;border:none;border-radius:8px;padding:5px 10px;">
                                                    <span class="material-icons" style="font-size:14px;vertical-align:middle;">delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr id="emptyRow">
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <div class="empty-state-icon">
                                                <span class="material-icons">folder_open</span>
                                            </div>
                                            <div class="fw-semibold" style="color:#334155;margin-bottom:4px;">No documents yet</div>
                                            <div class="text-muted small">Upload your first file using the panel on the left.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- No search results row (hidden by default) --}}
                <div id="noSearchResult" class="empty-state" style="display:none;">
                    <div class="empty-state-icon">
                        <span class="material-icons">search_off</span>
                    </div>
                    <div class="fw-semibold" style="color:#334155;">No results found</div>
                    <div class="text-muted small">Try a different search term.</div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
<script>
(function () {
    /* ── Drag & drop ── */
    const zone  = document.getElementById('dropZone');
    const input = document.getElementById('fileInput');
    const pill  = document.getElementById('fileNamePill');
    const pillText = document.getElementById('fileNameText');

    if (zone && input) {
        ['dragenter','dragover'].forEach(e => zone.addEventListener(e, ev => {
            ev.preventDefault(); zone.classList.add('drag-over');
        }));
        ['dragleave','drop'].forEach(e => zone.addEventListener(e, ev => {
            ev.preventDefault(); zone.classList.remove('drag-over');
            if (e === 'drop' && ev.dataTransfer.files.length) {
                const dt = new DataTransfer();
                dt.items.add(ev.dataTransfer.files[0]);
                input.files = dt.files;
                showPill(ev.dataTransfer.files[0].name);
            }
        }));
        input.addEventListener('change', () => {
            if (input.files[0]) showPill(input.files[0].name);
        });
    }

    function showPill(name) {
        pillText.textContent = name;
        pill.style.display = 'flex';
    }

    /* ── Client-side search ── */
    const searchInput = document.getElementById('docSearch');
    const rows = document.querySelectorAll('#docTableBody .doc-row');
    const noResult = document.getElementById('noSearchResult');

    if (searchInput && rows.length) {
        searchInput.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            let visible = 0;
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const show = !q || text.includes(q);
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            if (noResult) noResult.style.display = visible === 0 ? 'block' : 'none';
        });
    }
})();
</script>
@endpush
