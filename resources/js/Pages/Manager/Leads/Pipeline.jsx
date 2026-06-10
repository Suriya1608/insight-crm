import { Head, Link, router } from '@inertiajs/react';
import { useState, useEffect, useRef } from 'react';
import {
    LuLayoutList, LuPlus, LuDownload, LuFilter, LuRotateCcw,
    LuPhone, LuCalendar, LuExternalLink, LuUsers, LuUser,
    LuTrendingUp, LuRefreshCw, LuX, LuCheck, LuUserCheck,
    LuStar, LuChevronDown,
} from 'react-icons/lu';
import { MdOutlinePhoneInTalk } from 'react-icons/md';

const OR = '#FF5C00', DK = '#1D1D1D', WH = '#FEFEFE', MUT = '#9CA3AF', BOR = '#F0F0F0', BDY = '#374151';

const STATUS_CONFIG = {
    new:            { label: 'New',           color: '#3B82F6', bg: '#EFF6FF', light: '#BFDBFE', icon: <LuStar size={14}/> },
    assigned:       { label: 'Assigned',      color: OR,        bg: '#FFF7ED', light: '#FED7AA', icon: <LuUserCheck size={14}/> },
    contacted:      { label: 'Contacted',     color: '#06B6D4', bg: '#ECFEFF', light: '#A5F3FC', icon: <MdOutlinePhoneInTalk size={14}/> },
    interested:     { label: 'Interested',    color: '#10B981', bg: '#ECFDF5', light: '#6EE7B7', icon: <LuTrendingUp size={14}/> },
    follow_up:      { label: 'Follow Up',     color: '#8B5CF6', bg: '#FAF5FF', light: '#C4B5FD', icon: <LuRefreshCw size={14}/> },
    not_interested: { label: 'Not Interested',color: '#EF4444', bg: '#FEF2F2', light: '#FCA5A5', icon: <LuX size={14}/> },
    converted:      { label: 'Converted',     color: '#059669', bg: '#D1FAE5', light: '#6EE7B7', icon: <LuCheck size={14}/> },
};
const STATUSES = Object.keys(STATUS_CONFIG);

const AVATARS = [OR, '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#06B6D4', '#EC4899', '#3B82F6'];
const avatarBg = n => AVATARS[(n?.charCodeAt(0) ?? 0) % AVATARS.length];

function AgingBadge({ days }) {
    if (days >= 6) return <span style={{ fontSize: 9, fontWeight: 700, padding: '1px 7px', borderRadius: 20, background: '#FEF2F2', color: '#DC2626' }}>{days}d old</span>;
    if (days >= 3) return <span style={{ fontSize: 9, fontWeight: 700, padding: '1px 7px', borderRadius: 20, background: '#FFFBEB', color: '#D97706' }}>{days}d</span>;
    return null;
}

function fmtDate(d) {
    if (!d) return null;
    return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short' });
}

function Toast({ message, type }) {
    if (!message) return null;
    return (
        <div style={{
            position: 'fixed', bottom: 28, right: 28, zIndex: 10000,
            padding: '12px 20px', borderRadius: 12, fontSize: 12.5, fontWeight: 600,
            color: '#fff', boxShadow: '0 8px 28px rgba(0,0,0,.18)',
            background: type === 'success' ? '#10B981' : '#EF4444',
            display: 'flex', alignItems: 'center', gap: 8,
            pointerEvents: 'none', fontFamily: 'Poppins,sans-serif',
        }}>
            {type === 'success' ? <LuCheck size={14}/> : <LuX size={14}/>} {message}
        </div>
    );
}

