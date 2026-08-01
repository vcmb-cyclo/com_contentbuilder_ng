# Plan de refonte architecturale — ContentBuilder NG

> Périmètre : les chantiers identifiés par l'audit du 2026-07-31 et **exclus**
> de la passe de correctifs sécurité (P0/P1 + quick wins), qui, elle, est
> livrée. Ce document ne couvre pas les correctifs déjà appliqués.
>
> Base de référence : 86 000 lignes de source (hors `admin/vendor`,
> `node_modules`), 1 524 méthodes, 689 tests unitaires, PHPStan niveau 2 avec
> baseline de 1 639 lignes, 2 377 erreurs PSR-12.
>
> **État au 2026-08-01** : chantier A, étapes 1-2 faites (PSR-12 en gate
> global, 0 erreur sur les 218 fichiers couverts par `phpcs.xml.dist`) ;
> étapes 3-4 restent entières (PHPStan est toujours au niveau 2, aucun garde
> anti-régression de baseline). Voir le chantier A ci-dessous pour le détail.

---

## 0. Vue d'ensemble

| # | Chantier | Charge | Risque | Dépend de |
|---|---|---:|---|---|
| A | Outillage qualité (PHPCS, PHPStan 2→6) — **PHPCS fait, PHPStan à faire** | 11 – 16 j restants | Faible | — |
| B | Échappement des sorties front | 4 – 6 j | Faible | A |
| C | Cache et requêtes N+1 | 6 – 10 j | Moyen | A |
| D | Décomposition d'`EditModel::store()` | 25 – 30 j | **Élevé** | A, F |
| E | Décomposition des autres god-methods | 15 – 20 j | Moyen | A, D |
| F | `FormSourceInterface` | 8 – 12 j | Moyen | A |
| G | Refonte du modèle de permission | 10 – 15 j | Moyen | F |
| H | Moteur de gabarits sans `eval()` | 12 – 18 j | **Élevé** | D, F |

**Total : 90 – 126 jours-homme.**

Le chantier H est conservé au plan pour mémoire : la direction a décidé de
**maintenir `eval()`** dans les gabarits. Il n'est pas planifié, mais reste
documenté pour que la décision soit relisible et réversible.

### Ordre d'exécution recommandé

```mermaid
graph LR
    A[A. Outillage] --> B[B. Échappement]
    A --> C[C. Cache / N+1]
    A --> F[F. FormSourceInterface]
    F --> G[G. Permissions]
    F --> D[D. EditModel::store]
    D --> E[E. Autres god-methods]
    D -.optionnel.-> H[H. Moteur de gabarits]
    F -.optionnel.-> H
```

A est le préalable non négociable : sans filet statique et sans style
vérifié, D et E sont des refontes à l'aveugle.

---

## A. Outillage qualité

**Constat.** PHPStan tourne au niveau 2 (sur 10) avec 1 639 lignes de baseline.
PHPCS vient d'être introduit et mesure 2 377 erreurs / 2 425 avertissements sur
218 fichiers, dont 2 262 auto-corrigeables. Le job CI ne contrôle que les
fichiers modifiés d'une PR, ce qui empêche la dette de croître sans la résorber.

### Étapes

