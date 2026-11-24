@extends('layouts.app')

@section('titre', 'Contenus')

@section('contenu')
    <div class="page-entete">
        <h1>Contenus multimédias</h1>
        @auth
            <a href="{{ route('contenus.create') }}" class="btn btn-primaire">+ Nouveau contenu</a>
        @endauth
        @guest
            <a href="{{ route('connexion') }}" class="btn btn-secondaire" title="Connectez-vous pour publier">+ Publier un contenu</a>
        @endguest
    </div>

    @forelse ($publications as $publication)
        <x-carte titre="{{ $publication->titre }}">
            {{-- ============================================================
                 SEMAINE 9 — MINIATURE / IMAGE DE COUVERTURE DANS LA CARTE
                 Affichée uniquement si une image existe pour cette publication.
                 ============================================================ --}}
            @if($publication->image)
                <div style="margin-bottom: 10px;">
                    <img src="{{ $publication->url_miniature }}"
                        alt="{{ $publication->titre }}"
                        style="width: 100%; max-height: 180px; object-fit: cover; border-radius: 4px;">
                </div>
            @endif

            {{-- Badge du type de média si présent --}}
            @if($publication->type_media === 'audio')
                <span style="display:inline-block; background:#e3f2fd; color:#1565c0; padding:2px 8px; border-radius:12px; font-size:0.8rem; margin-bottom:8px;">🎵 Audio</span>
            @elseif($publication->type_media === 'video')
                <span style="display:inline-block; background:#fce4ec; color:#c62828; padding:2px 8px; border-radius:12px; font-size:0.8rem; margin-bottom:8px;">🎬 Vidéo</span>
            @endif

            <p>{{ Str::limit($publication->contenu, 150) }}</p>

            <p class="meta">
                <strong>Catégorie :</strong> {{ $publication->categorie?->nom ?? 'Non catégorisé' }} |
                <strong>Auteur :</strong> {{ $publication->auteur?->name ?? 'Anonyme' }} |
                <span>{{ $publication->created_at?->diffForHumans() }}</span>
            </p>

            <p class="commentaires-info">
                💬 {{ $publication->commentaires_count ?? 0 }} commentaire(s)
            </p>

            <div class="carte-actions">
                <a href="{{ route('contenus.show', $publication) }}" class="btn">Consulter</a>

                @can('update', $publication)
                    <a href="{{ route('contenus.edit', $publication) }}" class="btn btn-secondaire">Modifier</a>
                @endcan

                @can('delete', $publication)
                    <form method="POST" action="{{ route('contenus.destroy', $publication) }}"
                        style="display:inline-block;"
                        onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette publication ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                @endcan
            </div>
        </x-carte>
    @empty
        <p>Aucun contenu disponible pour le moment.</p>
    @endforelse

    <div class="pagination">
        {{ $publications->links() }}
    </div>
@endsection
