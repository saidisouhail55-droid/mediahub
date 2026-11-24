@extends('layouts.app')

@section('titre', 'Nouveau contenu')

@section('contenu')
    <div class="form-conteneur">
        <h1>Créer une nouvelle publication</h1>

        <form method="POST" action="{{ route('contenus.store') }}">
            @csrf

            <div class="champ">
                <label for="titre">Titre du contenu *</label>
                <input type="text" name="titre" id="titre" value="{{ old('titre') }}" required placeholder="Ex: Mon documentaire vidéo">
            </div>

            <div class="champ">
                <label for="categorie_id">Catégorie *</label>
                <select name="categorie_id" id="categorie_id" required>
                    <option value="">-- Sélectionnez une catégorie --</option>
                    @foreach ($categories as $categorie)
                        <option value="{{ $categorie->id }}" {{ old('categorie_id') == $categorie->id ? 'selected' : '' }}>
                            {{ $categorie->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="champ">
                <label for="contenu">Contenu / Description *</label>
                <textarea name="contenu" id="contenu" rows="6" required placeholder="Détaillez le contenu multimédia...">{{ old('contenu') }}</textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primaire">Publier le contenu</button>
                <a href="{{ route('contenus.index') }}" class="btn btn-secondaire">Annuler</a>
            </div>
        </form>
    </div>
@endsection