1. ✅ **Fait (2026-08-01). Passe `phpcbf` par lots** — 2 262 violations
   auto-corrigeables. Découpé par répertoire, un commit par lot, sans mélange
   avec du changement fonctionnel :

   ```
   admin/src/Controller  →  admin/src/Service  →  admin/src/Helper
   →  admin/src/Model    →  admin/src/View     →  site/src  →  plugins
   →  (reliquat : script.php, admin/layouts, admin/services,
       admin/src/Table, admin/src/Extension, admin/src/Contract,
       admin/src/Dto, site/layouts — hors de la liste initiale ci-dessus,
       repérés en vérifiant que l'arbre était bien propre avant de basculer
       le gate)
   ```

   Après chaque lot : `phpunit` + `phpstan`, verts à chaque fois (693 tests).
   Deux catégories d'erreurs n'étaient pas mécaniquement sûres à corriger et
   ont été traitées au cas par cas plutôt qu'en aveugle :
   - **Visibilité manquante** (`Squiz.Scope.MethodScope.Missing`, ~90
     occurrences) : toujours `public` explicite sur du déjà-implicitement-
     public (Table/View::display, __construct, getData…) — comportement
     inchangé par construction.
   - **Noms en snake_case** (`PSR1.Methods.CamelCapsMethodName`,
     `PSR2.Methods.MethodDeclaration.Underscore`) : renommés seulement quand
     chaque appelant était vérifié `private` + local au même fichier
     (`_buildQuery` → `buildQuery` dans 5 Models ; 4 méthodes de
     `StorageModel`). Exclus du sniff, documenté en commentaire dans
     `phpcs.xml.dist`, dans les cas où le renommage était invisible à grep
     et à PHPStan : tâches de contrôleur routées par nom littéral
     (`UsersController::verified_view` etc.), méthodes de
     `ContentbuilderngHelper` appelées par nom de méthode dynamique
     (`$helperClass::$method(...)`), et la classe d'installation de
     `script.php` résolue par Joomla via `class_exists()` sur un nom fixe.

   Restent exclus de `phpcs.xml.dist` (dette réelle, pas traitée) :
   `admin/src/types/*`, `site/src/Model/EditModel.php`,
   `site/src/Model/ListModel.php`, `site/src/View/Details/HtmlView.php`,
   `admin/src/Model/VerifyModel.php`, et les plugins `download`,
   `image_scale`, `contentbuilderng_themes`. `EditModel.php` en particulier
   est la cible du chantier D ci-dessous, qui exige des tests de
   caractérisation *avant* la première ligne touchée — passer `phpcbf`
   dessus maintenant aurait anticipé sur cette méthode sans le filet qu'elle
   impose.

2. ✅ **Fait (2026-08-01). Basculé le job CI en gate global**, `phpcbf` étant
   passé partout ailleurs. `phpcs -n --report=full` (sans argument, piloté
   entièrement par `phpcs.xml.dist`) tourne maintenant sur PR et sur push,
   bloque sur erreur, tolère les avertissements (longueur de ligne). Le job
   `code-style` est désormais une dépendance du job `package`, ce qu'il
   n'était pas avant faute de pouvoir tourner de façon inconditionnelle.

3. **PHPStan : monter le niveau un cran à la fois — pas commencé.** À chaque
   cran, regénérer
   la baseline puis la résorber, ne jamais l'agrandir :

   | Niveau | Ce qu'il ajoute | Charge estimée |
   |---|---|---:|
   | 3 | types de retour, propriétés | 1 j |
   | 4 | code mort, conditions toujours vraies | 2 j |
   | 5 | types des arguments passés | 3 j |
   | 6 | **types manquants** (le vrai saut) | 5 – 8 j |

   S'arrêter à 6. Les niveaux 7-8 exigent une couverture de typage que le
   code legacy (`types/`, `EditModel`) ne pourra pas offrir avant D et F.

4. **Ajouter un garde anti-régression de baseline — pas commencé.** En CI :
   échouer si le nombre de lignes de `phpstan-baseline.neon` augmente.

**Livrable.** PSR-12 vérifié en gate global ✅ (2026-08-01), PHPStan niveau 6
❌, baseline strictement décroissante ❌. Charge restante : 11 – 16 j
(étapes 3-4 seules).

---

## B. Échappement des sorties front

**Constat.** 755 sorties non échappées dans `admin/tmpl`, `admin/layouts`,
`site/tmpl`, `site/layouts`. La majorité est inoffensive (entiers, drapeaux,
classes CSS calculées) ; une quinzaine est alimentée par des données de base.

