@extends('layouts.app')

@section('titre', 'Modifier le contenu')

@section('contenu')
    <div class="form-conteneur">
        <h1>Modifier la publication</h1>

        <form method="POST" action="{{ route('contenus.update', $publication) }}">
            @csrf
            @method('PUT')

            <div class="champ">
                <label for="titre">Titre du contenu *</label>
                <input type="text" name="titre" id="titre" value="{{ old('titre', $publication->titre) }}" required>
            </div>

            <div class="champ">
                <label for="categorie_id">Catégorie *</label>
                <select name="categorie_id" id="categorie_id" required>
                    @foreach ($categories as $categorie)
                        <option value="{{ $categorie->id }}" {{ old('categorie_id', $publication->categorie_id) == $categorie->id ? 'selected' : '' }}>
                            {{ $categorie->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="champ">
                <label for="contenu">Contenu / Description *</label>
                <textarea name="contenu" id="contenu" rows="6" required>{{ old('contenu', $publication->contenu) }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primaire">Enregistrer les modifications</button>
                <a href="{{ route('contenus.show', $publication) }}" class="btn btn-secondaire">Annuler</a>
            </div>
        </form>
    </div>
@endsection
