<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - CAN 2025 Kinshasa</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --primary-light: #3b82f6;
            --success: #10b981;
            --error: #ef4444;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-20px, 20px); }
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 3rem 2.5rem;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .login-logo {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            display: inline-block;
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .login-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }

        .login-subtitle {
            font-size: 0.95rem;
            color: var(--gray-500);
            font-weight: 400;
        }

        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9rem;
            font-weight: 500;
            animation: slideDown 0.4s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #6ee7b7;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
            transition: color 0.2s;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1rem;
            font-size: 0.95rem;
            border: 2px solid var(--gray-200);
            border-radius: 10px;
            transition: all 0.3s;
            font-family: inherit;
            background: var(--gray-50);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-input.error {
            border-color: var(--error);
            background: #fef2f2;
        }

        .form-input::placeholder {
            color: var(--gray-400);
        }

        .form-error {
            display: block;
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: var(--error);
            font-weight: 500;
        }

        .form-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .form-checkbox input[type="checkbox"] {
            width: 1.125rem;
            height: 1.125rem;
            border: 2px solid var(--gray-300);
            border-radius: 5px;
            cursor: pointer;
            accent-color: var(--primary);
        }

        .form-checkbox label {
            font-size: 0.875rem;
            color: var(--gray-600);
            cursor: pointer;
            user-select: none;
        }

        .btn {
            width: 100%;
            padding: 0.875rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-family: inherit;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .forgot-password {
            text-align: center;
            margin-top: 1.5rem;
        }

        .forgot-password a {
            color: var(--primary);
            font-size: 0.875rem;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .forgot-password a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .footer {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-200);
            text-align: center;
        }

        .footer p {
            font-size: 0.75rem;
            color: var(--gray-400);
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 2rem 1.5rem;
            }

            .login-title {
                font-size: 1.5rem;
            }

            .login-logo {
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <div class="login-logo">🦁</div>
                <h1 class="login-title">CAN 2025 Kinshasa</h1>
                <p class="login-subtitle">Connectez-vous à votre espace admin</p>
            </div>

            <!-- Alerts -->
            <div class="alert alert-success" style="display: none;" id="successAlert">
                <span>✓</span>
                <span id="successMessage"></span>
            </div>

            <div class="alert alert-error" style="display: none;" id="errorAlert">
                <span>⚠</span>
                <span id="errorMessage"></span>
            </div>

            <!-- Form -->
            <form method="POST" action="/login" id="loginForm">
                <!-- Email -->
                <div class="form-group">
                    <label for="email" class="form-label">Adresse email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        class="form-input"
                        placeholder="admin@can2025.cd"
                        required
                        autofocus
                        autocomplete="username"
                    >
                    <span class="form-error" id="emailError" style="display: none;"></span>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="form-input"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    >
                    <span class="form-error" id="passwordError" style="display: none;"></span>
                </div>

                <!-- Remember Me -->
                <div class="form-checkbox">
                    <input id="remember_me" type="checkbox" name="remember">
                    <label for="remember_me">Se souvenir de moi</label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary">
                    <span>Se connecter</span>
                    <span>→</span>
                </button>

                <!-- Forgot Password -->
                <div class="forgot-password">
                    <a href="/password/reset">Mot de passe oublié ?</a>
                </div>
            </form>

            <!-- Footer -->
            <div class="footer">
                <p>© 2024 CAN 2025 Kinshasa. Tous droits réservés.</p>
            </div>
        </div>
    </div>

    <script>
        // Animation sur focus des inputs
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                const label = this.parentElement.querySelector('.form-label');
                if (label) {
                    label.style.color = 'var(--primary)';
                }
            });

            input.addEventListener('blur', function() {
                if (!this.value) {
                    const label = this.parentElement.querySelector('.form-label');
                    if (label) {
                        label.style.color = '';
                    }
                }
            });
        });

        // Auto-dismiss alerts après 5 secondes
        function autoDismissAlert(alertId) {
            setTimeout(() => {
                const alert = document.getElementById(alertId);
                if (alert && alert.style.display !== 'none') {
                    alert.style.animation = 'slideUp 0.3s ease-out reverse';
                    setTimeout(() => {
                        alert.style.display = 'none';
                    }, 300);
                }
            }, 5000);
        }

        // Vérifier les alertes au chargement
        if (document.getElementById('successAlert').style.display !== 'none') {
            autoDismissAlert('successAlert');
        }
        if (document.getElementById('errorAlert').style.display !== 'none') {
            autoDismissAlert('errorAlert');
        }
    </script>
</body>
</html>