### Étapes

1. **Inventorier et classer** les 755 occurrences en trois catégories :
   - *sûr* (littéral, entier, valeur calculée par le gabarit) → annoter, ne rien faire ;
   - *à échapper* (valeur issue de la base ou de la requête) → `$this->escape()` ;
   - *HTML voulu* (`intro_text`, `details_template`) → passer par
     `HTMLHelper::_('content.prepare', …)` ou un assainisseur explicite, jamais
     par un `echo` brut.

2. **Traiter en priorité** les cas déjà identifiés :

   | Fichier | Ligne | Variable |
   |---|---|---|
   | `site/tmpl/publicforms/default.php` | 206 | `$row->tag` |
   | `site/tmpl/publicforms/default.php` | 318-319 | `$this->lists['order']`, `['order_Dir']` |
   | `admin/tmpl/list/select.php` | 489, 494, 620, 625 | `$row->colRecord`, `$value` |
   | `admin/tmpl/list/default.php` | 122, 125 | `$this->page_title`, `$this->intro_text` |
   | `admin/tmpl/list/select.php` | 669-670 | `$this->lists['order']` |

3. **Ajouter un test structurel** (dans l'esprit de
   `InheritedMethodVisibilityTest`) qui échoue si un gabarit introduit un
   `echo $var` non échappé hors liste blanche.

4. **Nettoyer les gestionnaires `onclick` inline** de `admin/tmpl/list/select.php:322`
   et les `style=""` construits par `str_replace('::', ';', …)` — incompatibles
   avec une CSP stricte.

**Livrable.** Sorties classées, cas à risque échappés, régression bloquée par un test.

---

## C. Cache et requêtes N+1

**Constat.** Deux usages du cache Joomla dans tout le composant. 248 sites de
requête à l'intérieur de boucles.

### Étapes

1. **Cacher le rendu de liste frontend** (`ListModel::getData()`, 501 lignes,
   chemin le plus chaud) :

   ```php
   $cache = $container->get(CacheControllerFactoryInterface::class)
       ->createCacheController('callback', ['defaultgroup' => 'com_contentbuilderng.list']);

   $data = $cache->get(
       fn(): object => $this->getModel()->getData(),
       [],
       md5($formId . '|' . $userGroupsHash . '|' . $stateHash)
   );
   ```

   > ⚠️ **La clé doit intégrer les groupes de l'utilisateur.** Sans cela le
   > cache devient un vecteur de fuite d'ACL — un utilisateur servirait la vue
   > d'un autre. C'est le principal risque de ce chantier.

2. **Invalider** dans `FormModel::save()`, `StorageModel::save()` et à chaque
   écriture d'enregistrement.

3. **Résorber les N+1 les plus coûteux** — requêtes ensemblistes (`whereIn`)
   puis réindexation en PHP :

   | Fichier | Lignes |
   |---|---|
   | `admin/src/types/com_breezingformsng.php` | 632, 678, 863 |
   | `admin/src/types/com_contentbuilderng.php` | 108, 230 |
   | `admin/src/View/Storage/HtmlView.php` | 228, 400 |

4. **Sortir `SET SESSION group_concat_max_len`** de la boucle
   (`com_breezingformsng.php:441`).

5. **Vérifier que PhpSpreadsheet n'est pas chargé** hors du chemin d'export.

**Livrable.** Liste frontend cachée avec clé ACL-safe, N+1 principaux résorbés.

---

## D. Décomposition d'`EditModel::store()`

**Constat.** 1 305 lignes. Assure à elle seule : validation CSRF, résolution du
formulaire, traitement des téléversements, validation de champs, appels de
plugins, insertion en base, génération d'articles, notifications e-mail,
gestion d'état de liste. **Aucun test ne la couvre.**

C'est le chantier le plus risqué du plan : c'est le cœur de la soumission
d'enregistrement, et toute régression est silencieuse et coûteuse.

