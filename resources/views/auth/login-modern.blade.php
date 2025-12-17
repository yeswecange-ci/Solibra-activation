<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - CAN 2025 Solibra</title>
    <link rel="stylesheet" href="{{ asset('css/modern.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <div class="login-logo">
                    🦁
                </div>
                <h1 class="login-title">CAN 2025 Solibra</h1>
                <p class="login-subtitle">Connectez-vous à votre espace admin</p>
            </div>

            <!-- Alerts -->
            @if (session('status'))
                <div class="alert alert-success">
                    <span>✓</span>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error">
                    <span>!</span>
                    <span>
                        @if ($errors->has('email'))
                            {{ $errors->first('email') }}
                        @elseif ($errors->has('password'))
                            {{ $errors->first('password') }}
                        @else
                            Une erreur s'est produite. Veuillez réessayer.
                        @endif
                    </span>
                </div>
            @endif

            <!-- Form -->
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">Adresse email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="form-input @error('email') error @enderror"
                        placeholder="admin@can2025.cd"
                        required
                        autofocus
                        autocomplete="username"
                    >
                    @error('email')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="form-input @error('password') error @enderror"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    >
                    @error('password')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="form-checkbox">
                    <input
                        id="remember_me"
                        type="checkbox"
                        name="remember"
                    >
                    <label for="remember_me">Se souvenir de moi</label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary">
                    <span>Se connecter</span>
                    <span>→</span>
                </button>

                <!-- Forgot Password -->
                @if (Route::has('password.request'))
                    <div style="text-align: center; margin-top: 1.5rem;">
                        <a href="{{ route('password.request') }}" style="color: var(--gray-500); font-size: 0.875rem; text-decoration: none;">
                            Mot de passe oublié ?
                        </a>
                    </div>
                @endif
            </form>

            <!-- Footer -->
            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--gray-200); text-align: center;">
                <p style="font-size: 0.75rem; color: var(--gray-400);">
                    © {{ date('Y') }} CAN 2025 Kinshasa. Tous droits réservés.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Animation sur focus des inputs
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.querySelector('.form-label')?.style.setProperty('color', 'var(--primary)');
            });

            input.addEventListener('blur', function() {
                if (!this.value) {
                    this.parentElement.querySelector('.form-label')?.style.removeProperty('color');
                }
            });
        });

        // Auto-dismiss alerts après 5 secondes
        setTimeout(() => {
            document.querySelectorAll('.alert-success').forEach(alert => {
                alert.style.animation = 'slideUp 0.3s ease-out reverse';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
</body>
</html>
