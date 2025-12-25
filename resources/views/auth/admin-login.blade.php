<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - CAN 2025 Solibra</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f3f4f6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .login-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
            padding: 3rem 2.5rem;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .login-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            background: #1e40af;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08);
        }

        .login-logo-img {
            width: 36px;
            height: 36px;
            object-fit: contain;
        }

        .login-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #243b53;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
        }

        .login-subtitle {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 400;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.875rem;
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border-left: 4px solid #dc2626;
        }

        .alert-icon {
            flex-shrink: 0;
            width: 20px;
            height: 20px;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            transition: all 0.2s;
            font-family: inherit;
            background: white;
        }

        .form-input:focus {
            outline: none;
            border-color: #1e40af;
            box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
        }

        .form-input::placeholder {
            color: #9ca3af;
        }

        .form-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .form-checkbox input[type="checkbox"] {
            width: 1rem;
            height: 1rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            cursor: pointer;
            accent-color: #1e40af;
        }

        .form-checkbox label {
            font-size: 0.875rem;
            color: #4b5563;
            cursor: pointer;
            user-select: none;
        }

        .btn {
            width: 100%;
            padding: 0.875rem 1.5rem;
            font-size: 0.95rem;
            font-weight: 600;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
            background: #1e40af;
            color: white;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.08);
        }

        .btn:hover {
            background: #1d4ed8;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.08);
        }

        .btn:active {
            background: #1e3a8a;
        }

        .footer {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }

        .footer p {
            font-size: 0.75rem;
            color: #9ca3af;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 2rem 1.5rem;
            }

            .login-title {
                font-size: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">

            <!-- Header -->
            <div class="login-header">
                <div class="login-logo">
                    <img
                        src="/images/logo_solibra.png"
                        alt="Logo Solibra"
                        class="login-logo-img"
                    />
                </div>

                <h1 class="login-title">CAN 2025 Solibra</h1>
                <p class="login-subtitle">Espace d'administration</p>
            </div>

            <!-- Error Alert -->
            @if($errors->any())
            <div class="alert alert-error">
                <svg class="alert-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Form -->
            <form method="POST" action="{{ route('admin.login') }}">
                @csrf

                <div class="form-group">
                    <label for="email" class="form-label">Adresse email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="form-input"
                        placeholder="admin@can2025.cd"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="form-input"
                        placeholder="••••••••"
                        required
                    >
                </div>

                <div class="form-checkbox">
                    <input id="remember" type="checkbox" name="remember">
                    <label for="remember">Se souvenir de moi</label>
                </div>

                <button type="submit" class="btn">
                    Se connecter
                </button>
            </form>

            <!-- Footer -->
            <div class="footer">
                <p>© 2025 CAN 2025 Solibra. Tous droits réservés.</p>
            </div>

        </div>
    </div>
</body>
</html>
