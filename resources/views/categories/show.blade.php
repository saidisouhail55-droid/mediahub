@extends('layouts.app')

@section('titre', 'Catégorie : ' . $categorie->nom)

@section('contenu')
    <div class="page-entete">
        <h1>Catégorie : {{ $categorie->nom }}</h1>
        <div>
            <a href="{{ route('categories.edit', $categorie) }}" class="btn btn-secondaire">Modifier</a>
            <a href="{{ route('categories.index') }}" class="btn">&larr; Retour aux catégories</a>
        </div>
    </div>

    <p class="meta">
        <strong>Slug :</strong> <code>{{ $categorie->slug }}</code> |
        <strong>Nombre de publications :</strong> {{ $categorie->publications->count() }}
    </p>

    <hr style="margin: 20px 0;">

    <h2>Publications dans cette catégorie</h2>

    @forelse ($categorie->publications as $publication)
        <div class="carte">
            <h3>{{ $publication->titre }}</h3>
            <p>{{ Str::limit($publication->contenu, 140) }}</p>
            <p class="meta">
                <strong>Auteur :</strong> {{ $publication->auteur?->name ?? 'Anonyme' }} |
                <span>{{ $publication->created_at?->diffForHumans() }}</span>
            </p>
            <a href="{{ route('contenus.show', $publication) }}" class="btn">Consulter</a>
        </div>
    @empty
        <p>Aucune publication n'est encore associée à cette catégorie.</p>
    @endforelse
@endsection
