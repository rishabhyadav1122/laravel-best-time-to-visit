<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Best Time to Visit</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div class="container">
        <h1>Best Time to Visit</h1>

        <form method="POST" action="{{ url('/api/suggest') }}">
            @csrf

            <div>
                <label for="place">Place</label>
                <input type="text" name="place" id="place" value="{{ old('place') }}" required>
            </div>

            <div>
                <label for="date">Date</label>
                <input type="date" name="date" id="date" value="{{ old('date') }}" required>
            </div>

            <button type="submit">Find Best Time</button>
        </form>

        @if ($errors->any())
            <div class="error">
                <ul>
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</body>
</html>
