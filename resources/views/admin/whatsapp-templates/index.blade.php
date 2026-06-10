@extends('layouts.app')

@section('page_title', 'WhatsApp Templates')

@php
function waIco($name, $size = 14) {
    $icons = [
        'chat'    => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
        'plus'    => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><line x1="12" x2="12" y1="5" y2="19" stroke-linecap="round"/><line x1="5" x2="19" y1="12" y2="12" stroke-linecap="round"/></svg>',
        'edit'    => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>',
        'trash'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><polyline stroke-linecap="round" stroke-linejoin="round" points="3 6 5 6 21 6"/><path stroke-linecap="round" stroke-linejoin="round" d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>',
        'check'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><polyline stroke-linecap="round" stroke-linejoin="round" points="20 6 9 17 4 12"/></svg>',
        'globe'   => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12" stroke-linecap="round"/><path stroke-linecap="round" d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
        'x'       => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><line x1="18" y1="6" x2="6" y2="18" stroke-linecap="round"/><line x1="6" y1="6" x2="18" y2="18" stroke-linecap="round"/></svg>',
        'eye-off' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23" stroke-linecap="round"/></svg>',
    ];
    if (!isset($icons[$name])) return '';
    return str_replace('<svg ', '<svg width="'.$size.'" height="'.$size.'" ', $icons[$name]);
}
@endphp

@section('content')

@if (session('success'))
<div style="background:#ECFDF5;border:1px solid #A7F3D0;border-radius:10px;padding:10px 16px;margin-bottom:14px;display:flex;align-items:center;gap:8px;font-size:12.5px;color:#065F46;">
    {!! waIco('check', 14) !!}
    {{ session('success') }}
</div>
@endif
@if (session('error'))
<div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:10px;padding:10px 16px;margin-bottom:14px;display:flex;align-items:center;gap:8px;font-size:12.5px;color:#991B1B;">
    {!! waIco('x', 14) !!}
    {{ session('error') }}
</div>
@endif

