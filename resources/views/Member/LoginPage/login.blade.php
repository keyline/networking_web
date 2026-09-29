<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Login | Net-Works</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; background: #f3f6fb; color: #17233c; font-family: Inter, Arial, sans-serif; }
        .card { width: 100%; max-width: 420px; background: #fff; border: 1px solid #e5eaf2; border-radius: 14px; padding: 30px; box-shadow: 0 14px 35px rgba(23, 35, 60, .09); }
        .brand { color: #397ef6; font-size: 14px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        h1 { margin: 8px 0 6px; font-size: 25px; }
        .subtitle { margin: 0 0 24px; color: #6c7890; font-size: 14px; line-height: 1.55; }
        label { display: block; margin-bottom: 7px; font-size: 13px; font-weight: 700; }
        input { width: 100%; height: 46px; padding: 0 13px; border: 1px solid #dce3ee; border-radius: 8px; font-size: 15px; outline: none; }
        input:focus { border-color: #397ef6; box-shadow: 0 0 0 3px rgba(57, 126, 246, .12); }
        .otp { text-align: center; letter-spacing: .5em; font-size: 22px; font-weight: 700; }
        button { width: 100%; height: 46px; margin-top: 18px; border: 0; border-radius: 8px; background: #397ef6; color: #fff; font-size: 15px; font-weight: 700; cursor: pointer; }
        .secondary { margin-top: 10px; border: 1px solid #dce3ee; background: #fff; color: #397ef6; }
        .message { margin-bottom: 18px; padding: 11px 13px; border-radius: 8px; font-size: 13px; }
        .success { background: #eaf8f1; color: #17734a; }
        .error { background: #fff0f0; color: #b42318; }
        .error ul { margin: 0; padding-left: 18px; }
        .email { margin-bottom: 18px; padding: 10px 12px; background: #f6f8fc; border-radius: 7px; color: #4f5d75; font-size: 14px; }
    </style>
</head>
<body>
    <main class="card">
        <div class="brand">Net-Works</div>
        <h1>{{ session('member_otp_email') ? 'Verify your email' : 'Member login' }}</h1>
        <p class="subtitle">{{ session('member_otp_email') ? 'Enter the one-time password sent to your registered email.' : 'Buyer, seller and guest members can sign in securely without a password.' }}</p>

        @if (session('success'))
            <div class="message success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="message error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        @if (session('member_otp_email'))
            <div class="email">OTP sent to <strong>{{ session('member_otp_email') }}</strong></div>
            <form action="{{ route('member.verify-otp') }}" method="POST">
                @csrf
                <input type="hidden" name="email" value="{{ session('member_otp_email') }}">
                <label for="otp">4-digit OTP</label>
                <input id="otp" class="otp" name="otp" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="4" pattern="[0-9]{4}" required autofocus>
                <button type="submit">Verify and sign in</button>
            </form>
            <form action="{{ route('member.resend-otp') }}" method="POST">
                @csrf
                <input type="hidden" name="email" value="{{ session('member_otp_email') }}">
                <button type="submit" class="secondary">Resend OTP</button>
            </form>
        @else
            <form action="{{ route('member.send-otp') }}" method="POST">
                @csrf
                <label for="email">Registered email address</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" placeholder="name@example.com" required autofocus>
                <button type="submit">Send email OTP</button>
            </form>
        @endif
    </main>
</body>
</html>
