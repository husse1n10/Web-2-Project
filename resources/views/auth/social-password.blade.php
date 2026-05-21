@extends('layouts/blankLayout')

@section('title', 'Set Password')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
<div class="container-xxl">
    <div class="authentication-wrapper authentication-basic container-p-y">
        <div class="authentication-inner">
            <div class="card px-sm-6 px-0">
                <div class="card-body">
                    <!-- Logo -->
                    <div class="app-brand justify-content-center mb-6">
                        <a href="{{ url('/') }}" class="app-brand-link gap-2">
                            <span class="app-brand-logo demo">@include('_partials.macros')</span>
                            <span class="app-brand-text demo text-heading fw-bold">{{ config('variables.templateName') }}</span>
                        </a>
                    </div>
                    <!-- /Logo -->

                    <h4 class="mb-1">Set Your Password 🔐</h4>
                    <p class="mb-6">Complete your {{ ucfirst($social['provider'] ?? 'social') }} sign-in by choosing a password</p>

                    @if(session('info'))
                    <div class="alert alert-info py-2 mb-4">{{ session('info') }}</div>
                    @endif

                    @if($errors->any())
                    <div class="alert alert-danger py-2 mb-4">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                    @endif

                    <div class="bg-lighter rounded p-3 mb-6">
                        <div class="mb-1"><strong>Provider:</strong> {{ ucfirst($social['provider'] ?? 'social') }}</div>
                        <div><strong>Email:</strong> {{ $social['email'] ?? '-' }}</div>
                    </div>

                    <form method="POST" action="{{ route('social.password.store') }}">
                        @csrf
                        <div class="mb-6 form-password-toggle">
                            <label class="form-label" for="password">Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password" class="form-control" name="password" placeholder="Minimum 8 characters" required autocomplete="new-password" />
                                <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                            </div>
                        </div>
                        <div class="mb-6 form-password-toggle">
                            <label class="form-label" for="password_confirmation">Confirm Password</label>
                            <div class="input-group input-group-merge">
                                <input type="password" id="password_confirmation" class="form-control" name="password_confirmation" placeholder="Repeat your password" required autocomplete="new-password" />
                                <span class="input-group-text cursor-pointer"><i class="icon-base bx bx-hide"></i></span>
                            </div>
                        </div>
                        <button class="btn btn-primary d-grid w-100" type="submit">Save Password & Continue</button>
                    </form>

                    <p class="text-center text-muted mt-4" style="font-size:.8rem;">
                        After this step, you can sign in with either social login or email/password.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
