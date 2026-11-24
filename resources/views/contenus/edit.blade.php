@extends('layouts.app')

@section('titre', 'Modifier le contenu')

@section('contenu')
    <div class="form-conteneur">
        <h1>Modifier la publication</h1>

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

        <form method="POST" action="{{ route('contenus.update', $publication) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="champ">
                <label for="titre">Titre du contenu * (min. 10 caractères)</label>
                <input type="text" name="titre" id="titre"
                    value="{{ old('titre', $publication->titre) }}" required
                    class="@error('titre') champ-erreur @enderror">
                @error('titre')
                    <span class="erreur-champ">{{ $message }}</span>
                @enderror
            </div>

            <div class="champ">
                <label for="categorie_id">Catégorie *</label>
                <select name="categorie_id" id="categorie_id" required class="@error('categorie_id') champ-erreur @enderror">
                    @foreach ($categories as $categorie)
                        <option value="{{ $categorie->id }}"
                            {{ old('categorie_id', $publication->categorie_id) == $categorie->id ? 'selected' : '' }}>
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
                    class="@error('contenu') champ-erreur @enderror">{{ old('contenu', $publication->contenu) }}</textarea>
                @error('contenu')
                    <span class="erreur-champ">{{ $message }}</span>
                @enderror
            </div>

            {{-- ============================================================
                 SEMAINE 9 — PRÉVISUALISATION DE L'IMAGE ACTUELLE
                 Si une image existe déjà, on l'affiche avant le champ de remplacement.
                 Si l'utilisateur ne téléverse pas de nouvelle image, l'ancienne est conservée.
                 ============================================================ --}}
            <div class="champ">
                <label for="image">Image de couverture (laissez vide pour conserver l'actuelle — max 2 Mo)</label>

                @if ($publication->image)
                    <div class="apercu-media" style="margin-bottom: 10px;">
                        <p><strong>Image actuelle :</strong></p>
                        <img src="{{ Storage::disk('public')->url($publication->image) }}"
                            alt="Image actuelle de {{ $publication->titre }}"
                            style="max-width: 300px; max-height: 200px; border-radius: 6px; border: 1px solid #ddd;">
                    </div>
                @endif

                <input type="file" name="image" id="image" accept="image/*"
                    class="@error('image') champ-erreur @enderror">
                @error('image')
                    <span class="erreur-champ">{{ $message }}</span>
                @enderror
            </div>

            {{-- ============================================================
                 SEMAINE 9 — PRÉVISUALISATION DU FICHIER MULTIMÉDIA ACTUEL
                 Affiche un lecteur audio ou vidéo selon le type_media enregistré.
                 ============================================================ --}}
            <div class="champ">
                <label for="fichier_media">Fichier multimédia (laissez vide pour conserver l'actuel — max 20 Mo)</label>

                @if ($publication->fichier_media)
                    <div class="apercu-media" style="margin-bottom: 10px;">
                        <p><strong>Fichier actuel ({{ $publication->type_media }}) :</strong></p>
                        @if ($publication->type_media === 'audio')
                            <audio controls style="width: 100%;">
                                <source src="{{ Storage::disk('public')->url($publication->fichier_media) }}">
                                Votre navigateur ne supporte pas la lecture audio.
                            </audio>
                        @elseif ($publication->type_media === 'video')
                            <video controls style="max-width: 300px;">
                                <source src="{{ Storage::disk('public')->url($publication->fichier_media) }}">
                                Votre navigateur ne supporte pas la lecture vidéo.
                            </video>
                        @endif
                    </div>
                @endif

                <input type="file" name="fichier_media" id="fichier_media" accept=".mp3,.mp4,.wav"
                    class="@error('fichier_media') champ-erreur @enderror">
                @error('fichier_media')
                    <span class="erreur-champ">{{ $message }}</span>
                @enderror
            </div>

            {{-- Document privé --}}
            <div class="champ">
                <label for="document_prive">Document privé (laissez vide pour conserver l'actuel — PDF, max 10 Mo)</label>

                @if ($publication->document_prive)
                    <p style="color: #555; font-size: 0.9rem;">
                        ✅ Un document privé est actuellement associé à cette publication.
                        <a href="{{ route('contenus.document', $publication) }}">Télécharger le document actuel</a>
                    </p>
                @endif

                <input type="file" name="document_prive" id="document_prive" accept=".pdf"
                    class="@error('document_prive') champ-erreur @enderror">
                @error('document_prive')
                    <span class="erreur-champ">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primaire">Enregistrer les modifications</button>
                <a href="{{ route('contenus.show', $publication) }}" class="btn btn-secondaire">Annuler</a>
            </div>
        </form>
    </div>
@endsection
