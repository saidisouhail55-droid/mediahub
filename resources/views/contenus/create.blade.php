@extends('layouts.app')

@section('titre', 'Nouveau contenu')

@section('contenu')
    <div class="form-conteneur">
        <h1>Créer une nouvelle publication</h1>

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

        {{--
            SEMAINE 9 — enctype="multipart/form-data"
            Ce paramètre est OBLIGATOIRE pour les formulaires qui envoient des fichiers.
            Sans lui, le navigateur n'envoie pas le contenu binaire des fichiers,
            uniquement leur nom — les fichiers ne seraient jamais reçus par Laravel.
        --}}
        <form method="POST" action="{{ route('contenus.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="champ">
                <label for="titre">Titre du contenu * (min. 10 caractères)</label>
                <input type="text" name="titre" id="titre" value="{{ old('titre') }}" required
                    placeholder="Ex: Mon documentaire vidéo captivant"
                    class="@error('titre') champ-erreur @enderror">
                @error('titre')
                    <span class="erreur-champ">{{ $message }}</span>
                @enderror
            </div>

            <div class="champ">
                <label for="categorie_id">Catégorie *</label>
                <select name="categorie_id" id="categorie_id" required class="@error('categorie_id') champ-erreur @enderror">
                    <option value="">-- Sélectionnez une catégorie --</option>
                    @foreach ($categories as $categorie)
                        <option value="{{ $categorie->id }}" {{ old('categorie_id') == $categorie->id ? 'selected' : '' }}>
                            {{ $categorie->nom }}
                        </option>
                    @endforeach
                </select>
                @error('categorie_id')
                    <span class="erreur-champ">{{ $message }}</span>
                @enderror
            </div>

            <div class="champ">
                <label for="contenu">Contenu / Description * (min. 20 caractères)</label>
                <textarea name="contenu" id="contenu" rows="6" required
                    placeholder="Détaillez le contenu multimédia (au moins 20 caractères)..."
                    class="@error('contenu') champ-erreur @enderror">{{ old('contenu') }}</textarea>
                @error('contenu')
                    <span class="erreur-champ">{{ $message }}</span>
                @enderror
            </div>

            {{-- ============================================================
                 SEMAINE 9 — CHAMP IMAGE
                 accept="image/*" : guide le navigateur à n'afficher que les images
                 dans le sélecteur de fichiers, mais n'est PAS une sécurité
                 suffisante. La vraie validation est effectuée côté serveur
                 par la règle 'image' de StorePublicationRequest.
                 ============================================================ --}}
            <div class="champ">
                <label for="image">Image de couverture (optionnelle — JPEG, PNG, WebP — max 2 Mo)</label>
                <input type="file" name="image" id="image" accept="image/*"
                    class="@error('image') champ-erreur @enderror">
                @error('image')
                    <span class="erreur-champ">{{ $message }}</span>
                @enderror
            </div>

            {{-- ============================================================
                 SEMAINE 9 — CHAMP FICHIER MULTIMÉDIA (audio ou vidéo)
                 accept=".mp3,.mp4,.wav" : filtre l'interface de sélection.
                 La validation réelle se fait avec 'mimes:mp3,mp4,wav' côté serveur.
                 ============================================================ --}}
            <div class="champ">
                <label for="fichier_media">Fichier multimédia (optionnel — MP3, MP4, WAV — max 20 Mo)</label>
                <input type="file" name="fichier_media" id="fichier_media" accept=".mp3,.mp4,.wav"
                    class="@error('fichier_media') champ-erreur @enderror">
                @error('fichier_media')
                    <span class="erreur-champ">{{ $message }}</span>
                @enderror
            </div>

            {{-- ============================================================
                 SEMAINE 9 — DOCUMENT PRIVÉ (PDF uniquement, disque local)
                 Ce fichier sera stocké dans storage/app/private/ et ne sera
                 jamais accessible depuis le web sans vérification des droits.
                 ============================================================ --}}
            <div class="champ">
                <label for="document_prive">Document privé (optionnel — PDF uniquement — max 10 Mo)</label>
                <input type="file" name="document_prive" id="document_prive" accept=".pdf"
                    class="@error('document_prive') champ-erreur @enderror">
                <small style="color: #666;">Ce document sera uniquement accessible à vous et aux administrateurs.</small>
                @error('document_prive')
                    <span class="erreur-champ">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primaire">Publier le contenu</button>
                <a href="{{ route('contenus.index') }}" class="btn btn-secondaire">Annuler</a>
            </div>
        </form>
    </div>
@endsection