{{-- ── Page Card ── --}}
<div style="background:#fff;border-radius:14px;border:1px solid #F0F0F0;box-shadow:0 1px 6px rgba(0,0,0,0.06);overflow:hidden;">

    {{-- Card Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #F0F0F0;gap:12px;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:4px;height:26px;background:#FF5C00;border-radius:2px;"></div>
            <div style="width:36px;height:36px;border-radius:10px;background:#FFF7ED;display:flex;align-items:center;justify-content:center;color:#FF5C00;">
                {!! waIco('chat', 18) !!}
            </div>
            <div>
                <div style="font-size:14px;font-weight:700;color:#1D1D1D;">WhatsApp Templates</div>
                <div style="font-size:11.5px;color:#9CA3AF;margin-top:1px;">
                    {{ $templates->count() }} {{ Str::plural('template', $templates->count()) }} · Active templates appear in campaign blasts
                </div>
            </div>
        </div>
        <button onclick="openCreate()"
            style="display:inline-flex;align-items:center;gap:6px;background:#FF5C00;color:#fff;border:none;border-radius:8px;font-weight:600;padding:8px 16px;font-size:12.5px;cursor:pointer;white-space:nowrap;">
            {!! waIco('plus', 14) !!} New Template
        </button>
    </div>

    {{-- ── Summary Row ── --}}
    @php
        $total    = $templates->count();
        $active   = $templates->where('status', 'active')->count();
        $inactive = $templates->where('status', 'inactive')->count();
    @endphp
    <div style="display:flex;gap:0;border-bottom:1px solid #F0F0F0;">
        <div style="flex:1;padding:14px 20px;border-right:1px solid #F0F0F0;">
            <div style="font-size:11px;color:#9CA3AF;margin-bottom:3px;">Total</div>
            <div style="font-size:22px;font-weight:800;color:#1D1D1D;line-height:1;">{{ $total }}</div>
        </div>
        <div style="flex:1;padding:14px 20px;border-right:1px solid #F0F0F0;">
            <div style="font-size:11px;color:#9CA3AF;margin-bottom:3px;">Active</div>
            <div style="font-size:22px;font-weight:800;color:#10B981;line-height:1;">{{ $active }}</div>
        </div>
        <div style="flex:1;padding:14px 20px;">
            <div style="font-size:11px;color:#9CA3AF;margin-bottom:3px;">Inactive</div>
            <div style="font-size:22px;font-weight:800;color:#EF4444;line-height:1;">{{ $inactive }}</div>
        </div>
    </div>

    {{-- ── Table / Empty ── --}}
    @if ($templates->isEmpty())
        <div style="text-align:center;padding:56px 20px;">
            <div style="width:64px;height:64px;border-radius:16px;background:#FFF7ED;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;color:#FF5C00;opacity:.7;">
                {!! waIco('chat', 30) !!}
            </div>
            <div style="font-size:14px;font-weight:700;color:#1D1D1D;margin-bottom:6px;">No templates yet</div>
            <div style="font-size:12.5px;color:#9CA3AF;margin-bottom:18px;">Add your Meta WhatsApp templates so managers can use them in campaign blasts.</div>
            <button onclick="openCreate()"
                style="display:inline-flex;align-items:center;gap:6px;background:#FF5C00;color:#fff;border:none;border-radius:8px;font-weight:600;padding:9px 20px;font-size:13px;cursor:pointer;">
                {!! waIco('plus', 14) !!} Add First Template
            </button>
        </div>
    @else
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:12.5px;">
                <thead>
                    <tr style="background:#FAFAFA;border-bottom:1px solid #F0F0F0;">
                        <th style="padding:10px 20px;font-weight:600;color:#9CA3AF;text-align:left;white-space:nowrap;">#</th>
                        <th style="padding:10px 16px;font-weight:600;color:#9CA3AF;text-align:left;white-space:nowrap;">Template Name</th>
                        <th style="padding:10px 16px;font-weight:600;color:#9CA3AF;text-align:left;white-space:nowrap;">Display Name</th>
                        <th style="padding:10px 16px;font-weight:600;color:#9CA3AF;text-align:left;white-space:nowrap;">Language</th>
                        <th style="padding:10px 16px;font-weight:600;color:#9CA3AF;text-align:left;">Preview Text</th>
                        <th style="padding:10px 16px;font-weight:600;color:#9CA3AF;text-align:left;white-space:nowrap;">Status</th>
                        <th style="padding:10px 16px;font-weight:600;color:#9CA3AF;text-align:left;white-space:nowrap;">Created</th>
                        <th style="padding:10px 20px;font-weight:600;color:#9CA3AF;text-align:right;white-space:nowrap;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($templates as $i => $template)
                        <tr style="border-bottom:1px solid #F9FAFB;transition:background .15s;" onmouseover="this.style.background='#FFFAF7'" onmouseout="this.style.background=''">
                            <td style="padding:12px 20px;color:#9CA3AF;">{{ $i + 1 }}</td>
                            <td style="padding:12px 16px;">
                                <code style="background:#F3F4F6;color:#374151;padding:3px 8px;border-radius:5px;font-size:11.5px;font-family:monospace;">{{ $template->name }}</code>
                            </td>
                            <td style="padding:12px 16px;font-weight:600;color:#1D1D1D;">{{ $template->display_name }}</td>
                            <td style="padding:12px 16px;">
                                <span style="display:inline-flex;align-items:center;gap:5px;background:#F0F9FF;color:#0284C7;padding:3px 9px;border-radius:99px;font-size:11px;font-weight:600;">
                                    {!! waIco('globe', 11) !!} {{ $template->language }}
                                </span>
                            </td>
                            <td style="padding:12px 16px;color:#6B7280;max-width:240px;">
                                {{ $template->preview_text ? Str::limit($template->preview_text, 65, '…') : '—' }}
                            </td>
                            <td style="padding:12px 16px;">
                                <form action="{{ route('admin.whatsapp-templates.toggle-status', $template) }}" method="POST" style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button type="submit" title="Click to toggle"
                                        style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:99px;border:none;cursor:pointer;font-size:11px;font-weight:700;
                                        {{ $template->status === 'active'
                                            ? 'background:#D1FAE5;color:#065F46;'
                                            : 'background:#F3F4F6;color:#6B7280;' }}">
                                        @if($template->status === 'active')
                                            {!! waIco('check', 11) !!} Active
                                        @else
                                            {!! waIco('eye-off', 11) !!} Inactive
                                        @endif
                                    </button>
                                </form>
                            </td>
                            <td style="padding:12px 16px;color:#9CA3AF;white-space:nowrap;">{{ $template->created_at->format('d M Y') }}</td>
                            <td style="padding:12px 20px;text-align:right;white-space:nowrap;">
                                <button
                                    onclick="openEdit({{ $template->id }}, {{ json_encode($template->name) }}, {{ json_encode($template->language) }}, {{ json_encode($template->display_name) }}, {{ json_encode($template->preview_text ?? '') }}, '{{ $template->status }}')"
                                    style="display:inline-flex;align-items:center;gap:4px;background:transparent;border:1px solid #E5E7EB;color:#374151;border-radius:7px;padding:5px 10px;font-size:11.5px;cursor:pointer;margin-right:4px;transition:all .15s;"
                                    onmouseover="this.style.borderColor='#FF5C00';this.style.color='#FF5C00';"
                                    onmouseout="this.style.borderColor='#E5E7EB';this.style.color='#374151';">
                                    {!! waIco('edit', 12) !!} Edit
                                </button>
                                <form action="{{ route('admin.whatsapp-templates.destroy', $template) }}" method="POST" style="display:inline;"
                                    onsubmit="return confirm('Delete template \'{{ addslashes($template->display_name) }}\'?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                        style="display:inline-flex;align-items:center;gap:4px;background:transparent;border:1px solid #E5E7EB;color:#374151;border-radius:7px;padding:5px 10px;font-size:11.5px;cursor:pointer;transition:all .15s;"
                                        onmouseover="this.style.borderColor='#EF4444';this.style.color='#EF4444';"
                                        onmouseout="this.style.borderColor='#E5E7EB';this.style.color='#374151';">
                                        {!! waIco('trash', 12) !!} Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- ── Modal ── --}}
