@extends('layouts.app')
@section('title', 'Verify Email')
@section('page-title', 'Verify Your Email')

@section('content')
<div class="card" style="max-width: 560px; margin: 1rem auto;">
    <div class="card-body p-4">
        <div class="text-center mb-3">
            <i class="bi bi-envelope-check" style="font-size:2.6rem; color: var(--es-primary);"></i>
        </div>
        <h5 class="text-center mb-3" style="font-weight:700;">Confirm your email address</h5>
        <p class="text-muted text-center" style="font-size:.86rem;">
            We've sent a verification link to <strong>{{ auth()->user()->email }}</strong>.
            Click the link in that email to verify your address. If you didn't receive it,
            we can send a new one.
        </p>

        @if (session('success'))
            <div class="alert alert-success" style="font-size:.84rem;">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('verification.send') }}" class="d-flex justify-content-center gap-2 mt-3">
            @csrf
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-arrow-clockwise me-1"></i> Resend Verification Email
            </button>
        </form>

        <div class="text-center mt-3">
            <a href="{{ route('home') }}" class="text-muted" style="font-size:.82rem;">
                <i class="bi bi-arrow-left me-1"></i> Back to dashboard
            </a>
        </div>
    </div>
</div>
@endsection
