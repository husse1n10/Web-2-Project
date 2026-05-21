@extends('layouts/blankLayout')

@section('title', 'Two-Factor Verification')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
            <div class="card px-sm-6 px-0">
                <div class="card-body text-center">
                    <!-- Logo -->
                    <div class="app-brand justify-content-center mb-6">
                        <a href="{{ url('/') }}" class="app-brand-link gap-2">
                            <span class="app-brand-logo demo">@include('_partials.macros')</span>
                            <span class="app-brand-text demo text-heading fw-bold">{{ config('variables.templateName') }}</span>
                        </a>
                    </div>
                    <!-- /Logo -->

                    <h4 class="mb-1">Two-Factor Verification 💬</h4>
                    <p class="mb-6 text-start">Open your authenticator app and enter the 6-digit code for CedarGov</p>

                    @if($errors->any())
                    <div class="alert alert-danger py-2 mb-4 text-start">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                    @endif

                    <form action="{{ route('2fa.verify') }}" method="POST" id="otpForm">
                        @csrf
                        <input type="hidden" name="otp_code" id="otpHidden">
                        <div class="d-flex justify-content-center gap-2 mb-6" id="otpRow">
                            @for($i=0;$i<6;$i++)
                            <input type="text" class="form-control text-center fw-bold" style="width:48px;height:52px;font-size:1.25rem;" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="{{ $i===0 ? 'one-time-code' : 'off' }}" required>
                            @endfor
                        </div>
                        <button class="btn btn-primary d-grid w-100 mb-6" type="submit" id="verifyBtn" disabled>Verify & Sign In</button>
                    </form>

                    <div class="d-flex justify-content-center gap-4">
                        <a href="{{ route('login') }}">
                            <i class="icon-base bx bx-chevron-left me-1"></i>Back to Login
                        </a>
                        <a href="#">Lost access?</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const inputs = [...document.querySelectorAll('#otpRow input')];
const hidden = document.getElementById('otpHidden');
const btn = document.getElementById('verifyBtn');

function updateState() {
    const val = inputs.map(i => i.value).join('');
    hidden.value = val;
    btn.disabled = val.length < 6;
}

inputs.forEach((inp, i) => {
    inp.addEventListener('input', e => {
        const v = e.target.value.replace(/\D/,'').slice(-1);
        inp.value = v;
        updateState();
        if(v && i < 5) inputs[i+1].focus();
    });
    inp.addEventListener('keydown', e => {
        if(e.key==='Backspace' && !inp.value && i > 0) { inputs[i-1].focus(); inputs[i-1].value=''; updateState(); }
        if(e.key==='ArrowLeft' && i > 0) { e.preventDefault(); inputs[i-1].focus(); }
        if(e.key==='ArrowRight' && i < 5) { e.preventDefault(); inputs[i+1].focus(); }
    });
    inp.addEventListener('paste', e => {
        e.preventDefault();
        const paste = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g,'').slice(0,6);
        [...paste].forEach((c, j) => { if(inputs[i+j]) inputs[i+j].value = c; });
        updateState();
        const nextEmpty = inputs.findIndex((inp,j) => j >= i && !inp.value);
        if(nextEmpty !== -1) inputs[nextEmpty].focus(); else inputs[5].focus();
    });
});
inputs[0].focus();
</script>
@endpush

