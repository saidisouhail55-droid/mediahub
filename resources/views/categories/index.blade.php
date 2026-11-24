@extends('layouts.app')

@section('titre', 'Catégories')

@section('contenu')
    <div class="page-entete">
        <h1>Liste des catégories</h1>
        @can('gerer-categories')
            <a href="{{ route('categories.create') }}" class="btn btn-primaire">+ Nouvelle catégorie</a>
        @endcan
    </div>

    @forelse ($categories as $categorie)
        <div class="carte">
            <h2>{{ $categorie->nom }}</h2>
            <p class="meta">
                <strong>Slug :</strong> <code>{{ $categorie->slug }}</code> |
                <strong>Publications associées :</strong> {{ $categorie->publications_count }}
            </p>

            <div class="carte-actions">
                <a href="{{ route('categories.show', $categorie) }}" class="btn">Voir</a>

                @can('gerer-categories')
                    <a href="{{ route('categories.edit', $categorie) }}" class="btn btn-secondaire">Modifier</a>

                    <form method="POST" action="{{ route('categories.destroy', $categorie) }}" style="display:inline-block;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                @endcan
            </div>
        </div>
    @empty
        <p>Aucune catégorie disponible pour le moment.</p>
    @endforelse

    <div class="pagination">
        {{ $categories->links() }}
    </div>
@endsection
