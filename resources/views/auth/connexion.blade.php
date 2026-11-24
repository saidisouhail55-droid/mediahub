@extends('layouts.app')

@section('titre', 'Connexion')

@section('contenu')
    <div class="form-conteneur" style="max-width: 450px; margin: 0 auto;">
        <h1>Connexion à MediaHub</h1>
        <p style="color: #6b7280; margin-bottom: 1.5rem;">Connectez-vous pour accéder à votre espace auteur.</p>

        @if ($errors->any())
            <div class="alertes-erreurs">
                <strong>Erreur de connexion :</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('connexion') }}">
            @csrf

            <div class="champ">
                <label for="email">Adresse e-mail *</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus placeholder="votre@email.com" class="@error('email') champ-erreur @enderror">
                @error('email')
                    <span class="erreur-champ">{{ $message }}</span>
                @enderror
            </div>

            <div class="champ">
                <label for="password">Mot de passe *</label>
                <input type="password" name="password" id="password" required placeholder="••••••••" class="@error('password') champ-erreur @enderror">
                @error('password')
                    <span class="erreur-champ">{{ $message }}</span>
                @enderror
            </div>

            <div class="champ" style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" name="remember" id="remember" style="width: auto;">
                <label for="remember" style="margin-bottom: 0; font-weight: normal; cursor: pointer;">Se souvenir de moi</label>
            </div>

            <div class="form-actions" style="flex-direction: column; gap: 1rem;">
                <button type="submit" class="btn btn-primaire" style="width: 100%; text-align: center;">Se connecter</button>
                <p style="text-align: center; color: #4b5563; font-size: 0.95rem;">
                    Pas encore de compte ? <a href="{{ route('inscription') }}" style="color: #2563eb; font-weight: 500;">Inscrivez-vous ici</a>
                </p>
            </div>
        </form>
    </div>
@endsection
