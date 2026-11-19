@extends('layouts.app')

@section('titre', 'Contenus')

@section('contenu')
    <h1>Contenus multimédias</h1>

    @forelse ($publications as $publication)
        <x-carte titre="{{ $publication->titre }}">
            <p>{{ Str::limit($publication->contenu, 150) }}</p>

            <p class="meta" style="font-size: 0.9em; color: #666; margin-top: 10px;">
                <strong>Catégorie :</strong> {{ $publication->categorie?->nom ?? 'Non catégorisé' }} |
                <strong>Auteur :</strong> {{ $publication->auteur?->name ?? 'Anonyme' }} |
                <span>{{ $publication->created_at?->diffForHumans() }}</span>
            </p>

            <p style="font-size: 0.85em; color: #888;">
                💬 {{ $publication->commentaires_count ?? 0 }} commentaire(s)
            </p>

            <p style="margin-top: 10px;">
                <a href="{{ route('contenus.show', $publication) }}" class="btn">Consulter</a>
            </p>
        </x-carte>
    @empty
        <p>Aucun contenu disponible pour le moment.</p>
    @endforelse

    <div style="margin-top: 20px;">
        {{ $publications->links() }}
    </div>
@endsection
