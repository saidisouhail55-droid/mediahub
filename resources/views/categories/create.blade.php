@extends('layouts.app')

@section('titre', 'Créer une catégorie')

@section('contenu')
    <div class="form-conteneur">
        <h1>Créer une catégorie</h1>

        <form method="POST" action="{{ route('categories.store') }}">
            @csrf

            <div class="champ">
                <label for="nom">Nom de la catégorie *</label>
                <input type="text" name="nom" id="nom" value="{{ old('nom') }}" required placeholder="Ex: Vidéo, Podcast, etc.">
            </div>

            <div class="champ">
                <label for="slug">Slug (optionnel, généré automatiquement si vide)</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug') }}" placeholder="Ex: video, podcast">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primaire">Créer la catégorie</button>
                <a href="{{ route('categories.index') }}" class="btn btn-secondaire">Annuler</a>
            </div>
        </form>
    </div>
@endsection
