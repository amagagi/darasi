<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Réinitialiser le mot de passe - DARASI HUB</title>
    <!-- Tailwind CSS via CDN pour le test rapide (si tu as déjà compilé ton CSS, tu peux l'enlever) -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-gray-800 p-8 rounded-lg shadow-xl border border-gray-700">
        <div class="text-center mb-6">
            <img src="{{ asset('logo.png') }}" alt="DARASI HUB" class="h-12 mx-auto mb-4">
            <h2 class="text-2xl font-bold">Nouveau mot de passe</h2>
        </div>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <!-- Token caché (obligatoire pour la sécurité) -->
            <input type="hidden" name="token" value="{{ $token }}">

            <!-- Email (lecture seule) -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Adresse email</label>
                <input type="email" name="email" value="{{ request()->email }}" readonly class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 text-gray-300 focus:outline-none focus:border-indigo-500">
                @error('email')
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                @enderror
            </div>

            <!-- Nouveau mot de passe -->
            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Nouveau mot de passe</label>
                <input type="password" name="password" required class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 focus:outline-none focus:border-indigo-500">
                @error('password')
                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                @enderror
            </div>

            <!-- Confirmation mot de passe -->
            <div class="mb-6">
                <label class="block text-sm font-medium mb-1">Confirmer le mot de passe</label>
                <input type="password" name="password_confirmation" required class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 focus:outline-none focus:border-indigo-500">
            </div>

            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2 px-4 rounded transition duration-200">
                Réinitialiser le mot de passe
            </button>
        </form>
    </div>
</body>
</html>