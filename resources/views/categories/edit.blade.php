@extends('layouts.app')

@section('titre', 'Modifier la catégorie')

@section('contenu')
    <div class="form-conteneur">
        <h1>Modifier la catégorie : {{ $categorie->nom }}</h1>

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

        <form method="POST" action="{{ route('categories.update', $categorie) }}">
            @csrf
            @method('PUT')

            <div class="champ">
                <label for="nom">Nom de la catégorie *</label>
                <input type="text" name="nom" id="nom" value="{{ old('nom', $categorie->nom) }}" required class="@error('nom') champ-erreur @enderror">
                @error('nom')
                    <span class="erreur-champ">{{ $message }}</span>
                @enderror
            </div>

            <div class="champ">
                <label for="slug">Slug *</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug', $categorie->slug) }}" required class="@error('slug') champ-erreur @enderror">
                @error('slug')
                    <span class="erreur-champ">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primaire">Enregistrer les modifications</button>
                <a href="{{ route('categories.index') }}" class="btn btn-secondaire">Annuler</a>
            </div>
        </form>
    </div>
@endsection