### Méthode — refactoring sous filet, jamais de réécriture

1. **Écrire d'abord des tests de caractérisation.** Avant de toucher une ligne :
   capturer le comportement actuel (y compris les bizarreries) sur les cas
   nominal, upload, validation en échec, quota atteint, article généré,
   notification. Ces tests documentent l'existant, pas l'idéal.

2. **Extraire par couches, une par commit**, en gardant la signature publique
   de `store()` intacte à chaque étape :

   ```
   store()
     ├─ UploadFieldProcessor      ~250 lignes   (traitement des fichiers)
     ├─ FieldValidationRunner     ~200 lignes   (validations + plugins)
     ├─ RecordPersister           ~300 lignes   (écriture base)
     ├─ ArticleSynchroniser       ~200 lignes   (délègue à ArticleService)
     ├─ SubmissionNotifier        ~150 lignes   (e-mails)
     └─ ListStateApplier          ~100 lignes   (état de liste, notation)
   ```

3. **Cible :**

   ```php
   final class RecordSubmissionService
   {
       public function __construct(
           private readonly UploadFieldProcessor $uploads,
           private readonly FieldValidationRunner $validation,
           private readonly RecordPersister $persister,
           private readonly ArticleSynchroniser $articles,
           private readonly SubmissionNotifier $notifier,
       ) {}

       public function submit(SubmissionContext $context): SubmissionResult
       {
           $values = $this->uploads->process($context);
           $errors = $this->validation->validateAll($context, $values);

           if ($errors !== []) {
               return SubmissionResult::failed($errors);
           }

           $recordId = $this->persister->persist($context, $values);
           $this->articles->syncForRecord($context, $recordId);
           $this->notifier->notify($context, $recordId, $values);

           return SubmissionResult::ok($recordId);
       }
   }
   ```

4. **`store()` devient une façade** de compatibilité qui construit le
   `SubmissionContext` et délègue. Elle peut rester jusqu'à la version majeure
   suivante.

5. **Chaque collaborateur extrait est immédiatement testé unitairement** — c'est
   le bénéfice principal, pas la réduction du nombre de lignes.

### Points de vigilance

- `customValidate()` / `customAction()` (`EditModel.php:742,749`) contiennent
  des `eval()` dont le code injecté accède aux variables locales de la méthode
  appelante. Toute extraction change la portée visible : à traiter en dernier,
  ou à figer explicitement le contrat des variables exposées.
- Le traitement des téléversements écrit sur disque **avant** la validation des
  champs, puis supprime en cas d'échec. Cet ordre doit être préservé ou
  explicitement changé, jamais changé par accident.

**Livrable.** 6 services testés, `store()` réduite à une façade, couverture du
chemin de soumission.

---

## E. Décomposition des autres god-methods

**Constat.** 108 méthodes dépassent 100 lignes ; 301 dépassent 50.

### Cibles, par ordre de valeur

| Méthode | Lignes | Note |
|---|---:|---|
| `ContentbuilderngImageScale::onContentPrepare()` | 738 | Plugin ; installe aussi un `set_error_handler()` global à supprimer (cf. §I) |
| `ArticleService::createArticle()` | 662 | Déjà un service ; découper en `ArticleBuilder` + `CategoryResolver` |
| `FormModel::save()` | 661 | Ajouter les transactions au passage (cf. §J) |
| `ListModel::getData()` | 501 | Préalable au cache (§C) |
| `types/*::getListRecords()` | 461 / 373 | Disparaît largement avec §F |
| `TemplateRenderService::getEditableTemplate()` | 436 | Contient `eval()` — prudence |
| `StorageController::save()` | 397 | Contrôleur : la logique doit descendre en service |
| `VerifyModel::__construct()` | 367 | Un constructeur ne doit pas faire ça |

**Règle d'arrêt.** Viser < 60 lignes par méthode, sans en faire un dogme :
mieux vaut 5 méthodes de 80 lignes cohérentes que 20 de 20 lignes artificielles.

