# Certificats — PDF, signataires et vérification publique

Introduit en septembre 2026. Avant ce changement, `GET /api/certificats/{id}/pdf`
répondait « Le PDF sera généré prochainement » et l'application déduisait les
certificats des inscriptions terminées, avec une référence et des signatures
inventées.

Format de réponse commun au projet : `{"success": bool, "data": …}`.

---

## 1. Signatures figées

Un certificat porte **jusqu'à 3 signataires**, choisis ainsi :

1. les signataires propres au cours (champ *Signataires du certificat* du
   formulaire de cours), s'il en a ;
2. sinon, les signataires **par défaut** (menu *Signataires*, interrupteur
   *Signataire par défaut*), dans l'ordre d'impression.

Nom, fonction et image sont **recopiés sur le certificat** (table
`certificat_signatures`) au moment où il est émis. Modifier un signataire,
remplacer sa signature ou le supprimer ne change donc jamais un certificat déjà
délivré. L'image est copiée sous `storage/app/private/certificats/signatures/`,
nommée par l'empreinte de son contenu.

Cas particuliers :

- **Aucun signataire configuré** : rien n'est figé, le PDF sort sans signature,
  et les signataires s'appliqueront dès qu'ils seront définis.
- **Certificats émis avant cette fonctionnalité** : leurs signatures sont
  figées au premier téléchargement.
- **Correction d'une erreur** : action *Actualiser les signatures* dans la
  liste des certificats. Sans signataire configuré, elle ne retire rien.

Les images de signature sont sur le disque **privé** : exposées publiquement,
elles pourraient être apposées sur un faux.

---

## 2. Endpoints

### `GET /api/certificats` — *modifié*
**Auth :** `auth:sanctum`

Deux champs ajoutés à chaque certificat : `url_verification` (page publique,
voir §3) et `date_revocation` (`null` si valide).

### `GET /api/certificats/{id}` — *modifié*
**Auth :** `auth:sanctum` (titulaire ou administrateur)

Ajoute `url_verification` et `signataires` (`[{nom, fonction}]`, tels
qu'imprimés).

### `GET /api/certificats/{id}/pdf` — *modifié*
**Auth :** `auth:sanctum` (titulaire ou administrateur)

Délivre deux liens signés, valables **10 minutes**, que le navigateur ouvre
lui-même :

```json
{ "success": true,
  "data": {
    "certificat_id": 1,
    "code": "CERT-66E5A3B1C2D4F-1a2b3c",
    "url_apercu": "https://darasihub.com/api/certificats/1/fichier/apercu?expires=…&signature=…",
    "url_telechargement": "https://darasihub.com/api/certificats/1/fichier/telechargement?expires=…&signature=…",
    "expire_dans": 600 } }
```

| Code | Cas |
|------|-----|
| 403  | Certificat d'un autre apprenant |
| 403  | Certificat **révoqué** (`"error": "Ce certificat a été révoqué : il ne peut plus être téléchargé."`). L'administration garde l'accès ; le PDF porte alors la mention « RÉVOQUÉ ». |

### `GET /api/certificats/{certificat}/fichier/{mode}` — *nouveau*
**Auth :** URL signée (middleware `signed`), sans jeton · **mode :** `apercu` | `telechargement`

Sert le PDF (A4 paysage, ~60 Ko). `apercu` répond en
`Content-Disposition: inline`, `telechargement` en `attachment`. En-têtes
`Cache-Control: private, no-store` et `X-Robots-Tag: noindex`. Sans signature
valide : **403**.

Hors `auth:sanctum` à dessein, comme les médias de leçon : un lien ouvert par
le navigateur ne peut pas joindre d'en-tête `Authorization`.

### `GET /api/certificats/verify/{code}` — *modifié*
**Auth :** aucune

Ajoute `date_revocation` et `signataires` dans `data.certificat`. **404** si le
code n'existe pas.

---

## 3. Vérification publique

Le PDF imprime le numéro du certificat, l'URL de vérification et un **QR code**
qui y mène :

```
https://darasihub.com/#/verification/CERT-66E5A3B1C2D4F-1a2b3c
```

Page Flutter publique (sans compte) : verdict *authentique*, *révoqué* ou
*introuvable*, avec titulaire, formation, date et signataires. La saisie
manuelle d'un numéro se fait sur `/#/verification`.

L'adresse du front vient de `config('app.frontend_url')`, soit la variable
`FRONTEND_URL`, à défaut `APP_URL`. En production les deux sont identiques
(`https://darasihub.com`) : **aucune variable à ajouter**.

---

## 4. Déploiement

- **3 migrations** : `signataires`, `cours_signataire`, `certificat_signatures`.
  Toutes additives, aucune donnée existante modifiée.
- **Aucune dépendance Composer ajoutée** : dompdf était déjà installé ; le QR
  code utilise `chillerlan/php-qrcode`, présent via Filament. S'il disparaissait,
  le PDF resterait vérifiable par le numéro et l'URL imprimés en clair.
- **Extension GD requise** pour imprimer les signatures PNG détourées. Elle est
  dans l'image Docker (`deployment/backend/Dockerfile`). Sans elle, l'image est
  omise et l'erreur journalisée, plutôt que de faire échouer le téléchargement.
