@extends('layouts.app')

@section('titre', 'Contenus')

@section('contenu')
    <div class="page-entete">
        <h1>Contenus multimédias</h1>
        <a href="{{ route('contenus.create') }}" class="btn btn-primaire">+ Nouveau contenu</a>
    </div>

    @forelse ($publications as $publication)
        <x-carte titre="{{ $publication->titre }}">
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
                <a href="{{ route('contenus.edit', $publication) }}" class="btn btn-secondaire">Modifier</a>

                <form method="POST" action="{{ route('contenus.destroy', $publication) }}" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette publication ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </x-carte>
    @empty
        <p>Aucun contenu disponible pour le moment.</p>
    @endforelse

    <div class="pagination">
        {{ $publications->links() }}
    </div>
@endsection
