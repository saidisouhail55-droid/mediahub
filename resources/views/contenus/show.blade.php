@extends('layouts.app')

@section('titre', $publication->titre)

@section('contenu')
    <article class="publication-detail">
        <div class="page-entete">
            <h1>{{ $publication->titre }}</h1>
            <div class="carte-actions">
                <a href="{{ route('contenus.edit', $publication) }}" class="btn btn-secondaire">Modifier</a>
                <form method="POST" action="{{ route('contenus.destroy', $publication) }}" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette publication ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>

        <p class="meta">
            <strong>Catégorie :</strong> {{ $publication->categorie?->nom ?? 'Non catégorisé' }} |
            <strong>Auteur :</strong> {{ $publication->auteur?->name ?? 'Anonyme' }} |
            <strong>Publié le :</strong> {{ $publication->created_at?->format('d/m/Y à H:i') }}
        </p>

        @if($publication->tags && $publication->tags->isNotEmpty())
            <p class="tags">
                <strong>Tags :</strong>
                @foreach($publication->tags as $tag)
                    <span class="badge-tag">#{{ $tag->nom }}</span>
                @endforeach
            </p>
        @endif

        @if($publication->image)
            <div class="publication-image">
                <img src="{{ asset('storage/' . $publication->image) }}" alt="{{ $publication->titre }}">
            </div>
        @endif

        {{-- Affichage 100% sécurisé avec double accolades {{ }} contre les failles XSS --}}
        <div class="publication-corps" style="white-space: pre-line;">
            {{ $publication->contenu }}
        </div>

        <p style="margin-top: 25px;">
            <a href="{{ route('contenus.index') }}" class="btn">&larr; Retour à la liste des contenus</a>
        </p>

        <hr style="margin: 40px 0;">

        <section class="commentaires">
            <h2>Commentaires ({{ $publication->commentaires->count() }})</h2>

            @forelse($publication->commentaires as $commentaire)
                <div class="commentaire">
                    <p class="commentaire-auteur">
                        <strong>{{ $commentaire->auteur?->name ?? 'Utilisateur' }}</strong>
                        <small>({{ $commentaire->created_at?->diffForHumans() }})</small>
                    </p>
                    <p class="commentaire-texte">{{ $commentaire->contenu }}</p>
                </div>
            @empty
                <p>Aucun commentaire pour le moment. Soyez le premier à commenter !</p>
            @endforelse
        </section>
    </article>
@endsection
