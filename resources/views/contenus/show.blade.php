@extends('layouts.app')

@section('titre', $publication->titre)

@section('contenu')
    <article class="publication-detail">
        <h1>{{ $publication->titre }}</h1>

        <p class="meta" style="color: #666; margin-bottom: 15px;">
            <strong>Catégorie :</strong> {{ $publication->categorie?->nom ?? 'Non catégorisé' }} |
            <strong>Auteur :</strong> {{ $publication->auteur?->name ?? 'Anonyme' }} |
            <strong>Publié le :</strong> {{ $publication->created_at?->format('d/m/Y à H:i') }}
        </p>

        @if($publication->tags && $publication->tags->isNotEmpty())
            <p class="tags" style="margin-bottom: 15px;">
                <strong>Tags :</strong>
                @foreach($publication->tags as $tag)
                    <span style="display: inline-block; background: #e0e7ff; color: #3730a3; padding: 2px 8px; border-radius: 4px; font-size: 0.85em; margin-right: 5px;">
                        #{{ $tag->nom }}
                    </span>
                @endforeach
            </p>
        @endif

        @if($publication->image)
            <div class="publication-image" style="margin-bottom: 20px;">
                <img src="{{ asset('storage/' . $publication->image) }}" alt="{{ $publication->titre }}" style="max-width: 100%; border-radius: 6px;">
            </div>
        @endif

        <div class="publication-corps" style="line-height: 1.6; font-size: 1.1em; margin-bottom: 30px;">
            {!! nl2br(e($publication->contenu)) !!}
        </div>

        <p>
            <a href="{{ route('contenus.index') }}" class="btn">&larr; Retour à la liste des contenus</a>
        </p>

        <hr style="margin: 40px 0;">

        <section class="commentaires">
            <h2>Commentaires ({{ $publication->commentaires->count() }})</h2>

            @forelse($publication->commentaires as $commentaire)
                <div class="commentaire" style="border-left: 3px solid #3b82f6; padding-left: 15px; margin-bottom: 20px;">
                    <p style="margin: 0; font-size: 0.9em; color: #555;">
                        <strong>{{ $commentaire->auteur?->name ?? 'Utilisateur' }}</strong>
                        <span>— {{ $commentaire->created_at?->diffForHumans() }}</span>
                    </p>
                    <p style="margin-top: 5px;">{{ $commentaire->contenu }}</p>
                </div>
            @empty
                <p>Aucun commentaire pour le moment. Soyez le premier à commenter !</p>
            @endforelse
        </section>
    </article>
@endsection
