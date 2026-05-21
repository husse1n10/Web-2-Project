@extends('layouts.app')
@section('title', 'Payment')
@section('page-title', 'Complete Payment')

@section('content')
@php
    $citizenActionLocked = auth()->user()->isCitizen() && !auth()->user()->canUseCitizenSelfServiceActions();
@endphp
<div class="citizen-payment-shell citizen-reveal" data-citizen-reveal>
    <div class="card mb-3">
        <div class="card-body">
            <div class="citizen-payment-summary-row">
                <div class="citizen-payment-summary-icon">
                    <i class="bi bi-receipt"></i>
                </div>
                <div class="citizen-payment-summary-main">
                    <div class="citizen-payment-summary-title">{{ $serviceRequest->resolved_service_name }}</div>
                    <div class="citizen-payment-summary-sub">{{ $serviceRequest->office->name }}</div>
                </div>
                <div class="citizen-payment-summary-price">
                    <div class="citizen-payment-summary-amount">{{ $serviceRequest->formatted_service_price_value }}</div>
                    <div class="citizen-payment-summary-currency">{{ $serviceRequest->resolved_service_currency }}</div>
                </div>
            </div>
            <div class="citizen-payment-ref-row">
                <span>Reference</span>
                <code>{{ $serviceRequest->reference_number }}</code>
            </div>
        </div>
    </div>

    @if($errors->has('payment'))
        <div class="alert alert-danger mb-3">
            <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $errors->first('payment') }}
        </div>
    @endif

    @if($citizenActionLocked)
        <div class="alert alert-warning mb-3">
            <i class="bi bi-lock me-1"></i>{{ __('Finish your profile verification to unlock payments.') }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <span class="card-title">Select Payment Method</span>
        </div>
        <div class="card-body">
            <form action="{{ route('citizen.payment.process', $serviceRequest) }}" method="POST" id="payForm">
                @csrf

                <div class="citizen-payment-method-grid">
                    <label class="citizen-payment-method-item">
                        <input type="radio" name="payment_method" value="card" class="pm-radio" required @disabled($citizenActionLocked)>
                        <span class="pm-option" data-method="card">
                            <i class="bi bi-credit-card-2-front"></i>
                            <span class="pm-option-title">Card</span>
                            <span class="pm-option-sub">Visa / MC</span>
                        </span>
                    </label>
                    <label class="citizen-payment-method-item">
                        <input type="radio" name="payment_method" value="crypto" class="pm-radio" @disabled($citizenActionLocked)>
                        <span class="pm-option" data-method="crypto">
                            <i class="bi bi-currency-bitcoin"></i>
                            <span class="pm-option-title">Crypto</span>
                            <span class="pm-option-sub">BTC / USDT ERC20</span>
                        </span>
                    </label>
                </div>

                <div id="cardFields" class="citizen-payment-fieldset" hidden>
                    <div class="citizen-payment-info is-card">
                        <i class="bi bi-shield-lock-fill"></i>
                        <span>You will be redirected to Stripe secure checkout to complete your card payment.</span>
                    </div>
                </div>

                <div id="cryptoFields" class="citizen-payment-fieldset" hidden>
                    <div class="mb-3">
                        <label class="form-label">Select Cryptocurrency</label>
                        <select name="crypto_currency" class="form-select" @disabled($citizenActionLocked)>
                            <option value="BTC">Bitcoin (BTC)</option>
                            <option value="USDTERC20" selected>Tether (USDT ERC20)</option>
                        </select>
                    </div>
                    <div class="citizen-payment-info is-crypto">
                        <i class="bi bi-info-circle"></i>
                        <span>After clicking pay, you will get the wallet address and exact amount to send.</span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-3 citizen-pay-submit" id="payBtn" @disabled($citizenActionLocked)>
                    <i class="bi {{ $citizenActionLocked ? 'bi-lock-fill' : 'bi-shield-check' }} me-1"></i>
                    {{ $citizenActionLocked ? __('Profile Verification Required') : __('Pay Securely') }}
                </button>
            </form>

            <div class="citizen-payment-safe-note">
                <i class="bi bi-shield-check"></i>
                <span>256-bit SSL encryption &middot; Your data is safe</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
body.es-role-citizen .citizen-payment-shell {
    max-width: 520px;
    margin: 0 auto;
}

body.es-role-citizen .citizen-payment-summary-row {
    display: flex;
    align-items: center;
    gap: .85rem;
}

body.es-role-citizen .citizen-payment-summary-icon {
    width: 2.75rem;
    height: 2.75rem;
    border-radius: .72rem;
    background: #E0F2FE;
    color: #0284C7;
    border: 1px solid #BAE6FD;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    flex-shrink: 0;
}

body.es-role-citizen .citizen-payment-summary-main {
    flex: 1;
    min-width: 0;
}

body.es-role-citizen .citizen-payment-summary-title {
    font-size: .88rem;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

body.es-role-citizen .citizen-payment-summary-sub {
    font-size: .75rem;
    color: #64748B;
}

body.es-role-citizen .citizen-payment-summary-price {
    text-align: right;
    flex-shrink: 0;
}

body.es-role-citizen .citizen-payment-summary-amount {
    font-size: 1.2rem;
    font-weight: 800;
    color: #0284C7;
    line-height: 1.1;
}

body.es-role-citizen .citizen-payment-summary-currency {
    font-size: .7rem;
    color: #94A3B8;
}

body.es-role-citizen .citizen-payment-ref-row {
    border-top: 1px solid #E2E8F0;
    margin-top: .9rem;
    padding-top: .75rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: .75rem;
    color: #64748B;
}

body.es-role-citizen .citizen-payment-method-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: .65rem;
    margin-bottom: 1.25rem;
}

body.es-role-citizen .citizen-payment-method-item {
    cursor: pointer;
}

body.es-role-citizen .pm-radio {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

body.es-role-citizen .pm-option {
    border: 1.5px solid #E2E8F0;
    border-radius: .7rem;
    padding: .9rem;
    text-align: center;
    transition: all .15s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
}

body.es-role-citizen .pm-option i {
    font-size: 1.5rem;
    margin-bottom: .3rem;
}

body.es-role-citizen .pm-option[data-method="card"] i {
    color: #0284C7;
}

body.es-role-citizen .pm-option[data-method="crypto"] i {
    color: #D97706;
}

body.es-role-citizen .pm-option-title {
    font-size: .78rem;
    font-weight: 700;
    color: #334155;
    line-height: 1.2;
}

body.es-role-citizen .pm-option-sub {
    font-size: .66rem;
    color: #94A3B8;
    line-height: 1.2;
    margin-top: .1rem;
}

body.es-role-citizen .pm-option:hover {
    border-color: #7DD3FC;
    background: #F0F9FF;
}

body.es-role-citizen .pm-radio:checked + .pm-option {
    border-color: #0284C7;
    background: #E0F2FE;
    box-shadow: 0 0 0 3px rgba(14, 165, 233, .14);
}

body.es-role-citizen .citizen-payment-fieldset {
    margin-top: .2rem;
}

body.es-role-citizen .citizen-payment-info {
    border-radius: .6rem;
    padding: .82rem;
    font-size: .81rem;
    display: flex;
    align-items: flex-start;
    gap: .45rem;
}

body.es-role-citizen .citizen-payment-info i {
    flex-shrink: 0;
    margin-top: 1px;
}

body.es-role-citizen .citizen-payment-info.is-card {
    background: #EFF6FF;
    color: #1E40AF;
}

body.es-role-citizen .citizen-payment-info.is-crypto {
    background: #EEF6FF;
    color: #1D4ED8;
}

body.es-role-citizen .citizen-pay-submit {
    padding: .7rem;
}

body.es-role-citizen .citizen-payment-safe-note {
    text-align: center;
    margin-top: .9rem;
    font-size: .72rem;
    color: #64748B;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .3rem;
}

body.es-role-citizen .citizen-payment-safe-note i {
    color: #16A34A;
}

@media (max-width: 575.98px) {
    body.es-role-citizen .citizen-payment-method-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@push('scripts')
<script>
const paymentRadios = document.querySelectorAll('.pm-radio');
const cardFields = document.getElementById('cardFields');
const cryptoFields = document.getElementById('cryptoFields');
const payForm = document.getElementById('payForm');
const payBtn = document.getElementById('payBtn');

const togglePaymentFields = (value) => {
    if (!cardFields || !cryptoFields) return;
    cardFields.hidden = value !== 'card';
    cryptoFields.hidden = value !== 'crypto';
};

paymentRadios.forEach((radio) => {
    radio.addEventListener('change', () => togglePaymentFields(radio.value));
});

payForm?.addEventListener('submit', () => {
    payBtn.disabled = true;
    payBtn.setAttribute('aria-busy', 'true');
    payBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Processing...';
});
</script>
@endpush
