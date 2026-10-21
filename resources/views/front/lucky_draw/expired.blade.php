<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lucky Draw Expired</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        .card-custom {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
        }
    </style>
</head>
<body>
    <div class="container px-4 text-center">
        <div class="card-custom p-5 mx-auto" style="max-width: 480px;">
            <div class="mb-4">
                <i class="bi bi-clock-history text-warning display-1"></i>
            </div>
            <h3 class="fw-bold mb-2">Campaign Expired</h3>
            <p class="text-secondary mb-4">{{ $message ?? 'This scratch card link is no longer active.' }}</p>
            <a href="/" class="btn btn-warning rounded-pill px-5 fw-bold shadow">
                Visit Homepage
            </a>
        </div>
    </div>
</body>
</html>
