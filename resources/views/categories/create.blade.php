@extends('layouts.app')

@section('titre', 'Créer une catégorie')

@section('contenu')
    <div class="form-conteneur">
        <h1>Créer une catégorie</h1>

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

        <form method="POST" action="{{ route('categories.store') }}">
            @csrf

            <div class="champ">
                <label for="nom">Nom de la catégorie *</label>
                <input type="text" name="nom" id="nom" value="{{ old('nom') }}" required placeholder="Ex: Vidéo, Podcast, etc." class="@error('nom') champ-erreur @enderror">
                @error('nom')
                    <span class="erreur-champ">{{ $message }}</span>
                @enderror
            </div>

            <div class="champ">
                <label for="slug">Slug *</label>
                <input type="text" name="slug" id="slug" value="{{ old('slug') }}" placeholder="Ex: video, podcast" class="@error('slug') champ-erreur @enderror">
                <small style="color: #6b7280; font-size: 0.85rem;">Si laissé vide, le slug sera généré automatiquement à partir du nom.</small>
                @error('slug')
                    <span class="erreur-champ">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primaire">Créer la catégorie</button>
                <a href="{{ route('categories.index') }}" class="btn btn-secondaire">Annuler</a>
            </div>
        </form>
    </div>
@endsection
