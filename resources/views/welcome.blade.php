<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NewsFlow</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ url('/favicon.png') }}" type="image/png">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Styles -->
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --accent: #d4af37;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #94a3b8;
            --success: #10b981;
            --error: #ef4444;
            --border-radius: 16px;
            --shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);

            /* Dark mode variables */
            --bg-dark: #0f172a;
            --text-dark: #e2e8f0;
            --card-dark: rgba(30, 41, 59, 0.8);
            --gray-dark: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            color: var(--dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
            line-height: 1.5;
            transition: var(--transition);
        }

        body.dark-mode {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: var(--text-dark);
        }

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
        }

        .header {
            text-align: center;
            margin-bottom: 3rem;
            max-width: 800px;
        }

        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            border-radius: 20px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: var(--transition);
        }

        .dark-mode .logo {
            background: var(--card-dark);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .logo img {
            width: 50px;
            height: 50px;
        }

        h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--dark);
            position: relative;
            display: inline-block;
            transition: var(--transition);
        }

        .dark-mode h1 {
            color: var(--text-dark);
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            border-radius: 3px;
        }

        .subtitle {
            font-size: 1.25rem;
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto;
            transition: var(--transition);
        }

        .dark-mode .subtitle {
            color: var(--gray-dark);
        }

        /* Luxury Clock Styles */
        .luxury-clock {
            margin: 2rem 0;
            font-family: 'Playfair Display', serif;
            font-weight: 600;
            color: var(--dark);
            text-align: center;
            position: relative;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            min-width: 280px;
            transition: var(--transition);
        }

        .dark-mode .luxury-clock {
            background: rgba(30, 41, 59, 0.7);
            color: var(--text-dark);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .clock-time {
            font-size: 2.5rem;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .clock-date {
            font-size: 1rem;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-family: 'Inter', sans-serif;
            transition: var(--transition);
        }

        .dark-mode .clock-date {
            color: var(--gray-dark);
        }

        .clock-weather {
            position: absolute;
            top: -12px;
            right: 20px;
            background: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
        }

        .dark-mode .clock-weather {
            background: var(--card-dark);
        }

        .clock-weather i {
            color: var(--accent);
        }

        .clock-location {
            position: absolute;
            bottom: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            padding: 0.25rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
        }

        .dark-mode .clock-location {
            background: var(--card-dark);
        }

        .clock-location i {
            color: var(--primary);
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            width: 100%;
            max-width: 480px;
            transition: var(--transition);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
        }

        .dark-mode .card {
            background: rgba(30, 41, 59, 0.95);
            border-color: rgba(255, 255, 255, 0.1);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .card-content {
            padding: 2.5rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .auth-options {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            font-weight: 500;
            font-size: 1rem;
            transition: var(--transition);
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: white;
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .dark-mode .btn-secondary {
            background: var(--card-dark);
            color: var(--text-dark);
            border-color: var(--primary);
        }

        .btn-secondary:hover {
            background: rgba(37, 99, 235, 0.05);
            transform: translateY(-2px);
        }

        .dark-mode .btn-secondary:hover {
            background: rgba(37, 99, 235, 0.1);
        }

        .btn i {
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }

        .footer {
            margin-top: 3rem;
            text-align: center;
            color: var(--gray);
            font-size: 0.875rem;
            transition: var(--transition);
        }

        .dark-mode .footer {
            color: var(--gray-dark);
        }

        /* Dark Mode Toggle */
        .dark-mode-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border: none;
            transition: var(--transition);
        }

        .dark-mode .dark-mode-toggle {
            background: rgba(30, 41, 59, 0.9);
            color: var(--text-dark);
        }

        .dark-mode-toggle i {
            font-size: 1.2rem;
            transition: var(--transition);
        }

        /* Loading State */
        .loading {
            color: var(--gray);
            font-style: italic;
        }

        .dark-mode .loading {
            color: var(--gray-dark);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 1.5rem;
            }

            h1 {
                font-size: 2rem;
            }

            .subtitle {
                font-size: 1.1rem;
            }

            .card-content {
                padding: 2rem;
            }

            .clock-time {
                font-size: 2rem;
            }
        }

        @media (max-width: 480px) {
            .card-content {
                padding: 1.5rem;
            }

            .btn {
                padding: 0.875rem 1.25rem;
            }

            .luxury-clock {
                padding: 1rem;
                min-width: 240px;
            }

            .clock-time {
                font-size: 1.75rem;
            }

            .dark-mode-toggle {
                top: 10px;
                right: 10px;
                width: 36px;
                height: 36px;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate {
            animation: fadeIn 0.6s ease-out forwards;
        }
    </style>
</head>

<body>
    <!-- Dark Mode Toggle -->
    <button class="dark-mode-toggle animate" id="darkModeToggle" style="animation-delay: 0.1s;">
        <i class="fas fa-moon"></i>
    </button>

    <div class="container">
        <div class="header animate">
            <div class="logo animate" style="animation-delay: 0.1s;">
                <img src="{{ url('/favicon.png') }}" alt="NewsFlow Logo">
            </div>
            <h1 class="animate" style="animation-delay: 0.2s;">NewsFlow</h1>
            <p class="subtitle animate" style="animation-delay: 0.3s;">
                Curated intelligence for discerning professionals
            </p>

            <!-- Luxury Clock -->
            <div class="luxury-clock animate" style="animation-delay: 0.35s;">
                <div class="clock-weather">
                    <i class="fas fa-cloud"></i>
                    <span id="weather-temp" class="loading">Loading...</span>
                </div>
                <div class="clock-time" id="clock-time">00:00:00</div>
                <div class="clock-date" id="clock-date">Loading date...</div>
                <div class="clock-location">
                    <i class="fas fa-map-marker-alt"></i>
                    <span id="clock-location" class="loading">Detecting location...</span>
                </div>
            </div>
        </div>

        <div class="card animate" style="animation-delay: 0.4s;">
            <div class="card-content">
                <div class="auth-options">
                    <a href="/login" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Employee Login
                    </a>
                    <a href="/login?admin=1" class="btn btn-secondary">
                        <i class="fas fa-lock"></i> Admin Portal
                    </a>

                    @if(request()->has('admin_key') && request()->admin_key === config('app.admin_secret_key'))
                    <a href="/register" class="btn btn-secondary mt-4" style="background-color: #d4af37; border-color: #d4af37;">
                        <i class="fas fa-user-shield"></i> Admin Registration
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="footer animate" style="animation-delay: 0.5s;">
            <p>© 2025 NewsFlow. All rights reserved.</p>
        </div>
    </div>

    <script>
        // Enhanced button interactions
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('mousedown', () => {
                button.style.transform = 'translateY(1px)';
            });

            button.addEventListener('mouseup', () => {
                button.style.transform = 'translateY(-2px)';
            });

            button.addEventListener('mouseleave', () => {
                button.style.transform = '';
            });
        });

        // Dark Mode Toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        const darkModeIcon = darkModeToggle.querySelector('i');

        // Check for saved user preference or use system preference
        const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
        const currentTheme = localStorage.getItem('theme');

        if (currentTheme === 'dark' || (!currentTheme && prefersDarkScheme.matches)) {
            document.body.classList.add('dark-mode');
            darkModeIcon.classList.replace('fa-moon', 'fa-sun');
        }

        darkModeToggle.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');

            if (document.body.classList.contains('dark-mode')) {
                darkModeIcon.classList.replace('fa-moon', 'fa-sun');
                localStorage.setItem('theme', 'dark');
            } else {
                darkModeIcon.classList.replace('fa-sun', 'fa-moon');
                localStorage.setItem('theme', 'light');
            }
        });

        // Luxury Clock Functionality
        function updateClock() {
            const now = new Date();

            // Time with timezone awareness
            const timeOptions = {
                timeStyle: 'medium',
                hour12: false
            };

            // Get time in user's locale with timezone
            const timeStr = now.toLocaleTimeString(undefined, timeOptions);
            document.getElementById('clock-time').textContent = timeStr;

            // Date with timezone awareness
            const dateOptions = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone
            };

            document.getElementById('clock-date').textContent =
                now.toLocaleDateString(undefined, dateOptions);

            // Update every second
            setTimeout(updateClock, 1000);
        }

        // Get Weather Data from OpenWeatherMap API
        async function getWeather(lat, lon) {
            const apiKey = "e6689a54ecaad9c28ab77067b792a232";
            const weatherTemp = document.getElementById('weather-temp');
            const weatherIcon = document.querySelector('.clock-weather i');
            const locationElement = document.getElementById('clock-location');

            // Show loading state
            weatherTemp.textContent = '...';
            weatherIcon.className = 'fas fa-spinner fa-spin';

            try {
                const response = await fetch(
                    `https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&appid=${apiKey}&units=metric`
                );

                const data = await response.json();

                if (data.cod === 200) { // Success case
                    // Update temperature display
                    weatherTemp.textContent = `${Math.round(data.main.temp)}°C`;

                    // Update weather icon
                    weatherIcon.className = `fas ${getWeatherIcon(data.weather[0].id)}`;

                    // Update location if available
                    if (data.name) {
                        locationElement.textContent = data.name;
                        locationElement.classList.remove('loading');
                    }

                } else {
                    throw new Error(data.message || "Weather data unavailable");
                }

            } catch (error) {
                console.error("Weather error:", error);
                // Fallback display
                weatherTemp.textContent = '--°C';
                weatherIcon.className = 'fas fa-question';
                locationElement.textContent = 'Location found (weather error)';
            }
        }

        function getWeatherIcon(weatherId) {
            // Map weather IDs to Font Awesome icons
            if (weatherId >= 200 && weatherId < 300) return 'fa-bolt'; // Thunderstorm
            if (weatherId >= 300 && weatherId < 600) return 'fa-cloud-rain'; // Drizzle/Rain
            if (weatherId >= 600 && weatherId < 700) return 'fa-snowflake'; // Snow
            if (weatherId >= 700 && weatherId < 800) return 'fa-smog'; // Atmosphere (fog, haze, etc.)
            if (weatherId === 800) return 'fa-sun'; // Clear sky
            return 'fa-cloud'; // Default/cloudy
        }

        // Get User Location and Update Weather
        function getLocation() {
            const locationElement = document.getElementById('clock-location');

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    async (position) => {
                            const {
                                latitude,
                                longitude
                            } = position.coords;

                            try {
                                // Get city name from coordinates using reverse geocoding
                                const response = await fetch(
                                    `https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=${latitude}&longitude=${longitude}&localityLanguage=en`
                                );

                                if (!response.ok) throw new Error('Location data unavailable');

                                const data = await response.json();
                                const city = data.city || data.locality || 'Your location';
                                locationElement.textContent = city;
                                locationElement.classList.remove('loading');

                                // Get weather for this location
                                await getWeather(latitude, longitude);

                            } catch (error) {
                                console.error('Error fetching location:', error);
                                locationElement.textContent = 'Your location';
                                locationElement.classList.remove('loading');
                            }
                        },
                        (error) => {
                            console.error('Geolocation error:', error);
                            locationElement.textContent = 'Your location';
                            locationElement.classList.remove('loading');
                        }
                );
            } else {
                locationElement.textContent = 'Location unavailable';
                locationElement.classList.remove('loading');
            }
        }

        // Initialize all functionality
        function init() {
            updateClock();
            getLocation();

            // Remove loading states after a brief delay
            setTimeout(() => {
                const loadingElements = document.querySelectorAll('.loading');
                loadingElements.forEach(el => {
                    if (el.textContent.includes('Loading')) {
                        el.classList.remove('loading');
                    }
                });
            }, 3000);
        }

        // Start the app
        init();
    </script>
</body>

</html>