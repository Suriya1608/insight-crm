<x-guest-layout>

    {{-- Card icon + title --}}
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:24px;">
        <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,#FF7A30,#FF5C00);
                    display:flex;align-items:center;justify-content:center;
                    box-shadow:0 4px 14px rgba(255,92,0,.30);flex-shrink:0;">
            <span class="material-icons" style="color:#fff;font-size:22px;">lock_open</span>
        </div>
        <div>
            <div style="font-size:20px;font-weight:800;color:#0f172a;letter-spacing:-.3px;line-height:1.2;">
                Create New Password
            </div>
            <div style="font-size:13px;color:#64748b;margin-top:2px;">
                Choose a strong password for your account
            </div>
        </div>
    </div>

    {{-- Session status --}}
    @if (session('status'))
        <div style="background:#d1fae5;border-radius:10px;padding:12px 16px;margin-bottom:20px;
                    display:flex;align-items:center;gap:10px;font-size:13px;color:#065f46;">
            <span class="material-icons" style="font-size:18px;color:#10b981;">check_circle</span>
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.store') }}" id="resetPasswordForm">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        {{-- Email (read-only, pre-filled) --}}
        <div style="margin-bottom:20px;">
            <label style="font-size:13px;font-weight:600;color:#0f172a;display:block;margin-bottom:6px;">
                Email Address
            </label>
            <div style="position:relative;">
                <span class="material-icons"
                    style="position:absolute;left:13px;top:50%;transform:translateY(-50%);
                           font-size:18px;color:#94a3b8;pointer-events:none;">
                    alternate_email
                </span>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email', $request->email) }}"
                    required
                    autofocus
                    autocomplete="username"
                    class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    style="padding-left:42px;background:#f8fafc;color:#64748b;">
            </div>
            @if ($errors->has('email'))
                <div style="font-size:12px;color:#ef4444;margin-top:5px;display:flex;align-items:center;gap:5px;">
                    <span class="material-icons" style="font-size:14px;">error_outline</span>
                    {{ $errors->first('email') }}
                </div>
            @endif
        </div>

        {{-- New Password --}}
        <div style="margin-bottom:20px;">
            <label for="password" style="font-size:13px;font-weight:600;color:#0f172a;display:block;margin-bottom:6px;">
                New Password
            </label>
            <div style="position:relative;">
                <span class="material-icons"
                    style="position:absolute;left:13px;top:50%;transform:translateY(-50%);
                           font-size:18px;color:#94a3b8;pointer-events:none;">
                    lock
                </span>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="new-password"
                    placeholder="Min. 8 characters"
                    class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                    style="padding-left:42px;padding-right:44px;"
                    oninput="checkStrength(this.value)">
                <button type="button" onclick="toggleVis('password','eyeIcon1')"
                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);
                           background:none;border:none;padding:0;cursor:pointer;color:#94a3b8;
                           display:flex;align-items:center;">
                    <span class="material-icons" id="eyeIcon1" style="font-size:20px;">visibility</span>
                </button>
            </div>
            @if ($errors->has('password'))
                <div style="font-size:12px;color:#ef4444;margin-top:5px;display:flex;align-items:center;gap:5px;">
                    <span class="material-icons" style="font-size:14px;">error_outline</span>
                    {{ $errors->first('password') }}
                </div>
            @endif

            {{-- Strength bar --}}
            <div style="margin-top:8px;">
                <div style="height:4px;border-radius:4px;background:#e2e8f0;overflow:hidden;">
                    <div id="strengthBar" style="height:100%;width:0;border-radius:4px;transition:width .3s,background .3s;"></div>
                </div>
                <div id="strengthLabel" style="font-size:11px;color:#94a3b8;margin-top:4px;"></div>
            </div>
        </div>

        {{-- Confirm Password --}}
        <div style="margin-bottom:28px;">
            <label for="password_confirmation" style="font-size:13px;font-weight:600;color:#0f172a;display:block;margin-bottom:6px;">
                Confirm New Password
            </label>
            <div style="position:relative;">
                <span class="material-icons"
                    style="position:absolute;left:13px;top:50%;transform:translateY(-50%);
                           font-size:18px;color:#94a3b8;pointer-events:none;">
                    lock_clock
                </span>
                <input
                    id="password_confirmation"
                    type="password"
                    name="password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="Re-enter your password"
                    class="form-control {{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
                    style="padding-left:42px;padding-right:44px;"
                    oninput="checkMatch()">
                <button type="button" onclick="toggleVis('password_confirmation','eyeIcon2')"
                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);
                           background:none;border:none;padding:0;cursor:pointer;color:#94a3b8;
                           display:flex;align-items:center;">
                    <span class="material-icons" id="eyeIcon2" style="font-size:20px;">visibility</span>
                </button>
            </div>
            <div id="matchMsg" style="font-size:12px;margin-top:5px;display:none;align-items:center;gap:5px;"></div>
            @if ($errors->has('password_confirmation'))
                <div style="font-size:12px;color:#ef4444;margin-top:5px;display:flex;align-items:center;gap:5px;">
                    <span class="material-icons" style="font-size:14px;">error_outline</span>
                    {{ $errors->first('password_confirmation') }}
                </div>
            @endif
        </div>

        {{-- Submit --}}
        <button type="submit" id="submitBtn" class="btn-primary-crm"
                style="display:flex;align-items:center;justify-content:center;gap:8px;">
            <span class="material-icons" style="font-size:18px;">lock_reset</span>
            Reset Password
        </button>

    </form>

    {{-- Back to login --}}
    <div style="text-align:center;margin-top:24px;">
        <a href="{{ route('login') }}"
           style="font-size:13px;color:#64748b;text-decoration:none;
                  display:inline-flex;align-items:center;gap:6px;font-weight:500;">
            <span class="material-icons" style="font-size:16px;">arrow_back</span>
            Back to Sign In
        </a>
    </div>

    <script>
        function toggleVis(fieldId, iconId) {
            const f = document.getElementById(fieldId);
            const i = document.getElementById(iconId);
            if (f.type === 'password') { f.type = 'text'; i.textContent = 'visibility_off'; }
            else { f.type = 'password'; i.textContent = 'visibility'; }
        }

        function checkStrength(val) {
            const bar   = document.getElementById('strengthBar');
            const label = document.getElementById('strengthLabel');
            let score = 0;
            if (val.length >= 8)              score++;
            if (/[A-Z]/.test(val))            score++;
            if (/[0-9]/.test(val))            score++;
            if (/[^A-Za-z0-9]/.test(val))     score++;

            const map = [
                { w: '0',   bg: 'transparent', text: '' },
                { w: '25%', bg: '#ef4444',      text: 'Weak' },
                { w: '50%', bg: '#f59e0b',      text: 'Fair' },
                { w: '75%', bg: '#3b82f6',      text: 'Good' },
                { w: '100%',bg: '#10b981',      text: 'Strong' },
            ];
            const s = map[score] || map[0];
            bar.style.width = s.w;
            bar.style.background = s.bg;
            label.textContent = s.text;
            label.style.color = s.bg;
            checkMatch();
        }

        function checkMatch() {
            const pw  = document.getElementById('password').value;
            const cfw = document.getElementById('password_confirmation').value;
            const msg = document.getElementById('matchMsg');
            if (!cfw) { msg.style.display = 'none'; return; }
            if (pw === cfw) {
                msg.style.display = 'flex';
                msg.style.color   = '#10b981';
                msg.innerHTML = '<span class="material-icons" style="font-size:14px;">check_circle</span> Passwords match';
            } else {
                msg.style.display = 'flex';
                msg.style.color   = '#ef4444';
                msg.innerHTML = '<span class="material-icons" style="font-size:14px;">error_outline</span> Passwords do not match';
            }
        }
    </script>

</x-guest-layout>
