<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Semaine 9 — Gestion des médias
     *
     * Ajoute les colonnes nécessaires au stockage des fichiers multimédias
     * dans la table publications. La colonne 'image' existe déjà (semaine 6),
     * donc on ne l'ajoute pas ici.
     *
     * - fichier_media : chemin du fichier audio/vidéo stocké sur le disque public
     * - type_media    : 'audio' ou 'video' (déterminé automatiquement côté serveur)
     * - document_prive : chemin d'un document stocké sur le disque local (privé)
     */
    public function up(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            // Chemin du fichier audio ou vidéo (nullable : toutes les publications n'ont pas de média)
            if (! Schema::hasColumn('publications', 'fichier_media')) {
                $table->string('fichier_media')->nullable()->after('image');
            }

            // Type du média : 'audio' ou 'video' — déterminé automatiquement côté serveur
            if (! Schema::hasColumn('publications', 'type_media')) {
                $table->string('type_media')->nullable()->after('fichier_media');
            }

            // Chemin du document privé (stocké sur le disque 'local', invisible depuis le web)
            if (! Schema::hasColumn('publications', 'document_prive')) {
                $table->string('document_prive')->nullable()->after('type_media');
            }
        });
    }

    /**
     * Annule l'ajout des colonnes — utilisé uniquement en développement.
     * Ne jamais exécuter en production sans sauvegarde préalable.
     */
    public function down(): void
    {
        Schema::table('publications', function (Blueprint $table) {
            $table->dropColumnIfExists('fichier_media');
            $table->dropColumnIfExists('type_media');
            $table->dropColumnIfExists('document_prive');
        });
    }
};