// ─── Single Kanban card ───────────────────────────────────────────────────────
function KanbanCard({ lead }) {
    const bg = avatarBg(lead.name);
    return (
        <div className="kanban-card pl-card" data-id={lead.encrypted_id}>
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 7 }}>
                <code style={{ fontSize: 9.5, fontWeight: 700, color: MUT, background: '#F3F4F6', padding: '2px 7px', borderRadius: 5 }}>
                    {lead.lead_code}
                </code>
                <AgingBadge days={lead.days_aged}/>
            </div>
            <div style={{ display: 'flex', gap: 8, alignItems: 'flex-start', marginBottom: 8 }}>
                <div style={{
                    width: 30, height: 30, borderRadius: 8, flexShrink: 0,
                    background: `linear-gradient(135deg,${bg},${bg}bb)`,
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                    color: '#fff', fontSize: 12, fontWeight: 800,
                }}>
                    {(lead.name || '?')[0].toUpperCase()}
                </div>
                <div style={{ flex: 1, minWidth: 0 }}>
                    <div style={{ fontSize: 12, fontWeight: 700, color: DK, lineHeight: 1.25, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                        {lead.name}
                        {lead.is_duplicate && (
                            <span style={{ fontSize: 8, background: '#FFF7ED', color: '#EA580C', border: '1px solid #FED7AA', padding: '1px 5px', borderRadius: 4, fontWeight: 700, verticalAlign: 'middle', marginLeft: 4 }}>DUP</span>
                        )}
                    </div>
                    {lead.phone && (
                        <div style={{ display: 'flex', alignItems: 'center', gap: 3, fontSize: 10.5, color: MUT, marginTop: 2 }}>
                            <LuPhone size={10} style={{ flexShrink: 0 }}/>{lead.phone}
                        </div>
                    )}
                </div>
            </div>
            {lead.course && (
                <div style={{ fontSize: 10, color: BDY, marginBottom: 6, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap', background: '#F9FAFB', padding: '2px 7px', borderRadius: 5 }}>
                    {lead.course}
                </div>
            )}
            <div style={{ display: 'flex', alignItems: 'center', gap: 4, fontSize: 10.5, color: MUT, marginBottom: 6 }}>
                <LuUser size={10} style={{ flexShrink: 0 }}/>
                <span data-assigned style={{ overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                    {lead.assigned_user ?? 'Unassigned'}
                </span>
            </div>
            {lead.next_followup && (
                <div style={{ display: 'inline-flex', alignItems: 'center', gap: 4, fontSize: 10, color: OR, fontWeight: 600, marginBottom: 7, background: '#FFF7ED', padding: '2px 7px', borderRadius: 5 }}>
                    <LuCalendar size={9} style={{ flexShrink: 0 }}/>
                    {fmtDate(lead.next_followup)}
                </div>
            )}
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', paddingTop: 7, borderTop: `1px solid ${BOR}` }}>
                <span style={{ fontSize: 9, color: MUT }}>{lead.created_at}</span>
                <Link href={`/manager/leads/${lead.encrypted_id}`} className="pl-view-btn" onClick={e => e.stopPropagation()}>
                    View <LuExternalLink size={9}/>
                </Link>
            </div>
        </div>
    );
}

// ─── Kanban column ────────────────────────────────────────────────────────────
function KanbanColumn({ statusKey, cfg, initialLeads, total, filters, urls, onDrop, telecallers }) {
    const [leads,   setLeads]   = useState(initialLeads);
    const [count,   setCount]   = useState(total);
    const [loaded,  setLoaded]  = useState(initialLeads.length);
    const [hasMore, setHasMore] = useState(total > initialLeads.length);
    const [loading, setLoading] = useState(false);
    const bodyRef = useRef(null);

    // expose imperative API for drag-drop parent
    const api = useRef({ leads, setLeads, count, setCount });
    useEffect(() => { api.current = { leads, setLeads, count, setCount }; }, [leads, count]);

    // Register this column's API with parent
    useEffect(() => { onDrop(statusKey, api); }, []);

    async function loadMore() {
        setLoading(true);
        try {
            const params = new URLSearchParams({ status: statusKey, offset: loaded, ...filters });
            const res    = await fetch(`${urls.pipeline_more}?${params}`, { headers: { Accept: 'application/json' } });
            const data   = await res.json();
            setLeads(prev => [...prev, ...data.leads]);
            setLoaded(data.loaded);
            setHasMore(data.has_more);
        } catch (_) {}
        setLoading(false);
    }

    return (
        <div className="kanban-column pl-col" data-status={statusKey}>
            {/* Tinted header */}
            <div style={{
                background: cfg.bg, borderRadius: '11px 11px 0 0',
                padding: '11px 13px', borderBottom: `1px solid ${cfg.light}`,
            }}>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 7 }}>
                        <div style={{
                            width: 26, height: 26, borderRadius: 7,
                            background: `${cfg.color}18`,
                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                            color: cfg.color, flexShrink: 0,
                        }}>
                            {cfg.icon}
                        </div>
                        <div>
                            <div style={{ fontSize: 12, fontWeight: 700, color: DK }}>{cfg.label}</div>
                            <div style={{ fontSize: 9.5, color: `${cfg.color}99` }}>
                                {count} lead{count !== 1 ? 's' : ''}
                            </div>
                        </div>
                    </div>
                    <span style={{
                        background: count > 0 ? cfg.color : '#E5E7EB',
                        color: count > 0 ? '#fff' : MUT,
                        fontSize: 11, fontWeight: 800, padding: '2px 8px',
                        borderRadius: 20, minWidth: 24, textAlign: 'center',
                        transition: 'all .2s',
                    }}>
                        {count}
                    </span>
                </div>
            </div>

            {/* Cards body */}
            <div ref={bodyRef} className="kanban-column-body pl-col-body">
                {leads.length === 0 && (
                    <div style={{ textAlign: 'center', padding: '22px 10px' }}>
                        <div style={{
                            width: 34, height: 34, borderRadius: 9, background: cfg.bg,
                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                            margin: '0 auto 7px', color: `${cfg.color}77`,
                        }}>
                            {cfg.icon}
                        </div>
                        <div style={{ fontSize: 11, color: MUT }}>No leads</div>
                        <div style={{ fontSize: 10, color: '#D1D5DB', marginTop: 1 }}>Drop here</div>
                    </div>
                )}
                {leads.map(lead => <KanbanCard key={lead.id} lead={lead}/>)}
            </div>

            {/* Load more */}
            {hasMore && (
                <div style={{ padding: '8px 10px 10px' }}>
                    <div style={{ fontSize: 10, color: MUT, textAlign: 'center', marginBottom: 6 }}>
                        Showing {loaded} of {count}
                    </div>
                    <button onClick={loadMore} disabled={loading} className="pl-load-more-btn">
                        {loading
                            ? <><LuRefreshCw size={12}/> Loading…</>
                            : <><LuChevronDown size={12}/> Load More</>
                        }
                    </button>
                </div>
            )}
        </div>
    );
}

// ─── Main Pipeline ────────────────────────────────────────────────────────────
export default function Pipeline({ columns, columnTotals, telecallers, filters, urls }) {
    const [form, setForm]           = useState({ search: filters?.search ?? '', telecaller: filters?.telecaller ?? '', date_range: filters?.date_range ?? '' });
    const [toast, setToast]         = useState(null);
    const [pendingDrag, setPending] = useState(null);
    const [overlay, setOverlay]     = useState(false);
    const colApis = useRef({});
    const toastTimer = useRef(null);

    function showToast(msg, type) {
        setToast({ msg, type });
        clearTimeout(toastTimer.current);
        toastTimer.current = setTimeout(() => setToast(null), 3200);
    }

    function applyFilter(e) {
        e.preventDefault();
        const p = {};
        if (form.search)     p.search     = form.search;
        if (form.telecaller) p.telecaller = form.telecaller;
        if (form.date_range) p.date_range = form.date_range;
        router.get('/manager/leads/pipeline', p, { preserveState: false });
    }

    function resetFilter() {
        setForm({ search: '', telecaller: '', date_range: '' });
        router.get('/manager/leads/pipeline', {}, { preserveState: false });
    }

    // Register column API refs
    function registerColumn(statusKey, apiRef) {
        colApis.current[statusKey] = apiRef;
    }

    // Send status update to backend
    async function sendStatusUpdate(leadEncId, newStatus, oldStatus, telecallerId) {
        setOverlay(true);
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const body = { lead_id: leadEncId, status: newStatus };
        if (telecallerId) body.telecaller_id = telecallerId;
        try {
            const res  = await fetch(urls.pipeline_status, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify(body) });
            const data = await res.json();
            if (data.success) {
                showToast(`Moved to "${newStatus.replace('_', ' ')}"`, 'success');
                if (data.telecaller_name) {
                    const api = colApis.current[newStatus]?.current;
                    if (api) {
                        api.setLeads(prev => prev.map(l => l.encrypted_id === leadEncId ? { ...l, assigned_user: data.telecaller_name } : l));
                    }
                }
            } else {
                revertMove(leadEncId, newStatus, oldStatus);
                showToast(data.message || 'Failed to update status.', 'error');
            }
        } catch (_) {
            revertMove(leadEncId, newStatus, oldStatus);
            showToast('Network error — status not saved.', 'error');
        }
        setOverlay(false);
    }

    function revertMove(leadEncId, fromStatus, toStatus) {
        const fromApi = colApis.current[fromStatus]?.current;
        const toApi   = colApis.current[toStatus]?.current;
        if (!fromApi || !toApi) return;
        const lead = fromApi.leads.find(l => l.encrypted_id === leadEncId);
        if (!lead) return;
        fromApi.setLeads(prev => prev.filter(l => l.encrypted_id !== leadEncId));
        fromApi.setCount(c => Math.max(0, c - 1));
        toApi.setLeads(prev => [lead, ...prev]);
        toApi.setCount(c => c + 1);
    }

    // SortableJS initialisation
    useEffect(() => {
        let Sortable = window.Sortable;
        function initSortable(S) {
            document.querySelectorAll('.kanban-column-body').forEach(colBody => {
                S.create(colBody, {
                    group: 'leads-pipeline', animation: 160,
                    ghostClass: 'kanban-ghost', dragClass: 'kanban-dragging', handle: '.kanban-card',
                    onEnd(evt) {
                        const card      = evt.item;
                        const newStatus = evt.to.closest('.kanban-column')?.dataset.status;
                        const oldStatus = evt.from.closest('.kanban-column')?.dataset.status;
                        if (!newStatus || !oldStatus || newStatus === oldStatus) return;

                        const leadEncId = card.dataset.id;

                        // Update React state for both columns
                        const fromApi = colApis.current[oldStatus]?.current;
                        const toApi   = colApis.current[newStatus]?.current;
                        if (fromApi && toApi) {
                            const lead = fromApi.leads.find(l => l.encrypted_id === leadEncId);
                            if (lead) {
                                fromApi.setLeads(prev => prev.filter(l => l.encrypted_id !== leadEncId));
                                fromApi.setCount(c => Math.max(0, c - 1));
                                toApi.setLeads(prev => [{ ...lead, status: newStatus }, ...prev]);
                                toApi.setCount(c => c + 1);
                            }
                        }

                        if (newStatus === 'assigned') {
                            setPending({ leadEncId, newStatus, oldStatus });
                            new window.bootstrap.Modal(document.getElementById('assignTelecallerModal')).show();
                        } else {
                            sendStatusUpdate(leadEncId, newStatus, oldStatus, null);
                        }
                    },
                });
            });
        }

        if (Sortable) { initSortable(Sortable); return; }
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js';
        s.onload = () => initSortable(window.Sortable);
        document.head.appendChild(s);
    }, []);

    const totalLeads = STATUSES.reduce((s, k) => s + (columnTotals[k] ?? 0), 0);

    return (
        <>
            <Head title="Lead Pipeline"/>
            <style>{`
                @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap');
                .pl-pg, .pl-pg div, .pl-pg span:not([class*="material"]),
                .pl-pg p, .pl-pg label, .pl-pg button, .pl-pg input,
                .pl-pg select, .pl-pg a, .pl-pg small, .pl-pg code {
                    font-family: 'Poppins', sans-serif !important;
                    box-sizing: border-box;
                }
                .pl-pg { display: flex; flex-direction: column; gap: 14px; }

                /* ── Page header ── */
                .pl-header {
                    background: ${WH}; border-radius: 14px; border: 1px solid ${BOR};
                    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
                    padding: 14px 20px;
                    display: flex; align-items: center; justify-content: space-between;
                    flex-wrap: wrap; gap: 10px;
                }

                /* ── Two-column layout ── */
                .pl-layout {
                    display: grid;
                    grid-template-columns: 240px 1fr;
                    gap: 14px;
                    align-items: start;
                }
                @media(max-width: 860px) { .pl-layout { grid-template-columns: 1fr; } }

                /* ── Left filter panel ── */
                .pl-left {
                    background: ${WH}; border-radius: 14px; border: 1px solid ${BOR};
                    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
                    overflow: hidden;
                }
                .pl-left-head {
                    padding: 13px 16px 11px; border-bottom: 1px solid ${BOR};
                    background: linear-gradient(135deg, #FAFBFC, ${WH});
                    display: flex; align-items: center; gap: 10px;
                }
                .pl-filter-body { padding: 14px 16px; display: flex; flex-direction: column; gap: 12px; }
                .pl-lbl { font-size: 9.5px; font-weight: 700; color: ${MUT}; text-transform: uppercase; letter-spacing: .5px; display: block; margin-bottom: 4px; }
                .pl-fi {
                    width: 100%; height: 34px; border-radius: 8px; border: 1px solid #E5E7EB;
                    font-size: 12px; color: ${DK}; background: #FAFBFC; padding: 0 10px; outline: none;
                    transition: border-color .15s, box-shadow .15s;
                    appearance: none; -webkit-appearance: none;
                }
                .pl-fi:focus { border-color: ${OR}; box-shadow: 0 0 0 3px rgba(255,92,0,0.09); background: #fff; }

                /* ── Filter action buttons ── */
                .pl-apply-btn {
                    width: 100%; height: 36px; background: ${OR}; color: #fff; border: none;
                    border-radius: 8px; font-size: 12.5px; font-weight: 600; cursor: pointer;
                    display: flex; align-items: center; justify-content: center; gap: 6px;
                    transition: background .15s;
                }
                .pl-apply-btn:hover { background: #e05200; }
                .pl-reset-btn {
                    width: 100%; height: 34px; background: ${WH}; color: ${MUT}; border: 1px solid #E5E7EB;
                    border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer;
                    display: flex; align-items: center; justify-content: center; gap: 5px;
                    transition: all .15s;
                }
                .pl-reset-btn:hover { border-color: ${DK}; color: ${DK}; }

                .pl-divider { height: 1px; background: ${BOR}; margin: 4px 0; }

                /* ── Status summary list ── */
                .pl-status-list { display: flex; flex-direction: column; gap: 6px; }
                .pl-status-row-item {
                    display: flex; align-items: center; gap: 8px;
                    padding: 7px 10px; border-radius: 9px; cursor: default;
                    transition: background .12s;
                }
                .pl-status-row-item:hover { background: #FAFBFC; }

                /* ── Toolbar buttons ── */
                .pl-tool-btn {
                    display: inline-flex; align-items: center; gap: 6px;
                    padding: 7px 15px; border-radius: 9px; font-size: 12.5px; font-weight: 600;
                    text-decoration: none; cursor: pointer; transition: all .15s;
                    border: none;
                }
                .pl-tool-btn-dark { background: ${DK}; color: #fff; }
                .pl-tool-btn-dark:hover { background: ${OR}; color: #fff; }
                .pl-tool-btn-outline { background: ${WH}; color: ${BDY}; border: 1px solid ${BOR}; }
                .pl-tool-btn-outline:hover { border-color: ${OR}; color: ${OR}; }
                .pl-tool-btn-orange { background: ${OR}; color: #fff; }
                .pl-tool-btn-orange:hover { background: #e05200; color: #fff; }

                /* ── Kanban board ── */
                .pl-board {
                    display: grid;
                    grid-template-columns: repeat(3, 1fr);
                    gap: 12px;
                }
                @media(max-width: 1200px) { .pl-board { grid-template-columns: repeat(2, 1fr); } }
                @media(max-width: 700px)  { .pl-board { grid-template-columns: 1fr; } }

                /* ── Column ── */
                .pl-col {
                    background: ${WH}; border-radius: 11px;
                    border: 1px solid ${BOR};
                    display: flex; flex-direction: column;
                    transition: box-shadow .2s;
                }
                .pl-col-body {
                    overflow-y: auto; max-height: 380px; min-height: 70px;
                    padding: 8px; display: flex; flex-direction: column; gap: 7px;
                    transition: background .15s; border-radius: 0 0 11px 11px;
                }
                .pl-col-body::-webkit-scrollbar { width: 4px; }
                .pl-col-body::-webkit-scrollbar-track { background: #F0F2F5; border-radius: 4px; margin: 3px; }
                .pl-col-body::-webkit-scrollbar-thumb { background: #D1D5DB; border-radius: 4px; }
                .pl-col-body::-webkit-scrollbar-thumb:hover { background: #9CA3AF; }

                /* ── Lead / Kanban card ── */
                .pl-card {
                    background: ${WH}; border-radius: 9px; padding: 11px;
                    cursor: grab; user-select: none;
                    box-shadow: 0 1px 3px rgba(0,0,0,0.07);
                    border: 1px solid ${BOR};
                    transition: box-shadow .18s, transform .18s;
                    display: flex; flex-direction: column;
                }
                .pl-card:hover { box-shadow: 0 5px 18px rgba(0,0,0,0.10); transform: translateY(-2px); }
                .pl-card:active { cursor: grabbing; transform: scale(0.98); }

                /* ── View button ── */
                .pl-view-btn {
                    display: inline-flex; align-items: center; gap: 3px;
                    font-size: 10.5px; font-weight: 600; padding: 2px 8px;
                    background: #F3F4F6; color: ${BDY}; border-radius: 6px;
                    text-decoration: none; transition: background .15s, color .15s;
                }
                .pl-view-btn:hover { background: ${OR}; color: #fff; }

                /* ── Load more button ── */
                .pl-load-more-btn {
                    width: 100%; border: 1px dashed #D1D5DB; background: transparent;
                    color: ${MUT}; font-size: 11.5px; font-weight: 600; padding: 8px;
                    border-radius: 8px; cursor: pointer; display: flex;
                    align-items: center; justify-content: center; gap: 5px;
                    transition: all .15s;
                }
                .pl-load-more-btn:hover:not(:disabled) { border-color: ${OR}; color: ${OR}; }
                .pl-load-more-btn:disabled { opacity: .6; cursor: not-allowed; }

                /* ── SortableJS drag classes ── */
                .kanban-ghost    { opacity: .4; background: #FFF7ED !important; border: 2px dashed ${OR} !important; }
                .kanban-dragging { box-shadow: 0 12px 32px rgba(0,0,0,.18) !important; transform: rotate(1.5deg) !important; z-index: 9999; cursor: grabbing !important; }

                /* ── Scrollbars ── */
                .pl-col-body::-webkit-scrollbar { width: 4px; }
                .pl-col-body::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 4px; }

                /* ── Modal overrides ── */
                .pl-modal-header {
                    padding: 20px 24px 16px; border-bottom: 1px solid ${BOR};
                    display: flex; align-items: center; gap: 10px;
                }
                .pl-modal-confirm-btn {
                    display: inline-flex; align-items: center; gap: 6px;
                    background: ${OR}; color: #fff; border: none;
                    padding: 8px 20px; border-radius: 8px; font-size: 12.5px; font-weight: 600;
                    cursor: pointer; transition: background .15s;
                }
                .pl-modal-confirm-btn:hover { background: #e05200; }
                .pl-modal-cancel-btn {
                    background: ${WH}; color: ${BDY}; border: 1px solid ${BOR};
                    padding: 8px 20px; border-radius: 8px; font-size: 12.5px; font-weight: 600;
                    cursor: pointer; transition: all .15s;
                }
                .pl-modal-cancel-btn:hover { border-color: ${DK}; color: ${DK}; }
            `}</style>

            <div className="pl-pg">

                {/* ── Page header / toolbar ── */}
                <div className="pl-header">
                    <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                        <div style={{ width: 3, height: 22, borderRadius: 2, background: OR, flexShrink: 0 }}/>
                        <span style={{ fontSize: 16, fontWeight: 800, color: DK }}>Lead Pipeline</span>
                        <span style={{ background: '#FFF7ED', color: OR, border: '1px solid #FED7AA', fontSize: 11, fontWeight: 700, padding: '3px 11px', borderRadius: 20 }}>
                            {totalLeads} leads
                        </span>
                    </div>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 8, flexWrap: 'wrap' }}>
                        <Link href="/manager/leads" className="pl-tool-btn pl-tool-btn-dark">
                            <LuLayoutList size={14}/> List View
                        </Link>
                        <Link href="/manager/leads/create" className="pl-tool-btn pl-tool-btn-orange">
                            <LuPlus size={14}/> Add Lead
                        </Link>
                        <a href="/manager/leads/export" className="pl-tool-btn pl-tool-btn-outline">
                            <LuDownload size={14}/> Export
                        </a>
                    </div>
                </div>

                {/* ── Two-column: filter left + board right ── */}
                <div className="pl-layout">

                    {/* ── LEFT: filter panel + status summary ── */}
                    <div className="pl-left">
                        {/* Filter header */}
                        <div className="pl-left-head">
                            <div style={{ width: 3, height: 22, borderRadius: 2, background: OR, flexShrink: 0 }}/>
                            <div style={{ width: 30, height: 30, borderRadius: 8, background: '#FFF7ED', display: 'flex', alignItems: 'center', justifyContent: 'center', color: OR, flexShrink: 0 }}>
                                <LuFilter size={15}/>
                            </div>
                            <div>
                                <div style={{ fontSize: 13, fontWeight: 700, color: DK }}>Filter</div>
                                <div style={{ fontSize: 10.5, color: MUT, marginTop: 1 }}>Refine your leads</div>
                            </div>
                        </div>

                        {/* Filter inputs */}
                        <div className="pl-filter-body">
                            <div>
                                <label className="pl-lbl">Date Range</label>
                                <select className="pl-fi" value={form.date_range} onChange={e => setForm({ ...form, date_range: e.target.value })}>
                                    <option value="">All Time</option>
                                    <option value="today">Today</option>
                                    <option value="7">Last 7 Days</option>
                                    <option value="30">Last 30 Days</option>
                                </select>
                            </div>
                            <div>
                                <label className="pl-lbl">Telecaller</label>
                                <select className="pl-fi" value={form.telecaller} onChange={e => setForm({ ...form, telecaller: e.target.value })}>
                                    <option value="">All Telecallers</option>
                                    {telecallers.map(t => <option key={t.id} value={t.id}>{t.name}</option>)}
                                </select>
                            </div>
                            <div>
                                <label className="pl-lbl">Search</label>
                                <input type="text" className="pl-fi" placeholder="Name / Phone / Code"
                                    value={form.search} onChange={e => setForm({ ...form, search: e.target.value })}
                                    onKeyDown={e => { if (e.key === 'Enter') applyFilter(e); }}/>
                            </div>
                            <button onClick={applyFilter} className="pl-apply-btn">
                                <LuFilter size={13}/> Apply Filters
                            </button>
                            <button onClick={resetFilter} className="pl-reset-btn">
                                <LuRotateCcw size={12}/> Reset
                            </button>

                            <div className="pl-divider"/>

                            {/* Status summary */}
                            <div>
                                <div style={{ fontSize: 9.5, fontWeight: 700, color: MUT, textTransform: 'uppercase', letterSpacing: '.5px', marginBottom: 8 }}>
                                    Pipeline Status
                                </div>
                                <div className="pl-status-list">
                                    {STATUSES.map(key => {
                                        const cfg   = STATUS_CONFIG[key];
                                        const cnt   = columnTotals[key] ?? 0;
                                        return (
                                            <div key={key} className="pl-status-row-item">
                                                <div style={{ width: 26, height: 26, borderRadius: 7, flexShrink: 0, background: cfg.bg, display: 'flex', alignItems: 'center', justifyContent: 'center', color: cfg.color }}>
                                                    {cfg.icon}
                                                </div>
                                                <span style={{ flex: 1, fontSize: 12, fontWeight: 600, color: BDY }}>{cfg.label}</span>
                                                <span style={{ background: cnt > 0 ? cfg.color : '#F3F4F6', color: cnt > 0 ? '#fff' : MUT, fontSize: 10.5, fontWeight: 800, padding: '2px 8px', borderRadius: 20, minWidth: 24, textAlign: 'center', transition: 'all .2s' }}>
                                                    {cnt}
                                                </span>
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* ── RIGHT: kanban board ── */}
                    <div className="pl-board">
                        {STATUSES.map(key => (
                            <KanbanColumn key={key} statusKey={key} cfg={STATUS_CONFIG[key]}
                                initialLeads={columns[key] ?? []} total={columnTotals[key] ?? 0}
                                filters={filters} urls={urls}
                                onDrop={registerColumn} telecallers={telecallers}/>
                        ))}
                    </div>

                </div>
            </div>

            {/* ── Assign telecaller modal (Bootstrap, orange-themed) ── */}
            <div className="modal fade" id="assignTelecallerModal" tabIndex={-1} data-bs-backdrop="static">
                <div className="modal-dialog modal-dialog-centered" style={{ maxWidth: 420 }}>
                    <div className="modal-content" style={{ borderRadius: 14, border: `1px solid ${BOR}`, boxShadow: '0 20px 60px rgba(0,0,0,.12)', fontFamily: 'Poppins,sans-serif' }}>
                        {/* Header */}
                        <div className="pl-modal-header">
                            <div style={{ width: 3, height: 22, borderRadius: 2, background: OR, flexShrink: 0 }}/>
                            <div style={{ width: 34, height: 34, borderRadius: 9, background: '#FFF7ED', display: 'flex', alignItems: 'center', justifyContent: 'center', color: OR, flexShrink: 0 }}>
                                <LuUserCheck size={18}/>
                            </div>
                            <div>
                                <div style={{ fontSize: 14, fontWeight: 700, color: DK }}>Assign Telecaller</div>
                                <div style={{ fontSize: 10.5, color: MUT, marginTop: 1 }}>Select a telecaller for this lead</div>
                            </div>
                        </div>

                        {/* Body */}
                        <div className="modal-body" style={{ padding: '20px 24px' }}>
                            <p style={{ fontSize: 12.5, color: BDY, marginBottom: 16, lineHeight: 1.6 }}>
                                Select a telecaller to assign this lead to. The lead status will be set to <strong style={{ color: OR }}>Assigned</strong>.
                            </p>
                            <label className="pl-lbl" style={{ marginBottom: 6 }}>Telecaller</label>
                            <select id="modalTelecallerSelect" className="pl-fi" style={{ height: 38 }}>
                                <option value="">-- Select Telecaller --</option>
                                {telecallers.map(t => <option key={t.id} value={t.encrypted_id}>{t.name}</option>)}
                            </select>
                            <div id="assignModalError" style={{ display: 'none', color: '#EF4444', fontSize: 11.5, marginTop: 8, fontWeight: 600 }}>
                                Please select a telecaller to continue.
                            </div>
                        </div>

                        {/* Footer */}
                        <div className="modal-footer" style={{ borderTop: `1px solid ${BOR}`, padding: '16px 24px', gap: 8 }}>
                            <button type="button" className="pl-modal-cancel-btn" data-bs-dismiss="modal"
                                onClick={() => {
                                    if (pendingDrag) {
                                        revertMove(pendingDrag.leadEncId, pendingDrag.newStatus, pendingDrag.oldStatus);
                                        setPending(null);
                                    }
                                }}>
                                Cancel
                            </button>
                            <button type="button" className="pl-modal-confirm-btn"
                                onClick={() => {
                                    const sel = document.getElementById('modalTelecallerSelect');
                                    if (!sel.value) { document.getElementById('assignModalError').style.display = 'block'; return; }
                                    document.getElementById('assignModalError').style.display = 'none';
                                    const { leadEncId, newStatus, oldStatus } = pendingDrag;
                                    setPending(null);
                                    window.bootstrap.Modal.getInstance(document.getElementById('assignTelecallerModal')).hide();
                                    sendStatusUpdate(leadEncId, newStatus, oldStatus, sel.value);
                                }}>
                                <LuCheck size={14}/> Assign &amp; Move
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {/* ── Overlay (saving spinner) ── */}
            {overlay && (
                <div style={{ position: 'fixed', inset: 0, background: 'rgba(255,255,255,.5)', zIndex: 9999, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                    <div style={{ background: WH, borderRadius: 12, padding: '16px 24px', boxShadow: '0 8px 32px rgba(0,0,0,0.12)', display: 'flex', alignItems: 'center', gap: 10 }}>
                        <div className="spinner-border spinner-border-sm" style={{ color: OR }}/>
                        <span style={{ fontSize: 13, fontWeight: 600, color: DK, fontFamily: 'Poppins,sans-serif' }}>Updating…</span>
                    </div>
                </div>
            )}

            {/* ── Toast ── */}
            <Toast message={toast?.msg} type={toast?.type}/>
        </>
    );
}
