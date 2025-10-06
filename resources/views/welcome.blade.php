<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Best Time to Visit</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
            body {
      margin: 0;
      font-family: Arial, sans-serif;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
      color: white;
    }

    .container {
      width: 100%;
      max-width: 600px;
      padding: 2rem;
      border-radius: 20px;
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(12px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.7);
      border: 1px solid rgba(255,255,255,0.15);
    }

    h1 {
      text-align: center;
      font-size: 2rem;
      font-weight: bold;
      margin-bottom: 1.5rem;
      background: linear-gradient(to right, #ff4da6, #b266ff, #4da6ff);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 1rem;
    }

    label {
      font-weight: bold;
      margin-bottom: 0.25rem;
      font-size: 0.9rem;
    }

    input[type="text"], input[type="date"] {
      width: 100%;
      padding: 0.75rem;
      border-radius: 10px;
      border: 1px solid #444;
      background: #1e1e1e;
      color: white;
      font-size: 1rem;
      outline: none;
    }

    button {
      padding: 1rem;
      border: none;
      border-radius: 10px;
      font-size: 1.1rem;
      font-weight: bold;
      cursor: pointer;
      background: linear-gradient(to right, #7e22ce, #ec4899, #ef4444);
      color: white;
      transition: 0.3s;
    }

    button:hover {
      opacity: 0.9;
    }

.result {
  margin-top: 1.5rem;
  padding: 2rem;
  border-radius: 20px;
  background: linear-gradient(145deg, #1c1c1c, #111); /* subtle gradient */
  border: 1px solid rgba(255, 255, 255, 0.08);
  box-shadow: 0 12px 35px rgba(0, 0, 0, 0.8),
              inset 0 1px 0 rgba(255, 255, 255, 0.05); /* premium shadow */
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.result:hover {
  transform: translateY(-5px);
  box-shadow: 0 18px 45px rgba(0, 0, 0, 0.9);
}

.result h2 {
  font-size: 1.6rem;
  font-weight: 700;
  margin-bottom: 1.2rem;
  background: linear-gradient(90deg, #ff4da6, #a855f7, #4da6ff);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  letter-spacing: 0.5px;
}

.recommendation {
  font-size: 1.1rem;
  padding: 0.75rem 1rem;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.05);
  border-left: 4px solid #a855f7;
  margin-bottom: 1.2rem;
  font-weight: 500;
}

.reason {
  margin-top: 1rem;
}

.reason strong {
  display: block;
  font-size: 1.05rem;
  margin-bottom: 0.5rem;
  color: #c084fc;
}

.reason ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.reason ul li {
  position: relative;
  margin-bottom: 0.8rem;
  padding-left: 1.8rem;
  line-height: 1.5;
  color: #ddd;
  font-size: 0.95rem;
}

.reason ul li::before {
  content: "✔";
  position: absolute;
  left: 0;
  top: 0;
  color: #22c55e;
  font-size: 1rem;
}


  
    .confidence { margin-top: 1rem; font-weight: bold; color: #f472b6; }

    .error {
      margin-top: 1rem;
      padding: 1rem;
      border-radius: 10px;
      background: rgba(255, 0, 0, 0.12);
      color: #ffb3b3;
      border: 1px solid rgba(255,0,0,0.2);
    }

    pre.debug {
      margin-top: 1rem;
      padding: 1rem;
      border-radius: 10px;
      background: rgba(0,0,0,0.6);
      color: #ddd;
      max-height: 300px;
      overflow: auto;
      font-size: 0.85rem;
    }

    .back-btn {
  margin-top: 1.5rem;
  text-align: center;
}

.back-btn a {
  display: inline-block;
  padding: 0.7rem 1.5rem;
  border-radius: 12px;
  font-size: 0.95rem;
  font-weight: 600;
  color: white;
  text-decoration: none;
  background: linear-gradient(90deg, #3b82f6, #8b5cf6);
  box-shadow: 0 6px 15px rgba(0, 0, 0, 0.6);
  transition: all 0.3s ease;
}

.back-btn a:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.8);
  opacity: 0.95;
}


.weather-summary {
  margin-top: 0.75rem;
  padding: 0.9rem;
  border-radius: 12px;
  background: linear-gradient(to right, rgba(147,197,253,0.15), rgba(196,181,253,0.1));
  border: 1px solid rgba(147,197,253,0.3);
  font-size: 0.95rem;
  color: #dbeafe;
  box-shadow: 0 2px 6px rgba(0,0,0,0.3);
}
.weather-summary strong {
  color: #93c5fd;
}

.weather-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: 0.8rem;
}

.weather-tags .tag {
  display: flex;
  align-items: center;
  gap: 0.3rem;
  background: rgba(147, 197, 253, 0.15);
  border: 1px solid rgba(147, 197, 253, 0.3);
  border-radius: 9999px;
  padding: 0.4rem 0.8rem;
  font-size: 0.9rem;
  color: #e0f2fe;
  box-shadow: 0 1px 3px rgba(0,0,0,0.2);
  transition: all 0.2s ease;
}

.weather-tags .tag:hover {
  transform: scale(1.05);
  background: rgba(147, 197, 253, 0.25);
}


    </style>
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
