@extends('layouts.app')

@section('titre', $publication->titre)

@section('contenu')
    <article class="publication-detail">
        <div class="page-entete">
            <h1>{{ $publication->titre }}</h1>
            <div class="carte-actions">
                @can('update', $publication)
                    <a href="{{ route('contenus.edit', $publication) }}" class="btn btn-secondaire">Modifier</a>
                @endcan

                @can('delete', $publication)
                    <form method="POST" action="{{ route('contenus.destroy', $publication) }}" style="display:inline-block;"
                        onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette publication ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Supprimer</button>
                    </form>
                @endcan
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

        {{-- ============================================================
             SEMAINE 9 — AFFICHAGE DE L'IMAGE DE COUVERTURE
             Storage::disk('public')->url() génère l'URL complète à partir
             du chemin relatif stocké en base (ex: "publications/abc.jpg")
             → http://localhost/storage/publications/abc.jpg
             ============================================================ --}}
        @if($publication->image)
            <div class="publication-image" style="margin: 20px 0;">
                <img src="{{ Storage::disk('public')->url($publication->image) }}"
                    alt="Image de couverture : {{ $publication->titre }}"
                    style="max-width: 100%; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
            </div>
        @endif

        {{-- ============================================================
             SEMAINE 9 — AFFICHAGE DU FICHIER MULTIMÉDIA
             On utilise type_media pour choisir la bonne balise HTML :
             <audio> pour les fichiers MP3/WAV, <video> pour les MP4.
             controls : affiche les contrôles natifs du navigateur.
             ============================================================ --}}
        @if($publication->fichier_media)
            <div class="publication-media" style="margin: 20px 0;">
                @if($publication->type_media === 'audio')
                    <p><strong>🎵 Fichier audio :</strong></p>
                    <audio controls style="width: 100%;">
                        <source src="{{ Storage::disk('public')->url($publication->fichier_media) }}">
                        Votre navigateur ne supporte pas la lecture audio HTML5.
                    </audio>
                @elseif($publication->type_media === 'video')
                    <p><strong>🎬 Fichier vidéo :</strong></p>
                    <video controls style="max-width: 100%; border-radius: 6px;">
                        <source src="{{ Storage::disk('public')->url($publication->fichier_media) }}">
                        Votre navigateur ne supporte pas la lecture vidéo HTML5.
                    </video>
                @endif
            </div>
        @endif

        {{-- ============================================================
             SEMAINE 9 — LIEN DOCUMENT PRIVÉ
             Visible uniquement pour l'auteur ou un administrateur.
             Le fichier est servi via une route contrôlée, jamais directement.
             ============================================================ --}}
        @if($publication->document_prive)
            @can('update', $publication)
                <div class="publication-document" style="margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 6px; border: 1px solid #dee2e6;">
                    <p><strong>📎 Document privé associé :</strong></p>
                    <a href="{{ route('contenus.document', $publication) }}" class="btn btn-secondaire">
                        Télécharger le document
                    </a>
                </div>
            @endcan
        @endif

        {{-- Affichage 100% sécurisé avec double accolades {{ }} contre les failles XSS --}}
        <div class="publication-corps" style="white-space: pre-line; margin-top: 20px;">
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
