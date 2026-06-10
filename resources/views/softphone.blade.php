<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Softphone</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        /* ── Telecaller theme tokens ─────────────────────────────────────── */
        :root{
            --sp-accent:        #FF5C00;
            --sp-accent-dk:     #e05200;
            --sp-accent-lt:     #fff7ed;
            --sp-accent-ring:   rgba(255,92,0,.25);
            --sp-dark:          #0f172a;
            --sp-dark2:         #1e293b;
            --sp-dark3:         #334155;
            --sp-success:       #10b981;
            --sp-danger:        #ef4444;
            --sp-warning:       #f59e0b;
            --sp-bg:            #f1f5f9;
            --sp-surface:       #ffffff;
            --sp-border:        #e2e8f0;
            --sp-text:          #0f172a;
            --sp-muted:         #64748b;
        }

        *{box-sizing:border-box;margin:0;padding:0;}
        html,body{width:100%;height:100%;overflow:hidden;font-family:'Plus Jakarta Sans',sans-serif;background:var(--sp-bg);}
        body{display:flex;flex-direction:column;}

        /* ── Header — dark like telecaller panel ─────────────────────────── */
        .sp-hdr{
            display:flex;align-items:center;justify-content:space-between;
            padding:11px 14px;
            background:linear-gradient(135deg,var(--sp-dark) 0%,var(--sp-dark2) 100%);
            color:#fff;flex-shrink:0;
            box-shadow:0 2px 10px rgba(0,0,0,.35);
        }
        .sp-hdr-left{display:flex;align-items:center;gap:8px;}
        .sp-hdr-icon{
            width:28px;height:28px;border-radius:8px;
            background:linear-gradient(135deg,var(--sp-accent),var(--sp-accent-dk));
            display:flex;align-items:center;justify-content:center;flex-shrink:0;
            box-shadow:0 2px 6px rgba(255,92,0,.40);
        }
        .sp-hdr-title{font-weight:800;font-size:13px;letter-spacing:.2px;color:#fff;}
        .sp-hdr-dot{width:7px;height:7px;border-radius:50%;background:rgba(255,255,255,.3);flex-shrink:0;transition:background .3s;}
        .sp-hdr-dot.ready{background:#34d399;box-shadow:0 0 5px #34d399;}
        .sp-hdr-dot.on-call{background:#fbbf24;box-shadow:0 0 5px #fbbf24;animation:sp-pulse 1s ease-in-out infinite;}
        .sp-min-btn{
            background:rgba(255,255,255,.10);border:none;cursor:pointer;
            color:rgba(255,255,255,.80);display:flex;align-items:center;
            padding:5px;line-height:1;border-radius:7px;transition:background .15s;
        }
        .sp-min-btn:hover{background:rgba(255,255,255,.22);color:#fff;}

        /* ── Status bar ─────────────────────────────────────────────────── */
        .sp-status{
            display:flex;align-items:center;gap:8px;
            padding:6px 14px;
            background:var(--sp-surface);
            border-bottom:1px solid var(--sp-border);
            flex-shrink:0;
        }
        .sp-dot{width:8px;height:8px;border-radius:50%;background:var(--sp-muted);flex-shrink:0;transition:background .25s;}
        .sp-status-txt{font-size:11.5px;font-weight:600;color:var(--sp-muted);transition:color .25s;}

        /* ── Error banner ───────────────────────────────────────────────── */
        .sp-err-bar{display:none;padding:5px 14px 6px;background:#fef2f2;border-bottom:1px solid #fecaca;flex-shrink:0;}
        .sp-err-bar-txt{font-size:11px;font-weight:600;color:#dc2626;line-height:1.4;word-break:break-word;}

        /* ── Phone display ──────────────────────────────────────────────── */
        .sp-phone{
            padding:10px 14px 4px;
            font-size:22px;font-weight:800;
            color:var(--sp-text);letter-spacing:1.4px;
            min-height:48px;font-variant-numeric:tabular-nums;word-break:break-all;
            background:var(--sp-surface);
        }
        .sp-phone.empty{color:#94a3b8;font-size:18px;font-weight:600;letter-spacing:0;}

        /* ── Dial pad ───────────────────────────────────────────────────── */
        .sp-dp{display:grid;grid-template-columns:repeat(3,1fr);gap:5px;padding:4px 12px 8px;background:var(--sp-surface);}
        .sp-key{
            height:44px;border:1px solid var(--sp-border);border-radius:10px;
            background:var(--sp-bg);
            font-family:'Plus Jakarta Sans',sans-serif;font-size:17px;font-weight:700;
            color:var(--sp-text);cursor:pointer;
            display:flex;align-items:center;justify-content:center;
            transition:background .12s,color .12s,border-color .12s,transform .08s;
        }
        .sp-key:hover{background:var(--sp-accent-lt);color:var(--sp-accent);border-color:#fed7aa;}
        .sp-key:active{background:var(--sp-accent);color:#fff;border-color:var(--sp-accent);transform:scale(.94);}
        .sp-back{
            grid-column:1/-1;height:34px;border:1px solid var(--sp-border);border-radius:9px;
            background:var(--sp-bg);cursor:pointer;
            display:flex;align-items:center;justify-content:center;
            color:var(--sp-muted);transition:background .12s,color .12s,border-color .12s;
        }
        .sp-back:hover{background:#fee2e2;color:var(--sp-danger);border-color:#fecaca;}

        /* ── Call button — orange like telecaller actions ───────────────── */
        .sp-pre-actions{padding:0 12px 10px;background:var(--sp-surface);}
        .sp-call-btn{
            width:100%;height:44px;border:none;border-radius:10px;
            background:linear-gradient(135deg,var(--sp-accent),var(--sp-accent-dk));
            color:#fff;font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:14px;
            cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;
            box-shadow:0 3px 12px rgba(255,92,0,.35);
            transition:box-shadow .15s,transform .08s;
        }
        .sp-call-btn:not(:disabled):hover{box-shadow:0 5px 16px rgba(255,92,0,.50);transform:translateY(-1px);}
        .sp-call-btn:not(:disabled):active{transform:translateY(0);}
        .sp-call-btn:disabled{opacity:.4;cursor:not-allowed;box-shadow:none;}

        /* ── In-call panel ──────────────────────────────────────────────── */
        .sp-incall{display:none;flex-direction:column;padding:10px 14px 10px;gap:8px;background:var(--sp-surface);}
        .sp-timer{
            text-align:center;font-size:32px;font-weight:800;
            font-variant-numeric:tabular-nums;color:var(--sp-text);letter-spacing:1px;
        }
        .sp-call-lbl{
            text-align:center;font-size:12px;color:var(--sp-muted);
            background:var(--sp-bg);border-radius:20px;padding:3px 12px;
            display:inline-block;margin:0 auto;font-weight:600;
        }
        .sp-incall-btns{display:flex;gap:7px;}
        .sp-ibtn{
            flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;
            border-radius:10px;padding:9px 0;
            border:1px solid var(--sp-border);
            background:var(--sp-bg);color:var(--sp-text);
            font-family:'Plus Jakarta Sans',sans-serif;font-weight:600;font-size:11px;
            cursor:pointer;transition:background .12s,border-color .12s,color .12s;
        }
        .sp-ibtn:hover{background:var(--sp-accent-lt);border-color:#fed7aa;color:var(--sp-accent);}
        .sp-ibtn.danger{background:var(--sp-danger);border-color:var(--sp-danger);color:#fff;box-shadow:0 2px 8px rgba(239,68,68,.30);}
        .sp-ibtn.danger:hover{background:#dc2626;border-color:#dc2626;}
        .sp-ibtn.muted{background:#fee2e2;border-color:var(--sp-danger);color:var(--sp-danger);}
        .sp-ibtn.held{background:#fef3c7;border-color:var(--sp-warning);color:#92400e;}

        /* DTMF in-call keypad */
        .sp-dtmf-toggle{
            width:100%;background:none;border:none;color:var(--sp-muted);
            font-family:'Plus Jakarta Sans',sans-serif;font-size:11px;font-weight:600;cursor:pointer;
            padding:3px 0;display:flex;align-items:center;justify-content:center;gap:4px;
        }
        .sp-dtmf-toggle:hover{color:var(--sp-accent);}
        .sp-dtmf-pad{display:none;grid-template-columns:repeat(3,1fr);gap:4px;padding:4px 0;}
        .sp-dtmf-pad.open{display:grid;}
        .sp-dkey{
            height:34px;border:1px solid var(--sp-border);border-radius:7px;
            background:var(--sp-bg);
            font-family:'Plus Jakarta Sans',sans-serif;font-size:15px;font-weight:700;
            color:var(--sp-text);cursor:pointer;transition:background .12s,color .12s;
        }
        .sp-dkey:hover{background:var(--sp-accent-lt);color:var(--sp-accent);}
        .sp-dkey:active{background:var(--sp-accent);color:#fff;border-color:var(--sp-accent);}

        /* ── Agent controls ─────────────────────────────────────────────── */
        .sp-agent{
            display:flex;gap:8px;padding:8px 14px 12px;
            border-top:1px solid var(--sp-border);flex-shrink:0;
            background:var(--sp-surface);
        }
        .sp-abtn{
            flex:1;display:flex;flex-direction:column;align-items:center;gap:3px;
            border-radius:9px;padding:7px 0;
            border:1px solid var(--sp-border);
            background:var(--sp-bg);color:var(--sp-muted);
            font-family:'Plus Jakarta Sans',sans-serif;font-weight:600;font-size:11px;
            cursor:pointer;transition:background .12s,color .12s,border-color .12s;
        }
        .sp-abtn:hover{background:var(--sp-accent-lt);border-color:#fed7aa;color:var(--sp-accent);}

        /* ── Not-configured ─────────────────────────────────────────────── */
        .sp-uncfg{display:none;flex-direction:column;align-items:center;justify-content:center;flex:1;padding:20px;text-align:center;gap:10px;background:var(--sp-bg);}
        .sp-uncfg .material-icons{font-size:36px;color:#94a3b8;}
        .sp-uncfg p{font-size:12px;color:var(--sp-muted);font-weight:600;}

        /* ── Animations ─────────────────────────────────────────────────── */
        @keyframes sp-pulse{0%,100%{opacity:1}50%{opacity:.5}}

        /* ── Incoming call banner — orange accent ────────────────────────── */
        #spIncoming{
            flex-shrink:0;
            background:linear-gradient(135deg,var(--sp-dark) 0%,var(--sp-dark2) 100%);
            color:#fff;padding:12px 14px;
            border-left:3px solid var(--sp-accent);
        }
        #spIncoming .sp-inc-label{font-size:10px;font-weight:700;color:var(--sp-accent);margin-bottom:4px;letter-spacing:1px;text-transform:uppercase;}
        #spIncoming .sp-inc-name{font-size:16px;font-weight:800;letter-spacing:.3px;margin-bottom:1px;}
        #spIncoming .sp-inc-code{font-size:11px;font-weight:600;opacity:.6;margin-bottom:2px;}
        #spIncoming .sp-inc-phone{font-size:13px;font-weight:600;opacity:.85;letter-spacing:.5px;margin-bottom:10px;}
        #spIncoming .sp-inc-btns{display:flex;gap:8px;}
        .sp-inc-btn{
            flex:1;height:38px;border:none;border-radius:9px;
            font-family:'Plus Jakarta Sans',sans-serif;font-weight:700;font-size:13px;
            cursor:pointer;display:flex;align-items:center;justify-content:center;gap:5px;
            transition:opacity .15s,transform .08s;
        }
        .sp-inc-btn:hover{opacity:.88;transform:translateY(-1px);}
        .sp-inc-btn:active{transform:translateY(0);}
        .sp-inc-btn.accept{background:var(--sp-success);color:#fff;box-shadow:0 2px 8px rgba(16,185,129,.35);}
        .sp-inc-btn.reject{background:var(--sp-danger);color:#fff;box-shadow:0 2px 8px rgba(239,68,68,.35);}

        @keyframes sp-ring-pulse{
            0%,100%{box-shadow:0 0 0 0 var(--sp-accent-ring)}
            70%{box-shadow:0 0 0 10px rgba(255,92,0,0)}
        }
        #spIncoming.ringing{animation:sp-ring-pulse 1.2s ease-in-out infinite;}
    </style>
</head>
<body>

{{-- Header --}}
<div class="sp-hdr">
    <div class="sp-hdr-left">
        <div class="sp-hdr-icon">
            <span class="material-icons" style="font-size:15px;color:#fff;">call</span>
        </div>
        <span class="sp-hdr-title">Softphone</span>
        <span class="sp-hdr-dot" id="spHdrDot"></span>
    </div>
    <button class="sp-min-btn" id="spMinBtn" title="Minimize">
        <span class="material-icons" style="font-size:17px;" id="spMinIcon">remove</span>
    </button>
</div>

{{-- Incoming Call Banner (hidden until an invite arrives) --}}
<div id="spIncoming" style="display:none;">
    <div class="sp-inc-label">Incoming Call</div>
    <div class="sp-inc-name" id="spIncomingName" style="display:none;"></div>
    <div class="sp-inc-code" id="spIncomingCode" style="display:none;"></div>
    <div class="sp-inc-phone" id="spIncomingPhone">Unknown</div>
    <div class="sp-inc-btns">
        <button class="sp-inc-btn accept" id="spAcceptBtn">
            <span class="material-icons" style="font-size:16px;">call</span> Accept
        </button>
        <button class="sp-inc-btn reject" id="spRejectBtn">
            <span class="material-icons" style="font-size:16px;">call_end</span> Reject
        </button>
    </div>
</div>

{{-- Status --}}
<div class="sp-status">
    <span class="sp-dot" id="spDot"></span>
    <span class="sp-status-txt" id="spStatusTxt">Connecting&hellip;</span>
</div>

{{-- Error message banner (shown on tcn:error, cleared on ready) --}}
<div class="sp-err-bar" id="spErrBar">
    <span class="sp-err-bar-txt" id="spErrTxt"></span>
</div>

{{-- Phone display --}}
<div class="sp-phone empty" id="spPhone">&mdash;</div>

{{-- Dial pad + call button --}}
<div id="spDialSec">
    <div class="sp-dp" id="spDp"></div>
    <div class="sp-pre-actions">
        <button class="sp-call-btn" id="spCallBtn" disabled>
            <span class="material-icons" style="font-size:17px;">call</span> Call
        </button>
    </div>
</div>

{{-- In-call panel --}}
<div class="sp-incall" id="spInCall">
    <div class="sp-timer" id="spTimer">0:00</div>
    <div class="sp-call-lbl" id="spCallLbl"></div>
    <div class="sp-incall-btns">
        <button class="sp-ibtn" id="spMuteBtn">
            <span class="material-icons" style="font-size:21px;">mic</span>Mute
        </button>
        <button class="sp-ibtn" id="spHoldBtn">
            <span class="material-icons" style="font-size:21px;" id="spHoldIco">pause_circle</span>
            <span id="spHoldLbl">Hold</span>
        </button>
        <button class="sp-ibtn danger" id="spHangupBtn">
            <span class="material-icons" style="font-size:21px;">call_end</span>End
        </button>
    </div>
    {{-- DTMF keypad (shown during call) --}}
    <button class="sp-dtmf-toggle" id="spDtmfToggle">
        <span class="material-icons" style="font-size:14px;">dialpad</span> Keypad
    </button>
    <div class="sp-dtmf-pad" id="spDtmfPad"></div>
</div>

{{-- Agent controls --}}
<div class="sp-agent" id="spAgent">
    <button class="sp-abtn" id="spPauseBtn">
        <span class="material-icons" style="font-size:17px;" id="spPauseIco">pause</span>
        <span id="spPauseLbl">Pause</span>
    </button>
    <button class="sp-abtn" id="spLogoutBtn">
        <span class="material-icons" style="font-size:17px;">logout</span>Logout
    </button>
</div>

{{-- Not-configured state --}}
<div class="sp-uncfg" id="spUncfg">
    <span class="material-icons">phone_disabled</span>
    <p>TCN not configured.<br>Contact your admin.</p>
</div>

{{-- 419 handler (same-origin fetch interceptor) --}}
<script>
(function () {
    var _orig = window.fetch;
    window.fetch = function (input, init) {
        init = Object.assign({}, init);
        init.headers = Object.assign({ 'X-Requested-With': 'XMLHttpRequest' }, init.headers);
        return _orig.call(window, input, init);
    };
})();
</script>

{{-- TCN Service singleton --}}
<script src="{{ asset('js/tcn-service.js') }}"></script>

{{-- Softphone UI + logic --}}
<script>
(function () {
    'use strict';

    // ── Singleton guard ───────────────────────────────────────────
    // window.sipInitialized persists for the iframe's lifetime.
    // The iframe is kept alive by data-turbo-permanent on the parent,
    // so this flag survives all Turbo navigations — SIP only inits once.
    if (window.sipInitialized) {
        console.log('[SP] SIP already initialized — skipping.');
        return;
    }
    window.sipInitialized = true;

    // ── State ────────────────────────────────────────────────────
    var _state        = 'connecting';
    var _phone        = '';
    var _leadId              = null;    // set by parent via CALL message
    var _campaignContactId   = null;    // set by parent via CALL message (campaign contact calls)
    var _muted        = false;
    var _onHold       = false;
    var _paused       = false;
    var _secs         = 0;
    var _timer        = null;
    var _min          = false;   // minimized?
    var _dtmfOpen     = false;
    var _autoAnswered = false;   // true when the current call was an inbound auto-answer

    // ── DOM ──────────────────────────────────────────────────────
    function g(id) { return document.getElementById(id); }
    var D = {
        dot:     g('spDot'),     status: g('spStatusTxt'),
        hdrDot:  g('spHdrDot'),
        phone:   g('spPhone'),   dialSec: g('spDialSec'),
        dp:      g('spDp'),      callBtn: g('spCallBtn'),
        inCall:  g('spInCall'),  timer:   g('spTimer'),
        callLbl: g('spCallLbl'),
        muteBtn: g('spMuteBtn'),
        holdBtn: g('spHoldBtn'), holdIco: g('spHoldIco'), holdLbl: g('spHoldLbl'),
        hangupBtn: g('spHangupBtn'),
        dtmfToggle: g('spDtmfToggle'), dtmfPad: g('spDtmfPad'),
        agent:   g('spAgent'),
        pauseBtn: g('spPauseBtn'), pauseIco: g('spPauseIco'), pauseLbl: g('spPauseLbl'),
        logoutBtn: g('spLogoutBtn'),
        minBtn:  g('spMinBtn'), minIco: g('spMinIcon'),
        uncfg:   g('spUncfg'),
        errBar:  g('spErrBar'),  errTxt: g('spErrTxt'),
        // Incoming call
        incoming:      g('spIncoming'),
        incomingName:  g('spIncomingName'),
        incomingCode:  g('spIncomingCode'),
        incomingPhone: g('spIncomingPhone'),
        acceptBtn:     g('spAcceptBtn'),
        rejectBtn:     g('spRejectBtn'),
    };

    var COLORS = { connecting:'#64748b', ready:'#10b981', paused:'#f59e0b', calling:'#6366f1', 'on-call':'#ef4444', error:'#ef4444' };
    var LABELS = { connecting:'Connecting\u2026', ready:'Ready', paused:'Paused', calling:'Calling\u2026', 'on-call':'On Call', error:'Error' };

    // ── Render ───────────────────────────────────────────────────
    function render() {
        var c = COLORS[_state] || '#64748b';
        D.dot.style.background   = c;
        D.status.style.color     = c;
        D.status.textContent     = LABELS[_state] || _state;

        // Header dot: reflects live status
        if (D.hdrDot) {
            D.hdrDot.className = 'sp-hdr-dot' +
                (_state === 'ready'   ? ' ready'   :
                 _state === 'on-call' || _state === 'calling' ? ' on-call' : '');
        }

        var inCall = (_state === 'calling' || _state === 'on-call');
        D.dialSec.style.display  = inCall ? 'none'  : 'block';
        D.inCall.style.display   = inCall ? 'flex'  : 'none';
        D.agent.style.display    = inCall ? 'none'  : 'flex';

        var canCall = (_state === 'ready' && _phone.length >= 5);
        D.callBtn.disabled      = !canCall;
        D.callBtn.style.opacity = canCall ? '1' : '0.45';
        if (inCall) D.callLbl.textContent = _phone || '';

        D.phone.textContent = _phone || '\u2014';
        D.phone.className   = 'sp-phone' + (_phone ? '' : ' empty');

        // Hold button — disabled during 'calling' (OUTBOUND_LOCKED), only active on 'on-call'
        var canHold = (_state === 'on-call');
        D.holdBtn.disabled      = !canHold;
        D.holdBtn.style.opacity = canHold ? '1' : '0.4';
        D.holdBtn.style.cursor  = canHold ? 'pointer' : 'not-allowed';
        if (_onHold) {
            D.holdIco.textContent  = 'play_circle';
            D.holdLbl.textContent  = 'Resume';
            D.holdBtn.className    = 'sp-ibtn held';
        } else {
            D.holdIco.textContent  = 'pause_circle';
            D.holdLbl.textContent  = 'Hold';
            D.holdBtn.className    = 'sp-ibtn';
        }

        // Pause button appearance
        if (_paused) {
            D.pauseIco.textContent = 'play_arrow';
            D.pauseLbl.textContent = 'Resume';
            D.pauseBtn.style.cssText = 'background:#f59e0b;border-color:#f59e0b;color:#fff;';
        } else {
            D.pauseIco.textContent = 'pause';
            D.pauseLbl.textContent = 'Pause';
            D.pauseBtn.style.cssText = '';
        }

        // Pulsing tab icon while calling
        D.dot.style.animation = (_state === 'calling') ? 'sp-pulse 1s ease-in-out infinite' : '';

        // Clear error banner and unconfigured panel when leaving error state
        if (_state !== 'error') {
            if (D.errBar) D.errBar.style.display = 'none';
            if (D.uncfg)  D.uncfg.style.display  = 'none';
        }
    }

    function setState(s) { _state = s; render(); }
    function setPhone(p) { _phone = String(p || '').replace(/\s+/g, ''); render(); }

    // ── Build dial pad ───────────────────────────────────────────
    ['1','2','3','4','5','6','7','8','9','*','0','#'].forEach(function (k) {
        var b = document.createElement('button');
        b.className = 'sp-key';
        b.textContent = k;
        b.addEventListener('click', function () {
            if (_state === 'calling' || _state === 'on-call') return;
            _phone += k; render();
        });
        D.dp.appendChild(b);
    });
    var bk = document.createElement('button');
    bk.className = 'sp-back';
    bk.innerHTML = '<span class="material-icons" style="font-size:17px;">backspace</span>';
    bk.addEventListener('click', function () {
        if (_state === 'calling' || _state === 'on-call') return;
        _phone = _phone.slice(0, -1); render();
    });
    D.dp.appendChild(bk);

    // ── Build DTMF in-call keypad ─────────────────────────────────
    ['1','2','3','4','5','6','7','8','9','*','0','#'].forEach(function (k) {
        var b = document.createElement('button');
        b.className = 'sp-dkey';
        b.textContent = k;
        b.addEventListener('click', function () {
            if (window.TCN && window.TCN._callActive) window.TCN.dtmf(k);
        });
        D.dtmfPad.appendChild(b);
    });

    // ── DTMF keypad toggle ────────────────────────────────────────
    D.dtmfToggle.addEventListener('click', function () {
        _dtmfOpen = !_dtmfOpen;
        D.dtmfPad.className = 'sp-dtmf-pad' + (_dtmfOpen ? ' open' : '');
        D.dtmfToggle.innerHTML = '<span class="material-icons" style="font-size:14px;">dialpad</span> ' + (_dtmfOpen ? 'Hide Keypad' : 'Keypad');
    });

    // ── Keyboard input ───────────────────────────────────────────
    window.addEventListener('keydown', function (e) {
        if (_state === 'calling' || _state === 'on-call') return;
        if (e.metaKey || e.ctrlKey || e.altKey) return;

        if (/^[0-9*#]$/.test(e.key)) {
            _phone += e.key; render();
        } else if (e.key === '+' && _phone.length === 0) {
            _phone = '+'; render();
        } else if (e.key === 'Backspace') {
            _phone = _phone.slice(0, -1); render();
        } else if (e.key === 'Enter') {
            handleCall();
        }
    });

    // ── Timer ────────────────────────────────────────────────────
    function startTimer() {
        _secs = 0; stopTimer(); tick();
        _timer = setInterval(tick, 1000);
    }
    function stopTimer() { if (_timer) { clearInterval(_timer); _timer = null; } }
    function tick() {
        _secs++;
        var m = Math.floor(_secs / 60), s = _secs % 60;
        D.timer.textContent = m + ':' + (s < 10 ? '0' : '') + s;
    }

    // ── Ringtone (Web Audio API) ─────────────────────────────────
    var _ringCtx = null;
    var _ringInterval = null;

    function _startRingtone() {
        _stopRingtone();
        try {
            _ringCtx = new (window.AudioContext || window.webkitAudioContext)();
            function _beep() {
                if (!_ringCtx) return;
                var osc = _ringCtx.createOscillator();
                var gain = _ringCtx.createGain();
                osc.connect(gain);
                gain.connect(_ringCtx.destination);
                osc.type = 'sine';
                osc.frequency.value = 480;
                gain.gain.setValueAtTime(0.25, _ringCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, _ringCtx.currentTime + 0.4);
                osc.start(_ringCtx.currentTime);
                osc.stop(_ringCtx.currentTime + 0.45);
            }
            _beep();
            _ringInterval = setInterval(_beep, 1800);
        } catch (_) {}
    }

    function _stopRingtone() {
        if (_ringInterval) { clearInterval(_ringInterval); _ringInterval = null; }
        if (_ringCtx) { try { _ringCtx.close(); } catch (_) {} _ringCtx = null; }
    }

    // ── Incoming call helpers ────────────────────────────────────
    function _showIncoming(phone, name, code) {
        D.incomingPhone.textContent = phone || 'Unknown';
        if (D.incomingName) {
            D.incomingName.textContent = name || '';
            D.incomingName.style.display = name ? 'block' : 'none';
        }
        if (D.incomingCode) {
            D.incomingCode.textContent = code || '';
            D.incomingCode.style.display = code ? 'block' : 'none';
        }
        D.incoming.style.display = 'block';
        D.incoming.classList.add('ringing');
        // Hide dialer so only the incoming call UI shows
        if (D.dialSec) D.dialSec.style.display = 'none';
        _startRingtone();
    }

    function _hideIncoming() {
        _stopRingtone();
        D.incoming.style.display = 'none';
        D.incoming.classList.remove('ringing');
        // Restore dialer visibility
        if (D.dialSec) D.dialSec.style.display = '';
    }

    // ── postMessage to parent ────────────────────────────────────
    // Works in both modes:
    //   popup → use window.opener (parent page that called window.open)
    //   iframe → use window.parent (legacy / fallback)
    function toParent(msg) {
        try {
            if (window.opener && !window.opener.closed) {
                window.opener.postMessage(msg, '*');
            } else {
                window.parent.postMessage(msg, '*');
            }
        } catch (_) {}
    }

    // ── TCN events → forward to parent ──────────────────────────
    window.addEventListener('tcn:ready', function () {
        _paused = false;
        if (D.errBar) D.errBar.style.display = 'none';
        setState('ready');
        toParent({ type: 'TCN_READY' });
    });
    window.addEventListener('tcn:callStarted', function (e) {
        var d = e.detail || {};
        if (d.phone) setPhone(d.phone);
        // Reset timer display to 0:00 — do NOT start the interval here.
        // Timer must only run once the customer answers (tcn:callAnswered).
        stopTimer(); _secs = 0; D.timer.textContent = '0:00';
        setState('calling');
        // Include incoming flag so the parent call bar can show "Auto-Answered"
        var wasAutoAnswered = _autoAnswered;
        _autoAnswered = false;   // reset immediately after reading
        toParent({ type: 'TCN_CALL_STARTED', phone: d.phone || _phone, callLogId: d.callLogId, incoming: wasAutoAnswered });
    });
    window.addEventListener('tcn:callAnswered', function (e) {
        var d = e.detail || {};
        // Start timer only when customer answers — not on dial-out.
        startTimer();
        setState('on-call');
        toParent({ type: 'TCN_CALL_ANSWERED', phone: _phone, callLogId: d.callLogId });
    });
    window.addEventListener('tcn:callEnded', function (e) {
        var d = e.detail || {};
        var endedPhone = _phone;  // capture before clearing
        stopTimer(); _muted = false; _onHold = false; _dtmfOpen = false;
        _hideIncoming();   // dismiss any lingering incoming banner, restores dialer
        resetMute();
        D.dtmfPad.className = 'sp-dtmf-pad';
        D.dtmfToggle.innerHTML = '<span class="material-icons" style="font-size:14px;">dialpad</span> Keypad';
        _phone = '';      // clear dialer so the number doesn't persist after call ends
        setState(_paused ? 'paused' : 'ready');
        toParent({ type: 'TCN_CALL_ENDED', phone: endedPhone, callLogId: d.callLogId, duration: d.duration, status: d.status || 'completed' });
    });
    window.addEventListener('tcn:sipDropped', function () {
        if (_state !== 'calling' && _state !== 'on-call') setState('connecting');
        toParent({ type: 'TCN_SIP_DROPPED' });
    });
    window.addEventListener('tcn:loggedOut', function () {
        // Reset flags so the next explicit START_SIP is allowed
        window.sipInitialized = false;
        window._sipBooted = false;
        stopTimer(); setState('connecting');
        toParent({ type: 'TCN_LOGGED_OUT' });
    });
    window.addEventListener('tcn:error', function (e) {
        var msg = (e.detail && e.detail.message) || 'Unknown error';
        // Show message in softphone regardless of current state
        if (D.errTxt) D.errTxt.textContent = msg;
        if (D.errBar) D.errBar.style.display = 'block';
        // Transition to error state only when not actively on a call
        if (_state !== 'calling' && _state !== 'on-call') setState('error');
        toParent({ type: 'TCN_ERROR', message: msg });
    });

    // Auto-recover: when keep-alive reports agent READY, clear error and restore state
    window.addEventListener('tcn:statusUpdate', function (e) {
        var status = ((e.detail && e.detail.status) || '').toUpperCase();
        if (_state === 'error' && (status === 'READY' || status === 'AVAILABLE')) {
            if (D.errBar) D.errBar.style.display = 'none';
            setState('ready');
        }
    });

    // ── Receive commands from parent ─────────────────────────────
    window.addEventListener('message', function (ev) {
        var d = ev.data;
        if (!d || typeof d !== 'object') return;
        if (d.type === 'CALL')            { if (d.phone) setPhone(d.phone); _leadId = d.leadId || null; _campaignContactId = d.campaignContactId || null; handleCall(); }
        if (d.type === 'HANGUP')          { handleHangup(); }
        if (d.type === 'MUTE')            { toggleMute(); }
        if (d.type === 'HOLD')            { toggleHold(); }
        if (d.type === 'DTMF')            { if (window.TCN && d.digit) window.TCN.dtmf(d.digit); }
        if (d.type === 'SET_PHONE')       { setPhone(d.phone || ''); }
        if (d.type === 'LOGOUT')          { handleLogout(); }
        if (d.type === 'LOGOUT_SILENT')   { handleLogoutSilent(); }
        if (d.type === 'START_SIP')       { bootSip(); }
        if (d.type === 'ACCEPT_INCOMING') {
            _autoAnswered = true;
            _hideIncoming();
            if (window.TCN && window.TCN.acceptIncomingCall) window.TCN.acceptIncomingCall();
        }
        if (d.type === 'REJECT_INCOMING') {
            _hideIncoming();
            if (window.TCN && window.TCN.rejectIncomingCall) window.TCN.rejectIncomingCall();
        }
    });

    // ── Call actions ─────────────────────────────────────────────
    function handleCall() {
        // Block only when already in a call — not on 'connecting'.
        // TcnService.call() handles re-initialization internally, so
        // we must not gate on _state === 'ready' here.
        if (_state === 'calling' || _state === 'on-call') return;
        if (_phone.length < 5) return;
        var leadId             = _leadId;            // capture before async operations
        var campaignContactId  = _campaignContactId;
        if (window.TcnService) {
            window.TcnService.call(_phone, leadId, campaignContactId).catch(function (e) {
                console.error('[SP] call failed:', e.message);
                // tcn:error already fired — only set error state if it didn't run
                if (_state !== 'error') setState('error');
            });
        } else if (window.TCN && window.TCN._loggedIn) {
            window.TCN.startCall(_phone, leadId, campaignContactId).catch(function (e) {
                console.error('[SP] startCall failed:', e.message);
            });
        }
    }

    function handleHangup() {
        if (!window.TCN || !window.TCN._callActive) return;
        if (window.TCN._isIncoming) {
            window.TCN.endIncomingCall();
        } else {
            window.TCN.endCall();
        }
    }

    function toggleHold() {
        if (_state !== 'on-call') return;
        if (!window.TCN || !window.TCN._callActive) return;
        if (_onHold) {
            window.TCN.resume();
        } else {
            window.TCN.hold();
        }
    }

    // Reflect TCN hold/resume events in the UI and notify the parent call bar
    window.addEventListener('tcn:onHold', function () {
        _onHold = true;
        render();
        toParent({ type: 'TCN_ON_HOLD' });
    });
    window.addEventListener('tcn:offHold', function () {
        _onHold = false;
        render();
        toParent({ type: 'TCN_OFF_HOLD' });
    });

    // ── Incoming call events ─────────────────────────────────────
    //
    // MANUAL-ANSWER MODE: Show the incoming call banner and notify the parent
    // page. The agent must click Accept or Reject — no auto-answer.
    window.addEventListener('tcn:incomingCall', function (e) {
        var phone = (e.detail && e.detail.phone) || null;
        _phone = phone;
        _autoAnswered = false;

        // Show the incoming banner inside the iframe (with ringtone)
        _showIncoming(phone || 'Incoming');

        // Notify the parent page so it can show its own incoming call popup
        toParent({ type: 'TCN_INCOMING_CALL', phone: phone || 'Incoming' });
    });

    // ANI + lead info resolved after initial detection
    var _name     = null;
    var _leadCode = null;

    window.addEventListener('tcn:phoneResolved', function (e) {
        var phone    = (e.detail && e.detail.phone)    || null;
        var name     = (e.detail && e.detail.name)     || null;
        var leadCode = (e.detail && e.detail.leadCode) || null;
        if (!phone) return;
        _phone    = phone;
        _name     = name;
        _leadCode = leadCode;
        if (D.incomingPhone) D.incomingPhone.textContent = phone;
        if (D.incomingName) {
            D.incomingName.textContent = name || '';
            D.incomingName.style.display = name ? 'block' : 'none';
        }
        if (D.incomingCode) {
            D.incomingCode.textContent = leadCode || '';
            D.incomingCode.style.display = leadCode ? 'block' : 'none';
        }
        render();
        toParent({ type: 'TCN_PHONE_RESOLVED', phone: phone, name: name, leadCode: leadCode });
    });

    window.addEventListener('tcn:incomingCallRejected', function () {
        _autoAnswered = false;
        _hideIncoming();
        toParent({
            type:      'TCN_INCOMING_REJECTED',
            phone:     _phone    || null,
            name:      _name     || null,
            leadCode:  _leadCode || null,
            callLogId: (window.TCN && window.TCN._activeLogId) ? window.TCN._activeLogId : null,
        });
        _phone = ''; _name = null; _leadCode = null;
    });

    // Accept / Reject buttons kept for edge-case manual override
    // (e.g. when auto-accept fails and the banner is shown via _showIncoming)
    D.acceptBtn.addEventListener('click', function () {
        _hideIncoming();
        _autoAnswered = true;
        if (window.TCN && window.TCN.acceptIncomingCall) window.TCN.acceptIncomingCall();
    });

    D.rejectBtn.addEventListener('click', function () {
        _autoAnswered = false;
        _hideIncoming();
        if (window.TCN && window.TCN.rejectIncomingCall) window.TCN.rejectIncomingCall();
    });

    function toggleMute() {
        if (!window.TCN) return;
        _muted = !_muted;
        if (_muted) { window.TCN.mute(); renderMuted(); }
        else         { window.TCN.unmute(); resetMute(); }
    }
    function renderMuted() {
        D.muteBtn.innerHTML = '<span class="material-icons" style="font-size:21px;">mic_off</span>Unmute';
        D.muteBtn.className = 'sp-ibtn muted';
    }
    function resetMute() {
        D.muteBtn.innerHTML = '<span class="material-icons" style="font-size:21px;">mic</span>Mute';
        D.muteBtn.className = 'sp-ibtn';
    }

    // ── Pause / Resume ───────────────────────────────────────────
    function togglePause() {
        if (_state === 'calling' || _state === 'on-call') return;
        var newPaused = !_paused;
        var status    = newPaused ? 'UNAVAILABLE' : 'READY';
        D.pauseBtn.disabled = true;
        var csrf = document.querySelector('meta[name="csrf-token"]');
        fetch('/tcn/set-status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf ? csrf.content : '' },
            body: JSON.stringify({ status: status }),
        }).then(function (r) { return r.json(); })
          .then(function () { _paused = newPaused; setState(_paused ? 'paused' : 'ready'); })
          .catch(function () { _paused = newPaused; setState(_paused ? 'paused' : 'ready'); })
          .finally(function () { D.pauseBtn.disabled = false; });
    }

    // ── Logout (with confirm — used by in-panel Logout button) ──────
    function handleLogout() {
        if (!confirm('Log out of TCN softphone?')) return;
        handleLogoutSilent();
    }

    // ── Logout (no confirm — used by header Stop button via LOGOUT_SILENT) ──
    function handleLogoutSilent() {
        window.sipInitialized = false;
        window._sipBooted = false;
        if (window.TcnService) window.TcnService.logout();
        else if (window.TCN)   window.TCN.logout();
        setState('connecting');
        toParent({ type: 'TCN_LOGGED_OUT' });
    }

    // ── Minimize / Expand ────────────────────────────────────────
    D.minBtn.addEventListener('click', function () {
        _min = !_min;
        D.minIco.textContent = _min ? 'add' : 'remove';
        toParent({ type: _min ? 'SP_MINIMIZE' : 'SP_EXPAND' });
    });

    // ── Button events ────────────────────────────────────────────
    D.callBtn.addEventListener('click', handleCall);
    D.hangupBtn.addEventListener('click', handleHangup);
    D.holdBtn.addEventListener('click', toggleHold);
    D.muteBtn.addEventListener('click', toggleMute);
    D.pauseBtn.addEventListener('click', togglePause);
    D.logoutBtn.addEventListener('click', handleLogout);

    // ── Initial render ───────────────────────────────────────────
    render();

    // ── Deferred SIP boot ─────────────────────────────────────────
    // SIP does NOT auto-start on iframe load by default. The parent sends
    // { type: 'START_SIP' } when the user clicks "Ready".  We also
    // self-boot here from localStorage so the iframe doesn't depend on the
    // parent postMessage arriving at exactly the right time (DOMContentLoaded
    // fires before the iframe HTTP response completes, so the message can be
    // lost on first click and after hard page reloads).
    // _sipBooted is a per-iframe-lifetime guard (complements sipInitialized).
    window._sipBooted = false;

    function bootSip() {
        if (window._sipBooted) return;
        window._sipBooted = true;
        console.log('[SP] Booting SIP on START_SIP command.');

        if (window.TcnService) {
            window.TcnService.init()
                .then(function (ok) {
                    if (!ok) {
                        // TCN not configured for this agent — show unconfigured state.
                        window.sipInitialized = false;
                        window._sipBooted = false;
                        D.dialSec.style.display = 'none';
                        D.agent.style.display   = 'none';
                        D.uncfg.style.display   = 'flex';
                        setState('error');
                    }
                })
                .catch(function (e) {
                    console.error('[SP] TcnService.init failed:', e);
                    window.sipInitialized = false;
                    window._sipBooted = false;
                    setState('error');
                });
        } else {
            window.sipInitialized = false;
            window._sipBooted = false;
            D.dialSec.style.display = 'none';
            D.agent.style.display   = 'none';
            D.uncfg.style.display   = 'flex';
            setState('error');
        }
    }

    // ── Self-boot from localStorage ───────────────────────────────
    // If the parent persisted tcn_sip_active=1 (user previously clicked Ready),
    // boot SIP immediately on iframe load — no postMessage from parent required.
    // This fixes the timing race where START_SIP is sent before the iframe is
    // ready and the message is silently dropped.
    try {
        if (localStorage.getItem('tcn_sip_active') === '1') {
            bootSip();
        }
    } catch (_) {}

})();
</script>
</body>
</html>
