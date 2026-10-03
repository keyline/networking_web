<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Upload too large | Net-Works</title>
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:22px;background:#f5f7fb;color:#172640;font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}.card{width:min(520px,100%);padding:30px;border:1px solid #e1e7f0;border-radius:16px;background:#fff;box-shadow:0 16px 45px rgba(28,49,84,.08);text-align:center}.icon{display:grid;place-items:center;width:56px;height:56px;margin:0 auto 16px;border-radius:50%;background:#fff0f1;color:#c93642;font-size:25px;font-weight:800}.card h1{margin:0 0 10px;font-size:25px}.card p{margin:0;color:#687990;line-height:1.6}.button{display:inline-flex;align-items:center;justify-content:center;margin-top:22px;padding:12px 18px;border-radius:9px;background:#397ef6;color:#fff;text-decoration:none;font-weight:800}
    </style>
</head>
<body>
<main class="card">
    <div class="icon">!</div>
    <h1>That upload is too large</h1>
    <p>{{ $joinRequest ? 'Please return to the registration form and choose a smaller JPG, PNG or WebP photo. Large supported photos will be optimized automatically.' : 'Please choose a smaller file and try again.' }}</p>
    <a class="button" href="{{ $joinRequest ? route('join.create') : url('/') }}">{{ $joinRequest ? 'Return to registration' : 'Return home' }}</a>
</main>
</body>
</html>
