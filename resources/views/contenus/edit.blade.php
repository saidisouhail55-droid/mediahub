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

        <form method="POST" action="{{ route('contenus.update', $publication) }}">
            @csrf
            @method('PUT')

            <div class="champ">
                <label for="titre">Titre du contenu * (min. 10 caractères)</label>
                <input type="text" name="titre" id="titre" value="{{ old('titre', $publication->titre) }}" required class="@error('titre') champ-erreur @enderror">
                @error('titre')
                    <span class="erreur-champ">{{ $message }}</span>
                @enderror
            </div>

            <div class="champ">
                <label for="categorie_id">Catégorie *</label>
                <select name="categorie_id" id="categorie_id" required class="@error('categorie_id') champ-erreur @enderror">
                    @foreach ($categories as $categorie)
                        <option value="{{ $categorie->id }}" {{ old('categorie_id', $publication->categorie_id) == $categorie->id ? 'selected' : '' }}>
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
                <textarea name="contenu" id="contenu" rows="6" required class="@error('contenu') champ-erreur @enderror">{{ old('contenu', $publication->contenu) }}</textarea>
                @error('contenu')
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
