<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Visit Suggestion</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="container">
        <h1>Best Time to Visit</h1>

        @if (!empty($error))
            <div class="error">
                {{ $error }}
            </div>
        @else
<div class="result">
    <h2>{{ $place }} on {{ \Carbon\Carbon::parse($date)->format('F j, Y') }}</h2>

    <div class="recommendation">
        <strong>Recommended Time:</strong> {{ $suggestion['recommendation'] ?? 'N/A' }}
    </div>

    @if (!empty($suggestion['reason']))
        <div class="reason">
            <strong>Why:</strong>
            <ul style="margin-top:0.5rem; list-style: disc; padding-left:1.5rem;">
                @foreach ((array)$suggestion['reason'] as $r)
                    <li>{{ $r }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="confidence">
        Confidence: {{ $suggestion['confidence'] ?? 0 }}%
    </div>
</div>

        @endif

       <div class="back-btn">
    <a href="{{ url('/') }}">&#8592; Search Again</a>
</div>

    </div>
</body>
</html>