---

## F. `FormSourceInterface`

**Constat.** `admin/src/types/*.php` (3 149 lignes) utilise un namespace en
minuscules hors convention, est chargé par `require_once` dynamique construit
depuis une valeur en base, et instancié par `call_user_func`.

### Cible

```php
namespace CB\Component\Contentbuilderng\Administrator\FormSource;

interface FormSourceInterface
{
    public function getRecords(RecordQuery $query): RecordCollection;
    public function getRecord(int $recordId): ?Record;
    public function isOwner(int $userId, int $recordId): bool;

    /**
     * @param list<int> $recordIds
     * @param int|null  $restrictToUserId Obligatoire quand le droit est limité
     *                                    aux enregistrements de l'utilisateur.
     */
    public function delete(array $recordIds, int $formId, ?int $restrictToUserId = null): bool;
}
```

> Le paramètre `$restrictToUserId` est dans le **contrat**, pas optionnel par
> confort : c'est ce qui rend structurellement impossible la classe de faille
> corrigée en S-01.

### Étapes

1. Définir l'interface et les DTO (`RecordQuery`, `Record`, `RecordCollection`).
2. Adapter `contentbuilderng_com_contentbuilderng` puis
   `contentbuilderng_com_breezingformsng` derrière l'interface, sans changer
   leur comportement.
3. Remplacer `FormSourceFactory` par une fabrique enregistrée dans le
   conteneur, avec une **carte explicite** type → classe. Supprime le
   `require_once` construit depuis la base (gadget LFI).
4. Conserver un adaptateur pour les sources tierces déposées dans
   `media/contentbuilderng/types/`, avec validation stricte du nom
   (`/^[a-z0-9_]+$/i`) et chargement par autoloader.
5. Remplacer le contrat `getNumRecordsQuery()` (qui renvoie du **SQL brut**
   injecté dans un `SELECT`) par une méthode retournant un entier.

**Livrable.** Sources de formulaires autoloadées, typées, testables ; plus
aucune inclusion dynamique pilotée par la base.

---

## G. Refonte du modèle de permission

**Constat.** La passe de sécurité a colmaté la faille (clé de session portée par
formulaire + marqueur de contexte vérifié + filtre propriétaire en SQL). Le
**design** reste fragile : l'autorisation dépend d'un état de session, et la
correction repose sur une discipline d'appel — armer avant de vérifier.

### Cible — service sans état

```php
final readonly class PermissionContext
{
    /** @param list<int> $recordIds */
    public function __construct(
        public int $formId,
        public array $recordIds,
        public int $userId,
        public bool $frontend,
    ) {}
}

final readonly class PermissionDecision
{
    public function __construct(
        public bool $allowed,
        public string $reason,
        public bool $ownerScoped,
    ) {}
}

interface PermissionEvaluatorInterface
{
    public function evaluate(string $action, PermissionContext $context): PermissionDecision;
}
```

### Étapes

1. Implémenter l'évaluateur sans session ; cache **mémoire par requête**,
   indexé par le contexte complet.
2. Migrer les appelants un par un, en gardant `PermissionService` comme façade.
3. Supprimer la façade et le stockage en session.
4. `PermissionDecision::$ownerScoped` remplace `hasGroupGrant()` et alimente
   directement le `$restrictToUserId` de `FormSourceInterface::delete()`.

**Bénéfice.** L'autorisation ne peut plus être « héritée » d'une requête
précédente : le contexte est un argument, pas un état.

---

## H. Moteur de gabarits sans `eval()` — *non planifié*

> **Décision de la direction : `eval()` est conservé.** Ce chantier est
> documenté pour mémoire et n'est pas ordonnancé.

