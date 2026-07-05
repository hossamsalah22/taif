<!DOCTYPE html>
<html lang="{{ session('locale', 'ar') }}" dir="{{ session('locale', 'ar') === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <title>{{ __('Delete Account') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 1rem;
            transition: all 0.3s ease;
        }

        .container {
            background: white;
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            width: 100%;
            max-width: 450px;
            position: relative;
            backdrop-filter: blur(10px);
        }

        .language-toggle {
            position: absolute;
            top: 1rem;
            right: 1rem;
            display: flex;
            gap: 0.5rem;
        }

        .lang-btn {
            padding: 0.5rem 0.75rem;
            border: 1px solid #e5e7eb;
            background: white;
            border-radius: 0.5rem;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s;
            text-decoration: none;
            color: #6b7280;
        }

        .lang-btn:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }

        .lang-btn.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header-icon {
            width: 60px;
            height: 60px;
            background: #fee2e2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: #dc2626;
        }

        h1 {
            color: #111827;
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            color: #6b7280;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #374151;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1rem;
            z-index: 1;
        }

        [dir="rtl"] .input-icon {
            left: auto;
            right: 0.75rem;
        }

        input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.75rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 0.75rem;
            font-size: 1rem;
            transition: all 0.2s ease;
            background: #fafbfc;
        }

        [dir="rtl"] input {
            padding: 0.875rem 2.75rem 0.875rem 1rem;
        }

        input:focus {
            outline: none;
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        input:invalid {
            border-color: #ef4444;
        }

        select {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 0.75rem;
            font-size: 1rem;
            background: #fafbfc;
            transition: all 0.2s ease;
        }

        select:focus {
            outline: none;
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .password-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            font-size: 1rem;
            padding: 0.25rem;
            z-index: 2;
        }

        [dir="rtl"] .password-toggle {
            right: auto;
            left: 0.75rem;
        }

        .password-toggle:hover {
            color: #6b7280;
        }

        .delete-btn {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 0.75rem;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .delete-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.3);
        }

        .delete-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .delete-btn .btn-text {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .loading {
            display: none;
        }

        .delete-btn.loading .btn-text {
            display: none;
        }

        .delete-btn.loading .loading {
            display: block;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff40;
            border-top: 2px solid #ffffff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .alert {
            padding: 1rem 1.25rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.95rem;
            line-height: 1.4;
        }

        .alert-error {
            color: #991b1b;
            background-color: #fef2f2;
            border: 1px solid #fecaca;
        }

        .alert-success {
            color: #166534;
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
        }

        .warning-box {
            background: #fffbeb;
            border: 1px solid #fed7aa;
            border-radius: 0.75rem;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .warning-title {
            font-weight: 600;
            color: #92400e;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .warning-text {
            color: #a16207;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .checkbox-group {
            margin: 1.5rem 0;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            cursor: pointer;
        }

        .checkbox {
            width: 18px;
            height: 18px;
            margin-top: 0.125rem;
            flex-shrink: 0;
        }

        .checkbox-label {
            font-size: 0.9rem;
            color: #374151;
            line-height: 1.5;
            margin: 0;
        }

        @media (max-width: 480px) {
            .container {
                padding: 1.5rem;
                margin: 0.5rem;
            }

            h1 {
                font-size: 1.5rem;
            }

            .language-toggle {
                position: static;
                justify-content: center;
                margin-bottom: 1rem;
            }
        }

        /* RTL Adjustments */
        [dir="rtl"] {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Noto Sans Arabic", Arial, sans-serif;
        }

        [dir="rtl"] .checkbox-wrapper {
            flex-direction: row-reverse;
        }

        /* Step-specific styles */
        .step-container {
            transition: all 0.3s ease;
        }

        .step-container.slide-out {
            transform: translateX(-100%);
            opacity: 0;
        }

        .step-container.slide-in {
            transform: translateX(0);
            opacity: 1;
        }

        .primary-btn {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 0.75rem;
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .primary-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
        }

        .code-sent-message {
            text-align: center;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 0.75rem;
        }

        .success-icon {
            width: 50px;
            height: 50px;
            background: #22c55e;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.25rem;
            color: white;
        }

        .code-sent-message h3 {
            color: #0c4a6e;
            margin-bottom: 0.5rem;
            font-size: 1.25rem;
        }

        .code-sent-message p {
            color: #0369a1;
            font-size: 0.95rem;
        }

        .code-timer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 0.5rem;
            font-size: 0.875rem;
        }

        #timerText {
            color: #6b7280;
        }

        #countdown {
            font-weight: 600;
            color: #f59e0b;
        }

        .resend-btn {
            background: none;
            border: none;
            color: #3b82f6;
            cursor: pointer;
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            transition: all 0.2s;
        }

        .resend-btn:hover {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .back-btn {
            flex: 1;
            padding: 1rem;
            border: 1.5px solid #d1d5db;
            border-radius: 0.75rem;
            background: white;
            color: #6b7280;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .back-btn:hover {
            border-color: #9ca3af;
            color: #374151;
        }

        .delete-btn {
            flex: 2;
        }

        .helper-text {
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: #6b7280;
        }

        /* Code input special styling */
        input[name="verification_code"] {
            text-align: center;
            font-size: 1.25rem;
            font-weight: 600;
            letter-spacing: 0.2em;
            font-family: monospace;
        }

        /* Animation for form appearance */
        .container {
            animation: fadeInUp 0.6s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Step transition animations */
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutLeft {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(-100%);
                opacity: 0;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Language Toggle -->
        <div class="language-toggle">
            <a href="{{ url()->current() }}?lang=en"
                class="lang-btn {{ session('locale', 'ar') === 'en' ? 'active' : '' }}">
                <i class="fas fa-globe"></i> EN
            </a>
            <a href="{{ url()->current() }}?lang=ar"
                class="lang-btn {{ session('locale', 'ar') === 'ar' ? 'active' : '' }}">
                <i class="fas fa-globe"></i> عر
            </a>
        </div>

        <!-- Header -->
        <div class="header">
            <div class="header-icon">
                <i class="fas fa-user-slash"></i>
            </div>
            <h1>{{ __('delete_account_title') }}</h1>
            <p class="subtitle">{{ __('delete_account_subtitle') }}</p>
        </div>

        <!-- Alert Messages -->
        @if (session('error'))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif
        <!-- Warning Box -->
        <div class="warning-box">
            <div class="warning-title">
                <i class="fas fa-exclamation-triangle"></i>
                {{ __('warning') }}
            </div>
            <div class="warning-text">
                {{ __('delete_account_warning_sms') }}
            </div>
        </div>

        <!-- Step 1: Phone Verification -->
        <div id="step1" class="step-container">
            <form action="{{ route('account.request-delete-code') }}" method="POST" id="codeRequestForm"
                onsubmit="return handleCodeRequest(event)">
                @csrf

                <div class="form-group">
                    <label for="type">{{ __('type') }}</label>

                    <select id="type" name="type" class="form-control" required>
                        <option value="user" {{ old('type') == 'user' ? 'selected' : '' }}>
                            {{ __('user') }}
                        </option>
                        <option value="provider" {{ old('type') == 'provider' ? 'selected' : '' }}>
                            {{ __('provider') }}
                        </option>
                    </select>
                </div>

                <!-- Phone Field -->
                <div class="form-group">
                    <label for="phone">{{ __('phone_number') }}</label>
                    <div class="input-wrapper">
                        <i class="fas fa-phone input-icon"></i>
                        <input type="tel" id="phone" name="phone" required
                            placeholder="{{ __('enter_your_phone_number') }}" autocomplete="tel"
                            value="{{ old('phone') }}">
                    </div>
                    <div class="helper-text">
                        {{ __('sms_verification_code_will_be_sent') }}
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="primary-btn" id="requestCodeBtn">
                    <span class="btn-text">
                        <i class="fas fa-paper-plane"></i>
                        {{ __('send_verification_code') }}
                    </span>
                    <div class="loading">
                        <div class="spinner"></div>
                    </div>
                </button>
            </form>
        </div>

        <!-- Step 2: Code Verification & Final Confirmation -->
        <div id="step2" class="step-container" style="display: none;">
            <form action="{{ route('account.destroy') }}" method="POST" id="deleteForm"
                onsubmit="return handleFormSubmit(event)">
                @csrf
                @method('DELETE')

                <input type="hidden" id="verified_phone" name="login" value="">
                <input type="hidden" id="verification_type" name="login_type" value="">

                <!-- Code Sent Message -->
                <div class="code-sent-message">
                    <div class="success-icon">
                        <i class="fas fa-sms"></i>
                    </div>
                    <h3>{{ __('verification_code_sent') }}</h3>
                    <p id="codeSentTo">{{ __('sms_code_sent_message') }}</p>
                </div>

                <!-- Verification Code Field -->
                <div class="form-group">
                    <label for="verification_code">{{ __('verification_code') }}</label>
                    <div class="input-wrapper">
                        <i class="fas fa-key input-icon"></i>
                        <input type="text" id="verification_code" name="verification_code" required
                            placeholder="Enter 4-digit code" maxlength="4" minlength="4" pattern="[0-9]{4}"
                            autocomplete="one-time-code">
                    </div>
                    <div class="code-timer">
                        <span id="timerText">{{ __('code_expires_in') }} <span id="countdown">05:00</span></span>
                        <button type="button" id="resendBtn" class="resend-btn" style="display: none;"
                            onclick="resendCode()">
                            {{ __('resend_code') }}
                        </button>
                    </div>
                </div>

                <!-- Final Confirmation Checkbox -->
                <div class="checkbox-group">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" class="checkbox" id="final_confirm_delete" required>
                        <span class="checkbox-label">{{ __('final_confirm_delete_checkbox') }}</span>
                    </label>
                </div>

                <!-- Action Buttons -->
                <div class="button-group">
                    <button type="button" class="back-btn" onclick="goBackToStep1()">
                        <i class="fas fa-arrow-left"></i>
                        {{ __('back') }}
                    </button>

                    <button type="submit" class="delete-btn" id="deleteBtn">
                        <span class="btn-text">
                            <i class="fas fa-trash-alt"></i>
                            {{ __('delete_my_account') }}
                        </span>
                        <div class="loading">
                            <div class="spinner"></div>
                        </div>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let countdownTimer;
        let timeLeft = 180;

        function handleCodeRequest(event) {
            event.preventDefault();

            const form = document.getElementById('codeRequestForm');
            const formData = new FormData(form);
            const requestBtn = document.getElementById('requestCodeBtn');

            requestBtn.classList.add('loading');
            requestBtn.disabled = true;

            fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                            formData.get('_token')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => Promise.reject(err));
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        document.getElementById('verified_phone').value = formData.get('phone');
                        document.getElementById('verification_type').value = formData.get('type');
                        const codeSentTo = document.getElementById('codeSentTo');
                        const phone = formData.get('phone');
                        const maskedContact = maskContact(phone);
                        codeSentTo.textContent = `{{ __('verification_code_sent_to') }} ${maskedContact}`;
                        showStep2();
                        startCountdown();
                    } else {
                        showError(data.message || '{{ __('error_sending_code') }}');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (error.message) {
                        showError(error.message);
                    } else if (error.errors) {
                        const errorMessages = Object.values(error.errors).flat();
                        showError(errorMessages.join(', '));
                    } else {
                        showError('Network error occurred. Please try again.');
                    }
                })
                .finally(() => {
                    requestBtn.classList.remove('loading');
                    requestBtn.disabled = false;
                });

            return false;
        }

        function handleFormSubmit(event) {
            const deleteBtn = document.getElementById('deleteBtn');
            const confirmCheckbox = document.getElementById('final_confirm_delete');
            if (!confirmCheckbox.checked) {
                event.preventDefault();
                showError('{{ __('please_confirm_final_deletion') }}');
                return false;
            }
            deleteBtn.classList.add('loading');
            deleteBtn.disabled = true;
            return true;
        }

        function showStep2() {
            const step1 = document.getElementById('step1');
            const step2 = document.getElementById('step2');
            step1.style.display = 'none';
            step2.style.display = 'block';
            step2.style.animation = 'slideInRight 0.3s ease forwards';
        }

        function goBackToStep1() {
            const step1 = document.getElementById('step1');
            const step2 = document.getElementById('step2');
            step2.style.display = 'none';
            step1.style.display = 'block';
            step1.style.animation = 'slideInRight 0.3s ease forwards';
            if (countdownTimer) {
                clearInterval(countdownTimer);
            }
        }

        function startCountdown() {
            timeLeft = 180;
            const countdownElement = document.getElementById('countdown');
            const timerText = document.getElementById('timerText');
            const resendBtn = document.getElementById('resendBtn');

            countdownTimer = setInterval(() => {
                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;

                countdownElement.textContent =
                    `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

                if (timeLeft <= 0) {
                    clearInterval(countdownTimer);
                    timerText.style.display = 'none';
                    resendBtn.style.display = 'inline-block';
                }

                timeLeft--;
            }, 1000);
        }

        function resendCode() {
            const phone = document.getElementById('verified_phone').value;
            const type = document.getElementById('verification_type').value;
            const resendBtn = document.getElementById('resendBtn');

            resendBtn.disabled = true;
            resendBtn.textContent = '{{ __('sending') }}...';

            // Create form data for resend request
            const formData = new FormData();
            formData.append('phone', phone);
            formData.append('verification_type', type);
            formData.append('_token', document.querySelector('input[name="_token"]').value);

            // Make resend API call
            fetch('{{ route('account.request-delete-code') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        resendBtn.disabled = false;
                        resendBtn.textContent = '{{ __('resend_code') }}';
                        resendBtn.style.display = 'none';
                        document.getElementById('timerText').style.display = 'block';
                        startCountdown();
                        showSuccess('{{ __('verification_code_resent') }}');
                    } else {
                        resendBtn.disabled = false;
                        resendBtn.textContent = '{{ __('resend_code') }}';
                        showError(data.message || '{{ __('error_sending_code') }}');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    resendBtn.disabled = false;
                    resendBtn.textContent = '{{ __('resend_code') }}';
                    showError('{{ __('network_error') }}');
                });
        }

        function maskContact(contact) {
            if (contact.includes('@')) {
                // Email
                const [username, domain] = contact.split('@');
                const maskedUsername = username.charAt(0) + '*'.repeat(username.length - 2) + username.charAt(username
                    .length - 1);
                return `${maskedUsername}@${domain}`;
            } else {
                return contact.replace(/(\d{3})\d*(\d{3})/, '$1****$2');
            }
        }

        function showError(message) {
            let errorAlert = document.querySelector('.alert-error');
            if (!errorAlert) {
                errorAlert = document.createElement('div');
                errorAlert.className = 'alert alert-error';
                errorAlert.innerHTML = '<i class="fas fa-exclamation-circle"></i><span></span>';
                document.querySelector('.warning-box').after(errorAlert);
            }
            errorAlert.querySelector('span').textContent = message;
            errorAlert.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }

        function showSuccess(message) {
            let successAlert = document.querySelector('.alert-success');
            if (!successAlert) {
                successAlert = document.createElement('div');
                successAlert.className = 'alert alert-success';
                successAlert.innerHTML = '<i class="fas fa-check-circle"></i><span></span>';
                document.querySelector('.warning-box').after(successAlert);
            }
            successAlert.querySelector('span').textContent = message;
            successAlert.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
            setTimeout(() => {
                if (successAlert && successAlert.parentNode) {
                    successAlert.parentNode.removeChild(successAlert);
                }
            }, 3000);
        }

        // Language switching
        document.addEventListener('DOMContentLoaded', function() {
            const langButtons = document.querySelectorAll('.lang-btn');
            langButtons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const lang = this.href.includes('lang=ar') ? 'ar' : 'en';
                    document.cookie = `locale=${lang}; path=/; max-age=31536000`;
                    document.documentElement.setAttribute('lang', lang);
                    document.documentElement.setAttribute('dir', lang === 'ar' ? 'rtl' : 'ltr');
                    window.location.href = this.href;
                });
            });

            const codeInput = document.getElementById('verification_code');
            if (codeInput) {
                codeInput.addEventListener('input', function(e) {
                    this.value = this.value.replace(/\D/g, '');
                    if (this.value.length === 4) {
                        // Optional: auto-submit the form
                        // document.getElementById('deleteForm').submit();
                    }
                });
            }
        });

        function adjustViewport() {
            const vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        }

        window.addEventListener('resize', adjustViewport);
        adjustViewport();

        document.getElementById('phone').addEventListener('input', function() {
            const value = this.value.trim();
            const phonePattern = /^[+]?[\d\s\-\(\)]{8,20}$/;

            if (value && !phonePattern.test(value)) {
                this.style.borderColor = '#ef4444';
                this.setCustomValidity('Please enter a valid phone number');
            } else {
                this.style.borderColor = '#e5e7eb';
                this.setCustomValidity('');
            }
        });
    </script>
</body>

</html>