<div id="templateModal" style="display:none;position:fixed;inset:0;z-index:1060;background:rgba(0,0,0,.45);align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:500px;margin:0 16px;box-shadow:0 20px 60px rgba(0,0,0,.2);overflow:hidden;">

        {{-- Modal Header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #F0F0F0;">
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:3px;height:22px;background:#FF5C00;border-radius:2px;"></div>
                <span id="modalTitle" style="font-size:14px;font-weight:700;color:#1D1D1D;">Add Template</span>
            </div>
            <button onclick="closeModal()"
                style="width:28px;height:28px;border-radius:8px;border:1px solid #F0F0F0;background:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#9CA3AF;">
                {!! waIco('x', 14) !!}
            </button>
        </div>

        {{-- Modal Body --}}
        <form id="templateForm" method="POST" action="">
            @csrf
            <span id="formMethod"></span>
            <div style="padding:20px;display:flex;flex-direction:column;gap:16px;">

                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:5px;">
                        Meta Template Name <span style="color:#EF4444;">*</span>
                    </label>
                    <input type="text" name="name" id="field_name"
                        style="width:100%;border:1px solid #E5E7EB;border-radius:8px;padding:8px 12px;font-size:13px;color:#1D1D1D;outline:none;box-sizing:border-box;"
                        placeholder="e.g. welcome_template" required maxlength="100"
                        onfocus="this.style.borderColor='#FF5C00'" onblur="this.style.borderColor='#E5E7EB'">
                    <div style="font-size:11px;color:#9CA3AF;margin-top:4px;">Exact name as registered in your Meta Business Account.</div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:5px;">
                            Language Code <span style="color:#EF4444;">*</span>
                        </label>
                        <input type="text" name="language" id="field_language"
                            style="width:100%;border:1px solid #E5E7EB;border-radius:8px;padding:8px 12px;font-size:13px;color:#1D1D1D;outline:none;box-sizing:border-box;"
                            placeholder="en or en_US" required maxlength="10"
                            onfocus="this.style.borderColor='#FF5C00'" onblur="this.style.borderColor='#E5E7EB'">
                    </div>
                    <div>
                        <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:5px;">
                            Status <span style="color:#EF4444;">*</span>
                        </label>
                        <select name="status" id="field_status" required
                            style="width:100%;border:1px solid #E5E7EB;border-radius:8px;padding:8px 12px;font-size:13px;color:#1D1D1D;outline:none;box-sizing:border-box;background:#fff;"
                            onfocus="this.style.borderColor='#FF5C00'" onblur="this.style.borderColor='#E5E7EB'">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:5px;">
                        Display Name <span style="color:#EF4444;">*</span>
                    </label>
                    <input type="text" name="display_name" id="field_display_name"
                        style="width:100%;border:1px solid #E5E7EB;border-radius:8px;padding:8px 12px;font-size:13px;color:#1D1D1D;outline:none;box-sizing:border-box;"
                        placeholder="e.g. Welcome Message" required maxlength="150"
                        onfocus="this.style.borderColor='#FF5C00'" onblur="this.style.borderColor='#E5E7EB'">
                </div>

                <div>
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:5px;">Preview Text</label>
                    <textarea name="preview_text" id="field_preview_text" rows="3" maxlength="500"
                        style="width:100%;border:1px solid #E5E7EB;border-radius:8px;padding:8px 12px;font-size:13px;color:#1D1D1D;outline:none;box-sizing:border-box;resize:vertical;"
                        placeholder="Short preview of the template body…"
                        onfocus="this.style.borderColor='#FF5C00'" onblur="this.style.borderColor='#E5E7EB'"></textarea>
                </div>

            </div>

            {{-- Modal Footer --}}
            <div style="display:flex;justify-content:flex-end;gap:8px;padding:14px 20px;border-top:1px solid #F0F0F0;">
                <button type="button" onclick="closeModal()"
                    style="padding:8px 18px;border:1px solid #E5E7EB;border-radius:8px;background:#fff;color:#374151;font-size:12.5px;font-weight:600;cursor:pointer;">
                    Cancel
                </button>
                <button type="submit" id="submitBtn"
                    style="padding:8px 20px;border:none;border-radius:8px;background:#FF5C00;color:#fff;font-size:12.5px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                    {!! waIco('check', 13) !!} Save Template
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    var modal = document.getElementById('templateModal');

    function openCreate() {
        document.getElementById('modalTitle').textContent   = 'Add Template';
        document.getElementById('submitBtn').innerHTML      = `{!! waIco('plus', 13) !!} Add Template`;
        document.getElementById('templateForm').action      = '{{ route('admin.whatsapp-templates.store') }}';
        document.getElementById('formMethod').innerHTML     = '';
        document.getElementById('field_name').value        = '';
        document.getElementById('field_language').value    = 'en';
        document.getElementById('field_display_name').value= '';
        document.getElementById('field_preview_text').value= '';
        document.getElementById('field_status').value      = 'active';
        modal.style.display = 'flex';
    }

    function openEdit(id, name, language, displayName, previewText, status) {
        document.getElementById('modalTitle').textContent   = 'Edit Template';
        document.getElementById('submitBtn').innerHTML      = `{!! waIco('check', 13) !!} Update Template`;
        document.getElementById('templateForm').action     = '/admin/whatsapp-templates/' + id;
        document.getElementById('formMethod').innerHTML    = '<input type="hidden" name="_method" value="PUT">';
        document.getElementById('field_name').value        = name;
        document.getElementById('field_language').value   = language;
        document.getElementById('field_display_name').value = displayName;
        document.getElementById('field_preview_text').value = previewText;
        document.getElementById('field_status').value     = status;
        modal.style.display = 'flex';
    }

    function closeModal() {
        modal.style.display = 'none';
    }

    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });
</script>
@endpush
