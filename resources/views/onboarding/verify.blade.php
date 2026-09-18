<?php
use App\Models\Utils;
if (!isset($company)) { $company = Utils::company(); }
?>
@extends('layouts.onboarding')

@section('title', 'Verify your phone - ' . ($company->app_name ?? Utils::app_name()))
@section('meta_description', 'Enter the verification code we sent to your phone and email.')

@section('progress-indicator')
    <div class="progress-step">
        <h2 class="progress-title">Verify It's You</h2>
        <p class="progress-description">One quick code, then we set up your school.</p>
    </div>
    <div class="progress-indicator">
        <div class="progress-step-indicator active">✓</div>
        <span>Verification</span>
    </div>
@endsection

@section('content')
    <div class="content-title">Enter your verification code</div>
    <div class="content-description">
        We sent a 6-digit code by SMS to <b>{{ $draft->phone }}</b>
        @if(in_array('email', $channels ?? [])) and by email to <b>{{ $draft->email }}</b>@endif.
        It expires in {{ \App\Models\OnboardingDraft::OTP_TTL_MIN }} minutes.
    </div>

    @if(empty($channels))
        <div class="error-message" style="display:block;margin-bottom:1rem">
            We could not send the code just now. Please press "Send again" in a moment, or contact support at {{ Utils::get_support_phone() }}.
        </div>
    @endif

    <form id="verifyForm" method="POST" action="{{ route('onboarding.verify.process') }}">
        @csrf
        <div class="form-group">
            <label class="form-label" for="code">Verification code *</label>
            <input type="text" id="code" name="code" class="form-input" inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
                   autocomplete="one-time-code" required autofocus placeholder="6-digit code"
                   style="font-size:1.6rem;letter-spacing:.5rem;text-align:center">
            <div class="error-message" id="code_error" @if(session('verify_error')) style="display:block" @endif>{{ session('verify_error') }}</div>
        </div>
        <div style="display:flex;gap:1rem;align-items:center">
            <button type="submit" class="btn btn-primary" style="flex:2"><i class='bx bx-check-shield'></i> Verify &amp; continue</button>
            <button type="submit" form="resendForm" class="btn btn-secondary" style="flex:1">Send again</button>
        </div>
    </form>
    <form id="resendForm" method="POST" action="{{ route('onboarding.verify.resend') }}">@csrf</form>

    <p style="margin-top:1.5rem;font-size:.85rem;color:var(--text-light)">
        Wrong number? <a href="{{ url('onboarding/step2') }}">Go back and change it</a>.
        Closed this page later? Your progress is saved — use the link in the email to resume.
    </p>
@endsection