**Risque accepté.** Les champs `details_prepare` et `editable_prepare`
(`admin/forms/form.xml:127,147`, `filter="raw"`) sont exécutés par `eval()` au
rendu frontend. Toute personne disposant de `core.edit` sur le composant peut
donc exécuter du PHP arbitraire sur le serveur — une escalade de privilège que
Joomla protège habituellement par `core.admin`.

**Atténuation possible sans perdre la fonctionnalité**, si la position évolue :
restreindre l'**écriture** de ces deux champs à `core.admin`. Les gabarits
existants continuent de s'exécuter à l'identique ; seule la modification est
réservée aux Super Utilisateurs. Coût : ~0,5 j.

**Refonte complète, si un jour souhaitée.** L'infrastructure existe déjà
(`replaceTemplateFieldToken()` fait du remplacement `{champ:value}`) : l'étendre
à des directives déclaratives (conditions, boucles, formatage) supprimerait la
surface RCE et rendrait les gabarits analysables statiquement. 12 – 18 j.

---

## I. Chantiers courts hors refonte

À traiter au fil de l'eau, indépendamment du reste :

| Sujet | Localisation | Charge |
|---|---|---:|
| `set_error_handler()` vide global — supprime toutes les notices du site | `ContentbuilderngImageScale.php:30-31` | 0,5 j |
| Sortir la génération du classeur du gabarit (violation MVC) | `site/tmpl/export/default.php` (293 l.) | 2 j |
| Événements `GenericEvent` par chaîne → classes typées | 19 occurrences | 2 j |
| Tâches Scheduler (purge notations, vérifications, journaux) | — | 3 j |
| Service Locator `PermissionService::createFromRuntimeContext()` → conteneur | `ApiController` et 4 autres | 1 j |
| Requête d'état sans filtre `form_id` (peut lire l'état d'un autre formulaire) | `site/tmpl/export/default.php:149-154` | 0,5 j |

---

## J. Transactions

**Constat.** Deux usages dans tout le composant. Ne sont pas transactionnels
alors qu'ils le devraient :

| Opération | Localisation |
|---|---|
| Sauvegarde d'un stockage (`RENAME` + `ALTER` + métadonnées) | `StorageModel.php:634-903` |
| Sauvegarde d'un formulaire (formulaire + éléments + options + assets) | `FormModel.php:944` |
| Copie de formulaire | `FormController.php:899-1034` |
| Synchronisation de colonnes | `DatatableService.php:447-620` |

À traiter **pendant** les chantiers D et E, pas après : ces méthodes seront
déjà ouvertes.

> Le DDL n'étant pas transactionnel sous MySQL/MariaDB, prévoir en plus un
> chemin de compensation explicite pour les `ALTER`/`RENAME`.

---

## Jalons

| Jalon | Contenu | Charge cumulée |
|---|---|---:|
| **M1 — Filet** | A complet, PHPStan 6, PSR-12 en gate global | 15 j |
| **M2 — Surface** | B + I | 25 j |
| **M3 — Fondations** | F + G | 52 j |
| **M4 — Cœur** | D + J | 85 j |
| **M5 — Finition** | E + C | 115 j |

Un jalon ne démarre qu'avec les tests du précédent au vert. M4 est le seul
jalon où une régression fonctionnelle est probable : prévoir une recette
manuelle sur le parcours de soumission complet.

---

## Indicateurs de suivi

À mesurer à chaque jalon :

```bash
# Méthodes > 100 lignes            (départ : 108)
# Erreurs PSR-12                   (départ : 2 377)
admin/vendor/bin/phpcs --report=summary | tail -3

# Lignes de baseline PHPStan       (départ : 1 639)
wc -l phpstan-baseline.neon

# Tests et couverture              (départ : 689 tests)
cd admin && vendor/bin/phpunit --coverage-text
```

**Critère de sortie global :** aucune méthode > 150 lignes, PHPStan niveau 6
sans baseline, PSR-12 sans exclusion, couverture du chemin de soumission
d'enregistrement.
