> **Note paiement KomiPay.** La confirmation des paiements ne repose sur aucun
> webhook : la documentation KomiPay ne décrit aucune notification
> serveur-à-serveur. Elle dépend entièrement de `check-transaction-status`,
> interrogé par l'application (toutes les 4 s pendant un paiement) et par la
> commande planifiée `komipay:sync` (toutes les 15 min).
> Piège à connaître : cet endpoint attend `apikey` **sans underscore**, alors
> que tous les autres utilisent `api_key`.

# Endpoints ajoutés — diffusion sécurisée & messagerie

Deux ensembles d'endpoints introduits en août 2026. Le compteur de visiteurs
(`/api/site-visits`, `/api/site-statistics`) existait déjà et n'est pas décrit
ici.

Format de réponse commun au projet : `{"success": bool, "data": …}` ou
`{"success": false, "message": "…"}` en cas d'erreur.

---

## 1. Diffusion sécurisée des médias de leçon

Avant, les PDF vivaient sur le disque `public` : le support d'un cours payant
était téléchargeable en devinant son URL, sans compte. Les fichiers sont
désormais sur le disque privé (`storage/app/private`) et servis derrière une
URL signée.

### `GET /api/lecons/{id}/contenu` — *modifié*
**Auth :** `auth:sanctum`

Inchangé, sauf le champ `url` qui contient maintenant une **URL signée de
30 minutes** au lieu d'un lien public. Nouveau champ `est_externe` (booléen) :
`true` quand l'URL pointe vers un service tiers (YouTube), auquel cas le lecteur
intégré ne peut pas la lire.

### `GET /api/lecons/{id}/media`
**Auth :** `auth:sanctum` · **Query :** `type=pdf|video` (défaut : type de la leçon)

Délivre une URL de lecture fraîche, après vérification que l'utilisateur est
inscrit au cours (les cours gratuits sont ouverts ; le formateur propriétaire et
les administrateurs gardent l'accès). Utile pour les vidéos longues, dont l'URL
peut expirer en cours de lecture.

```json
{ "success": true,
  "data": { "url": "https://…/stream/pdf?expires=…&signature=…",
            "type": "pdf", "expire_dans": 1800, "est_externe": false } }
```

`403` si l'utilisateur n'est pas inscrit · `404` si la leçon n'a pas de média.

### `GET /api/lecons/{lecon}/stream/{type}`
**Auth :** aucune — **c'est la signature de l'URL qui autorise** (middleware `signed`).

Ce choix est délibéré : sur le Web, la balise `<video>` et le moteur PDF
chargent l'URL eux-mêmes et ne savent pas joindre d'en-tête `Authorization`.

Sert le fichier avec :
- `Content-Disposition: inline` — pas de téléchargement forcé ;
- `Accept-Ranges: bytes` et réponses **206** sur requête `Range`, ce qui permet
  l'avance rapide vidéo sans rapatrier le fichier entier ;
- `Cache-Control: private, no-store` et `X-Robots-Tag: noindex`.

`403` si la signature est absente, altérée ou expirée.

### Commande de migration des fichiers

```bash
php artisan lecons:securiser-medias --dry-run   # inspecter
php artisan lecons:securiser-medias             # appliquer
```

Déplace les médias du disque public vers le disque privé et normalise les
chemins en base, qui mélangeaient trois formats hérités (`/storage/cours/x.pdf`,
`lecons/pdfs/x.pdf`, URL externes). Idempotente.

---

## 2. Messagerie formateur ↔ apprenants

Deux formes de fil : `groupe` (tous les inscrits d'un cours) et `prive`
(formateur ↔ un apprenant).

**Les participants sont déduits des inscriptions, jamais stockés.** Il n'existe
pas de table de participants : elle devrait être resynchronisée à chaque
inscription ou désinscription. Conséquence directe : un apprenant désinscrit
perd l'accès immédiatement.

Toutes les routes ci-dessous sont sous `auth:sanctum`.

### `GET /api/conversations`
Fils visibles par l'appelant, du plus récemment actif au plus ancien. Chaque
entrée porte `non_lus`, `dernier_message` et un `titre` prêt à afficher.

Un apprenant voit les fils de groupe de ses cours et ses propres fils privés ;
un formateur, tous les fils de ses cours.

### `GET /api/conversations/non-lus`
`{"success": true, "data": {"total": 3}}` — alimente la pastille de navigation.

### `POST /api/conversations/cours/{cours}/groupe`
Ouvre ou récupère le fil de groupe du cours. **Idempotent.**

### `POST /api/conversations/cours/{cours}/prive`
Corps : `{"apprenant_id": 12}`. Ouvre ou récupère le fil privé. **Idempotent.**

Réservé au formateur du cours (un apprenant ne peut ouvrir qu'un fil le
concernant lui-même). `422` si l'apprenant visé n'est pas inscrit au cours.

### `GET /api/conversations/{conversation}/messages`
**Query :** `since=ID` (optionnel)

Sans `since` : les 50 derniers messages, dans l'ordre chronologique.
Avec `since` : **uniquement les messages postérieurs à cet id**.

```json
{ "success": true,
  "data": { "messages": [ { "id": 42, "contenu": "…", "est_de_moi": false,
                            "envoye_le": "2026-08-25T09:12:00+00:00",
                            "expediteur": { "id": 2, "nom": "…", "role": "formateur" } } ],
            "dernier_id": 42 } }
```

`dernier_id` est le curseur à renvoyer au sondage suivant. C'est ce mécanisme
qui rend l'interrogation périodique viable sans WebSocket : le client sonde
toutes les 5 s dans un fil ouvert, 15 s pour la liste, et ne rapatrie que le
nouveau.

### `POST /api/conversations/{conversation}/messages`
Corps : `{"contenu": "…"}` (5000 caractères max). Renvoie `201` et le message créé.

### `POST /api/conversations/{conversation}/lu`
Positionne le curseur de lecture sur le dernier message du fil. Ne recule
jamais : un sondage tardif ne peut pas « dé-lire ».

Toutes ces routes renvoient `403` si l'appelant n'a pas accès au fil.

### Modération

Le back-office (Filament › Communication › Messagerie) permet de **masquer** un
message sans le supprimer : la trace reste pour un éventuel litige, mais le
message disparaît de l'API (tous les endpoints filtrent sur `est_masque`).

---

## Passage ultérieur au temps réel

Le polling a été retenu pour ne pas ajouter de service long au déploiement
(conteneur unique derrière le nginx de l'hôte). Un passage à Laravel Reverb ne
changerait que le **transport côté client** : le modèle de données et les
endpoints resteraient identiques, `since` servant alors de rattrapage après une
coupure de connexion.
