<x-guest-layout>

    {{-- Card icon + title --}}
    <div style="display:flex;align-items:center;gap:14px;margin-bottom:24px;">
        <div style="width:48px;height:48px;border-radius:14px;background:linear-gradient(135deg,#FF7A30,#FF5C00);
                    display:flex;align-items:center;justify-content:center;
                    box-shadow:0 4px 14px rgba(255,92,0,.30);flex-shrink:0;">
            <span class="material-icons" style="color:#fff;font-size:22px;">lock_reset</span>
        </div>
        <div>
            <div style="font-size:20px;font-weight:800;color:#0f172a;letter-spacing:-.3px;line-height:1.2;">
                Reset Password
            </div>
            <div style="font-size:13px;color:#64748b;margin-top:2px;">
                We'll send a reset link to your inbox
            </div>
        </div>
    </div>

    {{-- Info blurb --}}
    <div style="background:#fff8f5;border:1px solid #ffd5b8;border-radius:12px;
                padding:12px 16px;margin-bottom:28px;display:flex;align-items:flex-start;gap:10px;">
        <span class="material-icons" style="color:#FF5C00;font-size:18px;margin-top:1px;flex-shrink:0;">info</span>
        <p style="font-size:13px;color:#7c3510;line-height:1.6;margin:0;">
            Enter the email address linked to your account and we'll email you a link to reset your password.
        </p>
    </div>

    {{-- Session status (success message) --}}
    @if (session('status'))
        <div style="background:#d1fae5;border-radius:10px;padding:12px 16px;margin-bottom:20px;
                    display:flex;align-items:center;gap:10px;font-size:13px;color:#065f46;">
            <span class="material-icons" style="font-size:18px;color:#10b981;">check_circle</span>
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        {{-- Email field --}}
        <div style="margin-bottom:24px;">
            <label for="email" style="font-size:13px;font-weight:600;color:#0f172a;display:block;margin-bottom:6px;">
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
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="email"
                    placeholder="you@example.com"
                    class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    style="padding-left:42px;">
                @if ($errors->has('email'))
                    <div style="font-size:12px;color:#ef4444;margin-top:5px;display:flex;align-items:center;gap:5px;">
                        <span class="material-icons" style="font-size:14px;">error_outline</span>
                        {{ $errors->first('email') }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Submit button --}}
        <button type="submit" class="btn-primary-crm"
                style="display:flex;align-items:center;justify-content:center;gap:8px;">
            <span class="material-icons" style="font-size:18px;">send</span>
            Send Reset Link
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

</x-guest-layout>
