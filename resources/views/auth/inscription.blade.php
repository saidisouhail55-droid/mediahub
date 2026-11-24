@extends('layouts.app')

@section('titre', 'Inscription')

@section('contenu')
    <div class="form-conteneur" style="max-width: 500px; margin: 0 auto;">
        <h1>Créer un compte MediaHub</h1>
        <p style="color: #6b7280; margin-bottom: 1.5rem;">Inscrivez-vous pour publier et partager vos contenus multimédias.</p>

        @if ($errors->any())
            <div class="alertes-erreurs">
                <strong>Veuillez corriger les erreurs ci-dessous :</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('inscription') }}">
            @csrf

            <div class="champ">
                <label for="name">Nom complet *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus placeholder="Ex: Souhail Saidi" class="@error('name') champ-erreur @enderror">
                @error('name')
                    <span class="erreur-champ">{{ $message }}</span>
                @enderror
            </div>

            <div class="champ">
                <label for="email">Adresse e-mail *</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="Ex: nom@domaine.com" class="@error('email') champ-erreur @enderror">
                @error('email')
                    <span class="erreur-champ">{{ $message }}</span>
                @enderror
            </div>

            <div class="champ">
                <label for="password">Mot de passe * (min. 8 caractères)</label>
                <input type="password" name="password" id="password" required placeholder="••••••••" class="@error('password') champ-erreur @enderror">
                @error('password')
                    <span class="erreur-champ">{{ $message }}</span>
                @enderror
            </div>

            <div class="champ">
                <label for="password_confirmation">Confirmer le mot de passe *</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required placeholder="••••••••">
            </div>

            <div class="form-actions" style="flex-direction: column; gap: 1rem;">
                <button type="submit" class="btn btn-primaire" style="width: 100%; text-align: center;">S'inscrire</button>
                <p style="text-align: center; color: #4b5563; font-size: 0.95rem;">
                    Vous possédez déjà un compte ? <a href="{{ route('connexion') }}" style="color: #2563eb; font-weight: 500;">Connectez-vous</a>
                </p>
            </div>
        </form>
    </div>
@endsection
