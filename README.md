# DARASI — Backend

API Laravel + back-office [Filament](https://filamentphp.com/) de la plateforme d'e-learning DARASI. Sert l'API JSON consommée par le frontend Flutter web ([`../darasi_app`](../darasi_app)) et le panel d'administration à `/admin`.

## Stack

- PHP 8.3, Laravel 13, Filament 5
- MySQL 8
- Sanctum pour l'authentification API

## Installation locale

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Renseigner dans `.env` au minimum : `DB_*` (base MySQL locale), et éventuellement `KOMIPAY_*`/`RECAPTCHA_*` si vous testez le paiement ou la vérification anti-robot (laisser vide désactive ces vérifications, voir les commentaires dans `.env.example`).

```bash
php artisan migrate
php artisan storage:link
php artisan serve
```

Les seeders ne sont **pas** exécutés automatiquement (voir la note ci-dessous). Pour peupler une base neuve avec des données d'exemple :

```bash
php artisan db:seed
```

## Variables d'environnement

Voir [`.env.example`](.env.example) — chaque variable y est commentée (rôle, valeurs possibles, obligatoire ou non). Les pilotes alternatifs (Redis, S3, Postmark...) sont listés en fin de fichier, commentés, car inertes tant que les pilotes actifs (`database`/`local`) ne sont pas changés.

## Déploiement Docker

Ce dépôt fournit les images consommées par [`../deployment`](../deployment) (`docker-compose.yml`), lui-même son propre dépôt versionné séparément. Voir `../deployment/.env.example` pour les variables spécifiques au déploiement (secrets MySQL, `APP_KEY` de production, clés reCAPTCHA).

Notes importantes sur le conteneur backend (`deployment/backend/docker-entrypoint.sh`) :
- Au démarrage, seules les **migrations** sont exécutées (`php artisan migrate --force`) — **pas** de seeding automatique. Un seeder avec un ancien identifiant en dur (`formateur_id`) avait provoqué une boucle de redémarrage en production le jour où ce compte de démo a été supprimé ; le seeding est désormais une opération manuelle (`docker compose exec backend php artisan db:seed`).
- La limite de taille des images uploadées (actuellement 5 Mo pour le champ « Image d'illustration » d'Actualités & alertes) est fixée à trois niveaux qui doivent rester synchronisés : validation Filament (`->maxSize()`), PHP (`deployment/backend/Dockerfile`, `conf.d/uploads.ini`), et nginx (`deployment/backend/nginx.conf`, `client_max_body_size`).

## Tâches planifiées (cron)

Le planificateur Laravel est configuré dans `bootstrap/app.php` (`withSchedule`). Le serveur doit exécuter `php artisan schedule:run` chaque minute (crontab classique) pour que ces tâches s'exécutent :

| Commande | Fréquence | Rôle |
|---|---|---|
| `abonnements:check-expiration` | quotidienne, 08:00 | Vérifie et traite les abonnements arrivant à expiration. |
| `visits:aggregate` | quotidienne, 00:05 | Recalcule les compteurs de visites (`visit_counters`) et corrige toute dérive du compteur temps réel. |

Commande disponible mais non planifiée automatiquement (à déclencher manuellement ou à ajouter au planificateur selon le besoin) :

| Commande | Rôle |
|---|---|
| `komipay:sync` | Synchronise les paiements Komipay. |

## Compteur de visites

Le compteur de visites (footer public + tableau de bord admin) repose sur un endpoint dédié (`POST /api/site-visits`, protégé par le middleware `App\Http\Middleware\TrackSiteVisit`) que le frontend Flutter appelle explicitement à la navigation — pas un middleware global, l'app étant une SPA côté client que Laravel ne voit jamais naviguer.

Le texte et l'activation/désactivation du compteur affiché dans le footer se configurent via l'écran **Vision & Mission** existant (`/admin/contenus-site`) : créez ou éditez le bloc dont la « Clé technique » vaut `compteur_visites` (le champ « Texte » accepte le jeton `{n}`, remplacé par le total). Aucune ligne n'existe par défaut sur une base neuve — le compteur reste alors affiché avec un texte générique tant que ce réglage n'est pas créé.

## Tests

```bash
php artisan test
```
