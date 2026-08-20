# CBList — spécification fonctionnelle et technique

## Card commune facultative — RC10-B3

`card=h1` à `card=h6` et `card=v1` à `card=v6` utilisent la Card CSS commune
ContentBuilder NG. Sans `card`, le rendu reste inchangé. Le bandeau reprend
uniquement un `title=` explicite et non vide, sans dupliquer le titre intérieur.
`card` est incompatible avec `output=value`.
Le titre des variantes H et V est horizontal et placé au-dessus du contenu.
Les variantes V sont compactes et juxtaposables ; elles passent en pleine
largeur sur petit écran.
`w=33`, `w=66` et `w=100` occupent respectivement une, deux ou trois colonnes
du conteneur `.cb-cards`. La valeur doit être numérique, sans guillemets, et
n'est valide qu'avec `card=`. Sans `w=`, V vaut 33 et H vaut 100. Sur petit
écran, toutes les Cards occupent 100 %. `w` est incompatible avec
`output=value`, comme `card`.

Exemple : `{CBList id=15 title="Inscriptions" card=h1}`.
Exemple : `{CBList id=15 title="Inscriptions" card=v2 w=66}`.

## 1. Statut du document

Ce document est la spécification de référence de `{CBList}` pour les humains et
les assistants de développement (Codex, Claude ou autre).

- Projet : ContentBuilder NG
- Statut : implémentée et évolutive
- Version du document : 1.4
- Dernière mise à jour : 2026-08-20
- Version d'introduction : ContentBuilder NG 6.1.9-RC05
- Version de l'option `limit` : ContentBuilder NG 6.1.10-RC01
- Version de la pagination compacte : ContentBuilder NG 6.1.10-RC02
- Version de `pagination=0`, `output=value` et `offset` : ContentBuilder NG 6.1.10-RC10
- Plateforme : Joomla 6 uniquement
- PHP : 8.3 ou version ultérieure
- Base de données : MySQL ou MariaDB uniquement
- Base fonctionnelle : comportement publié au commit `d9ff1471`
- Évolutions documentées : correction du rétrécissement de l'iframe après une
  pagination vers une page plus courte ; ajout de `limit` ; syntaxe stricte des
  options numériques CBList et CBStats ; pagination compacte et responsive ;
  masquage de la pagination et sortie d'une valeur texte unique
- Exemple d'identifiant de vue dans toute nouvelle documentation : `15`

Cette spécification décrit le contrat à préserver. Une évolution qui modifie ce
contrat doit d'abord mettre à jour ce document et ses critères d'acceptation.

## 2. Objectif

CBList est un plugin de contenu Joomla qui remplace une balise `{CBList ...}`
présente dans un contenu par une vue liste ContentBuilder NG interactive.

La liste intégrée doit :

- réutiliser la configuration et le thème de la vue ContentBuilder NG ;
- conserver les ACL, les filtres et les restrictions de la vue ;
- permettre de réduire les champs et actions visibles sans jamais étendre les
  droits de l'utilisateur ;
- conserver les restrictions `fields` et `actions` pendant la consultation, la
  création, l'édition et les mutations autorisées ;
- afficher des erreurs compréhensibles sans afficher une liste partiellement
  configurée lorsque la balise est invalide ;
- rester utilisable sans JavaScript grâce à un lien de secours.

## 3. Périmètre et exclusions

CBList couvre uniquement l'intégration d'une vue liste ContentBuilder NG dans un
contenu Joomla.

Ne font pas partie de son contrat :

- la création ou la modification d'une vue ContentBuilder NG ;
- l'octroi de permissions supplémentaires ;
- le contournement de la publication, de la langue, de la propriété ou des
  filtres de la vue ;
- le choix d'un thème dans la balise : il n'existe pas d'option `theme=` ;
- une compatibilité avec Joomla antérieur à 6 ou PHP antérieur à 8.3 ;
- une option `itemid=` propre à CBList.

## 4. Syntaxe générale

Syntaxe minimale :

```text
{CBList id=15}
```

Syntaxe complète représentative :

```text
{CBList id=15 fields="Nom|Prenom|Email" title="Liste des inscrits" sort="Nom|Prenom" dir="asc" pagination=25 limit=10 actions="detail|edit|export" layout=cards height=700 loading=lazy}
```

Règles lexicales :

- le nom de balise `CBList` et les noms d'options ne sont pas sensibles à la
  casse ;
- les options utilisent la forme `nom=valeur` ;
- les options numériques CBList `id`, `height`, `pagination`, `limit` et `offset`
  utilisent exclusivement des entiers sans guillemets ;
- la même règle s'applique aux options numériques CBStats `id`, `limit` et à la
  liste numérique `idsum`, par exemple `idsum=15+16` ;
- les valeurs textuelles peuvent être sans guillemets, entre guillemets doubles
  ou entre apostrophes ;
- les valeurs contenant des espaces ou `|` doivent être écrites entre
  guillemets dans les exemples et instructions générés par une IA ;
- une accolade fermante placée dans une valeur entre guillemets ne termine pas
  la balise ;
- les entités HTML, les espaces insécables et le balisage parasite produit par
  un éditeur WYSIWYG sont normalisés avant l'analyse ;
- si une option apparaît plusieurs fois, la dernière valeur analysée prévaut ;
- plusieurs balises CBList peuvent être présentes dans un même contenu.

Le plugin ne réinterprète pas CBList depuis une page du composant
ContentBuilder NG ni depuis une requête déjà marquée comme intégrée. Cette règle
évite les intégrations récursives.

## 5. Options

### 5.1 `id` — obligatoire

- Type : entier strictement positif.
- Exemple : `id=15`.
- La vue doit exister dans ContentBuilder NG.
- Une valeur absente, vide, nulle, négative, décimale ou non numérique est
  invalide.
- Une vue inexistante produit une erreur et aucun cadre de liste.
- L'existence de la ligne de configuration ne remplace pas les contrôles de
  publication et d'accès exécutés ensuite par ContentBuilder NG.

### 5.2 `fields` — facultatif

But : réduire les colonnes visibles et fixer leur ordre.

- Format : sélecteurs séparés uniquement par `|`.
- Exemple : `fields="Nom|Prenom|Email"`.
- Chaque sélecteur est soit le nom source exact d'un élément, soit son
  identifiant de référence exact.
- La comparaison des noms est stricte : casse, accents et orthographe doivent
  être identiques.
- Les libellés traduits ou affichés ne sont pas acceptés.
- Les virgules et points-virgules sont refusés comme séparateurs.
- Les valeurs vides et doublons sont éliminés en conservant l'ordre de la
  première occurrence.
- Maximum : 100 sélecteurs, 255 caractères UTF-8 par sélecteur, aucun caractère
  de contrôle.
- Option absente ou vide : toutes les colonnes déjà visibles dans la vue restent
  disponibles.
- L'option ne peut que retirer et réordonner des colonnes ; elle ne peut jamais
  rendre visible une colonne désactivée.
- Un élément qui existe réellement mais qui est dépublié ou désactivé pour la
  liste est valide : sa colonne est simplement omise.
- Un sélecteur qui ne correspond à aucun élément réel est une erreur.
- Tous les sélecteurs inconnus sont signalés au cours de la même validation.
- En présence d'une erreur, aucune liste ni donnée ne doit être affichée.

### 5.3 `title` — facultatif

- Option absente : conserver le titre configuré dans la vue.
- `title="Liste des inscrits"` : remplacer le titre visible.
- `title=hide` : masquer le titre visible.
- `title=""` : équivalent de `title=hide`.
- Le mot `hide` n'est pas sensible à la casse.
- Même si le titre visible est masqué, l'iframe conserve un titre accessible
  traduit indiquant l'identifiant de la vue.

### 5.4 `sort` et `dir` — facultatifs

But : définir le tri initial tant que l'utilisateur n'a pas choisi un autre tri.

`sort` suit les règles de liste, séparateur et correspondance exacte de
`fields`, avec une restriction supplémentaire : chaque champ trié doit être une
colonne visible et triable de la vue après application de sa configuration.

- Exemple : `sort="Nom|Prenom" dir="asc"`.
- Un champ existant mais dépublié ou non visible est donc accepté dans `fields`
  mais refusé dans `sort`.
- Tous les champs de tri inconnus sont signalés ensemble.
- `dir` accepte uniquement `asc` et `desc`, sans sensibilité à la casse.
- Une direction unique est appliquée à tous les champs de `sort`.
- Pour mélanger les directions, fournir une direction par champ :
  `dir="asc|desc"`.
- Si plusieurs directions sont fournies, leur nombre doit être exactement égal
  au nombre de champs de `sort`.
- La direction par défaut est `asc`.
- `dir` n'a d'effet que si `sort` est renseigné.
- Le tri initial est conservé pendant la pagination.
- Un tri choisi explicitement par l'utilisateur remplace le tri initial CBList.
- Si la validation contextuelle du tri échoue, aucune liste n'est affichée.

### 5.5 `pagination` — facultatif

- Type : entier compris entre 0 et 5 000.
- Exemple : `pagination=25`.
- Option absente : conserver la limite configurée par la vue ou le contexte
  normal de ContentBuilder NG.
- `pagination=0` conserve cette limite et les données affichées, mais masque le
  sélecteur du nombre d'enregistrements en haut ainsi que les contrôles de
  pagination en bas. Aucun défilement ou découpage supplémentaire n'est créé.
- Les formes `pagination="25"` et `pagination='25'` sont invalides.

#### Présentation compacte de la pagination — 6.1.10-RC02

La pagination compacte est un comportement commun de ContentBuilder NG, et non
une seconde pagination propre à CBList. Son contrat complet est défini dans
[Pagination ContentBuilder NG](contentbuilder-pagination.md).

CBList doit uniquement :

- transmettre `pagination` comme taille de page et `cblist_limit` comme plafond
  distinct ;
- conserver son contexte, la recherche, les filtres, le tri, `fields`,
  `actions` et `limit` dans tous les liens de pagination ;
- fournir au modèle commun le total plafonné par `limit` ;
- utiliser sans duplication le rendu compact partagé par les listes CB.

### 5.6 `limit` — facultatif

But : plafonner le nombre de résultats affichables sans définir une taille de
page.

- Type : entier strictement positif compris entre 1 et 5 000, sans guillemets.
- Exemple : `limit=10`.
- `limit="10"`, `limit='10'`, `limit=XX`, `limit=1A`, les nombres décimaux,
  nuls ou négatifs sont invalides.
- L'option est appliquée après les ACL, la recherche, les filtres et le tri
  effectif.
- La recherche porte sur tous les enregistrements accessibles à l'utilisateur,
  et non uniquement sur les lignes définies par `limit`.
- Après ces traitements, CBList conserve au maximum les `limit` premiers
  résultats.
- `limit` n'est pas une pagination et ne remplace jamais `pagination`.
- Si `pagination < limit`, le sous-ensemble limité est paginé normalement.
- Si `pagination >= limit`, aucune navigation entre pages n'est nécessaire.
- Le total utilisé par la pagination est
  `min(nombre de résultats accessibles après filtrage, limit)` ; le total réel
  situé au-delà de `limit` n'est jamais affiché.
- Le résumé utilise le format `1 - 10 sur 10 affichés` en français,
  `1 - 10 of 10 displayed` en anglais et `1 - 10 von 10 angezeigt` en allemand.
- L'export contient tout le sous-ensemble limité, indépendamment de la page
  courante. Par exemple, `limit=100 pagination=20` exporte au maximum les 100
  résultats retenus.
- `limit` doit être conservé dans la recherche, les filtres, le tri, la
  pagination, le détail, l'édition, l'enregistrement, les retours, les mutations
  et l'export au moyen du paramètre distinct `cblist_limit`.
- `limit` n'est pas une frontière de sécurité : toutes les ACL et restrictions
  ContentBuilder NG restent obligatoires.
- Aucun paramètre de masquage du résumé n'est ajouté en 6.1.10-RC01.

Ordre de traitement de référence :

```text
ACL et visibilité → recherche et filtres → tri effectif → limit → pagination → affichage ou export
```

Exemple avec recherche et pagination :

```text
{CBList id=15 fields="Nom|Prenom|Email" title=hide sort=Prenom dir=asc pagination=3 actions=search limit=10}
```

### 5.7 `output=value` et `offset` — facultatifs

But : insérer directement la valeur texte d'un unique champ, sans iframe.

- Syntaxe minimale : `{CBList id=15 fields="Nom" output=value}`.
- `fields` est obligatoire et doit contenir exactement un champ existant.
- Le résultat est du texte Unicode échappé pour le document HTML ; aucun code
  HTML provenant de la donnée n'est exécuté.
- Les ACL, la visibilité, la recherche et tous les filtres ContentBuilder sont
  appliqués avant le tri et la sélection de la valeur.
- Sans `sort`, `dir` ni `offset`, les valeurs implicites sont
  `sort=ID dir=desc offset=0` : la dernière valeur accessible est renvoyée.
- `sort` peut viser un autre élément existant que celui indiqué dans `fields`.
- `offset` est un entier sans guillemets compris entre 0 et 4 999. `offset=1`
  sélectionne le résultat précédant celui désigné par `offset=0`.
- `limit`, s'il est présent, plafonne d'abord les résultats ; un `offset`
  extérieur à ce sous-ensemble ne renvoie rien.
- Aucun résultat ou une valeur absente ne produit aucune sortie.
- `offset` sans `output=value` est invalide.
- Avec `output=value`, les options de présentation `pagination`, `actions`,
  `title`, `layout`, `height` et `loading` sont invalides, même avec leur valeur
  par défaut. L'erreur cite toujours l'option et la valeur reçue.

Exemples :

```text
{CBList id=15 fields="Nom" output=value}
{CBList id=15 fields="Statut" sort="Nom" dir=asc offset=1 output=value}
```

### 5.8 `actions` — facultatif

But : définir une liste d'autorisation de contrôles dans les écrans intégrés de
liste, détail et édition.

- Format : termes séparés uniquement par `|`.
- Les termes ne sont pas sensibles à la casse et sont normalisés en minuscules.
- Les virgules et points-virgules sont refusés.
- Les valeurs vides et doublons sont éliminés.
- Option absente ou vide : ne rien restreindre au-delà des ACL et de la
  configuration de la vue.
- `actions=none` : masquer et interdire tous les contrôles couverts par
  `actions`, y compris recherche, filtres, création, détail, édition,
  suppression, export, notation et impression.
- `none` est exclusif : `actions="none|detail"` est invalide.
- Option présente : masquer ou interdire tous les contrôles non cités.
- Cette option ne doit jamais accorder une capacité refusée par les ACL, la
  propriété, l'état de la vue ou une autre règle ContentBuilder NG.

Vocabulaire exhaustif :

| Terme | Contrôle autorisé |
| --- | --- |
| `search` | Barre de recherche et filtres |
| `state` | Filtre et contrôle du statut de workflow |
| `publish` | Filtre et contrôle de publication/dépublication |
| `language` | Filtre de langue |
| `new` | Création d'un enregistrement |
| `edit` | Ouverture de l'édition d'un enregistrement |
| `delete` | Suppression d'enregistrements |
| `export` | Export |
| `rating` | Affichage et utilisation de la notation |
| `detail` | Ouverture du détail d'un enregistrement |
| `print` | Impression depuis l'écran de détail |

Tout autre terme est invalide et doit être signalé.

`actions=none` ne masque pas le sélecteur du nombre de lignes ni la navigation
de pagination, qui ne sont pas des actions. Pour une liste sans commandes en
haut ou en bas, utiliser :

```text
{CBList id=15 actions=none pagination=0}
```

Lorsque cette combinaison ne laisse aucun contrôle disponible, CBList ne doit
rendre aucun conteneur, panneau ou espace vide à la place de la barre d'outils.

### 5.9 `layout` — facultatif

Valeurs autorisées, sensibles à la casse :

- `default`
- `cards` (alias lisible, résolu en `listcard`)
- `listone`
- `listtwo`
- `listthree`
- `listcard`
- `listcompact`
- `listtiles`

Option absente : conserver la mise en page normale de la vue.

### 5.10 `height` — facultatif

- Type : entier strictement positif compris entre 240 et 5 000 pixels.
- Valeur par défaut : 240 pixels.
- La valeur est la hauteur minimale initiale du cadre.
- Après chargement, le cadre s'agrandit automatiquement selon la hauteur du
  document intégré lorsque la politique de même origine le permet.
- Après une pagination ou un rechargement vers une page contenant moins de
  lignes, le cadre doit également rétrécir jusqu'à la nouvelle hauteur utile,
  sans descendre sous la hauteur minimale configurée.
- La mesure doit être indépendante de la hauteur laissée par la page précédente :
  la hauteur courante de l'iframe ne doit pas devenir un minimum implicite.
- Si l'accès au document intégré est empêché, la hauteur configurée reste le
  repli sûr.
- Un cadre qui contient uniquement des erreurs doit se réduire à la hauteur du
  message, sans conserver artificiellement la hauteur minimale.

### 5.11 `loading` — facultatif

- Valeurs : `lazy` ou `eager`, sans sensibilité à la casse.
- Valeur par défaut : `lazy`.
- `lazy` diffère le chargement à l'approche de la zone visible.
- `eager` charge immédiatement.

## 6. Options inconnues et validation

Les seules options autorisées sont :

```text
id, height, pagination, limit, layout, loading, fields, actions, title, sort, dir, output, offset
```

Une option analysée mais inconnue est une erreur. La validation doit être
cumulative : toutes les erreurs détectables pendant une passe sont affichées,
avec le nom de l'option et sa valeur fautive lorsque cela aide l'utilisateur.

La validation comporte deux niveaux :

1. le plugin valide la syntaxe générale, les options, les bornes et l'existence
   de la vue avant de créer l'iframe ;
2. la vue intégrée valide `fields` et `sort` contre les éléments et colonnes de
   la vue sélectionnée.

Dans les deux cas :

- les messages passent par des clés de traduction ;
- EN, FR et DE sont mis à jour ensemble ;
- un lien vers l'aide syntaxique publique est proposé ;
- les valeurs sont échappées avant affichage ;
- si la configuration est invalide, les résultats, statistiques ou données de
  la liste ne sont pas rendus sous le message.

Limite connue du parseur actuel : du texte résiduel qui n'a pas la forme
`nom=valeur` peut ne pas devenir un attribut et donc ne pas être signalé. Toute
demande visant à rendre ce point plus strict doit ajouter des tests de
compatibilité avant de modifier le parseur.

## 7. Rendu et accessibilité

Pour une balise valide, CBList produit :

- un conteneur pleine largeur ;
- une iframe vers `com_contentbuilderng`, tâche `list.display`, avec
  `tmpl=component` ;
- un identifiant d'iframe unique par occurrence ;
- un attribut `title` accessible, personnalisé ou traduit ;
- un attribut natif `loading` ;
- une hauteur initiale et un redimensionnement automatique ;
- un lien de secours dans `<noscript>` permettant d'ouvrir la liste.

Les ressources CSS et JavaScript doivent être enregistrées et chargées avec le
Web Asset Manager natif de Joomla 6. Le JavaScript doit aussi initialiser les
iframes injectées après un événement `joomla:updated`.

## 8. Contexte de navigation

Une requête CBList porte le marqueur public `cblist_embed=content-plugin`. Ce
marqueur identifie le contexte de rendu ; ce n'est pas un jeton d'autorisation.

Les restrictions `fields`, `actions` et `limit` doivent être conservées dans les
formulaires, liens, redirections et retours nécessaires à :

- la pagination ;
- un tri utilisateur ;
- l'affichage du détail ;
- l'édition et l'enregistrement ;
- la création ;
- la suppression, la publication et les autres mutations autorisées ;
- l'export et l'impression lorsqu'ils sont autorisés.

Le contexte ne doit jamais permettre de contourner une vérification ACL côté
contrôleur ou modèle. Masquer un bouton dans le template ne constitue pas une
protection suffisante.

## 9. Sécurité

Exigences obligatoires :

- toutes les autorisations existantes de ContentBuilder NG restent appliquées ;
- `actions` est toujours combiné par un ET logique avec les autorisations
  existantes ;
- `fields` ne peut jamais réactiver un élément non visible ;
- les contrôleurs de mutation valident eux-mêmes le contexte et les droits ;
- les identifiants, valeurs d'URL et contenus HTML sont typés, encodés ou
  échappés au bon niveau ;
- les URL sont générées avec les API Joomla ;
- les liens ouvrant l'aide dans un nouvel onglet utilisent
  `rel="noopener noreferrer"` ;
- aucun paramètre transmis par le navigateur n'est considéré comme une preuve
  d'autorisation.

## 10. Compatibilité documentaire et traductions

Toute modification fonctionnelle de CBList doit mettre à jour ensemble :

- l'aide du plugin en `en-GB`, `fr-FR` et `de-DE` ;
- l'aide syntaxique publique en `en-GB`, `fr-FR` et `de-DE` ;
- les pages documentaires EN, FR et DE concernées ;
- les exemples, avec `id=15` par défaut ;
- les tests qui vérifient la présence et la validité des traductions et exemples.

Les chaînes visibles ne doivent jamais être écrites directement dans le PHP ou
le JavaScript.

## 11. Critères d'acceptation minimaux

Une évolution CBList n'est terminée que si les scénarios concernés sont couverts
par des tests automatisés et si les invariants suivants restent vrais :

1. `{CBList id=15}` conserve les réglages normaux de la vue.
2. Une vue inexistante affiche une erreur sans iframe de liste.
3. Plusieurs options invalides produisent plusieurs messages en une passe.
4. Une option inconnue est refusée.
5. `fields` conserve l'ordre demandé et ne peut que réduire les colonnes.
6. Un champ réel mais dépublié ou désactivé est accepté dans `fields` puis omis.
7. Un champ inexistant est refusé et aucune liste n'est rendue.
8. Un champ non visible est refusé dans `sort`.
9. Une direction unique est diffusée sur tous les champs triés.
10. Un tableau de directions doit avoir la même longueur que `sort`.
11. Le tri initial survit à la pagination, puis cède la priorité au tri choisi
    par l'utilisateur.
12. `actions` ne peut jamais étendre les ACL.
13. Le vocabulaire des actions est exhaustif et les termes inconnus sont refusés.
14. Le contexte des champs et actions survit aux parcours autorisés.
15. `title=hide` et `title=""` masquent le titre visible sans supprimer le titre
    accessible de l'iframe.
16. Le cadre respecte sa hauteur minimale, grandit et rétrécit avec le contenu,
    notamment entre une avant-dernière page remplie et une dernière page courte,
    et se réduit à la hauteur utile pour un message d'erreur seul.
17. Un lien de secours existe sans JavaScript.
18. Les traductions EN, FR et DE restent complètes et alignées.
19. Les exemples documentés sont analysables par le même service que la
    production.
20. Les tests unitaires, la validation du paquet et le smoke test Joomla passent
    selon le niveau de risque de la modification.
21. Les options numériques CBList et CBStats sont acceptées sans guillemets et
    refusées lorsqu'elles sont citées, avec un message explicite.
22. `limit` est appliqué après ACL, recherche, filtres et tri, puis avant la
    pagination et l'export.
23. La dernière page ne rend jamais plus de lignes que le reliquat de `limit`.
24. Le résumé et la pagination utilisent le total plafonné sans révéler le total
    réel situé au-delà de `limit`.
25. L'export porte sur l'ensemble limité et non sur la seule page courante.
26. `cblist_limit` survit aux parcours intégrés autorisés sans remplacer
    `list[limit]`.
27. CBList utilise la pagination compacte commune de ContentBuilder NG sans
    implémenter un second algorithme ou un second layout concurrent.
28. La navigation compacte conserve l'état de recherche, de filtre, de tri et
    les restrictions `fields`, `actions` et `limit`.
29. Le nombre de pages transmis à la pagination commune est calculé sur le total
    plafonné par `limit`.
30. `pagination=0` masque les deux zones de contrôle de pagination sans modifier
    la taille de page ni le jeu de données.
31. `output=value` ne crée aucune iframe et renvoie uniquement du texte Unicode
    échappé après les ACL, restrictions, recherches, filtres et tri.
32. Le mode valeur impose exactement un champ, utilise par défaut
    `sort=ID dir=desc offset=0` et accepte un champ de tri différent.
33. Un `offset` hors résultat ne renvoie rien ; une syntaxe numérique citée ou
    invalide est refusée avec l'option et sa valeur dans le message.
34. Les options de présentation incompatibles avec `output=value` sont toutes
    signalées en une passe et aucune donnée n'est rendue.
35. `actions=none` interdit toutes les actions intégrées ; combiné à
    `pagination=0`, il ne laisse aucun contrôle ni cadre vide en haut ou en bas.

## 12. Procédure à donner à une IA pour toute évolution

Copier le bloc suivant dans la demande, puis ajouter le changement souhaité :

```text
Travaille uniquement dans le dépôt ContentBuilder NG indiqué.
Respecte AGENTS.md et CBLIST_SPECIFICATION.md.
Joomla 6, PHP 8.3+, MySQL/MariaDB uniquement ; aucune compatibilité legacy.

Avant toute modification :
1. reformule le comportement demandé et les critères d'acceptation concernés ;
2. inspecte uniquement les fichiers CBList strictement nécessaires ;
3. signale toute contradiction entre la demande, la spécification, les tests et
   l'implémentation actuelle.

Pendant l'implémentation :
4. conserve les ACL et le principe selon lequel fields/actions ne peuvent que
   réduire les capacités ;
5. utilise les API natives Joomla 6 et respecte MVC ;
6. ajoute ou adapte d'abord les tests de régression pertinents ;
7. garde les modifications minimales et ciblées ;
8. mets à jour ensemble les chaînes et aides EN, FR et DE ;
9. utilise id=15 dans les exemples génériques et mets entre guillemets les
   valeurs contenant des espaces ou | ;
10. ne modifie pas output/, tmp/, les ZIP locaux, le serveur Joomla Update, un
    tag Git ou une release.

Avant de terminer :
11. exécute les tests ciblés puis la suite complète appropriée ;
12. vérifie la documentation, les traductions, l'échappement, l'accessibilité et
    la conservation du contexte ;
13. résume les fichiers modifiés, les tests exécutés et tout risque résiduel ;
14. ne crée ni tag, ni release, ni déclenchement Joomla Update sans demande
    explicite.
```

## 13. Processus recommandé pour une nouvelle fonctionnalité comparable

Pour CBList, CBStats ou une future balise, l'ordre normal est :

1. **Besoin** — décrire le problème utilisateur et les résultats attendus.
2. **Inventaire** — identifier le comportement existant, les contraintes Joomla,
   les ACL, la navigation, les traductions et les tests concernés.
3. **Contrat fonctionnel** — fixer syntaxe, options, valeurs par défaut, bornes,
   erreurs, cas limites, non-objectifs et compatibilité.
4. **Exemples et matrices** — écrire des exemples valides/invalides et les
   décisions pour les cas ambigus.
5. **Critères d'acceptation** — transformer chaque règle importante en scénario
   vérifiable.
6. **Conception technique** — répartir les responsabilités entre parseur,
   service, modèle, vue, template, contrôleur, assets et traductions.
7. **Tests de régression** — écrire les tests qui échouent avant la correction ou
   qui verrouillent le nouveau contrat.
8. **Implémentation minimale** — modifier uniquement les couches nécessaires.
9. **Documentation multilingue** — aligner aide, documentation et exemples EN,
   FR et DE.
10. **Validation** — tests ciblés, suite complète, paquet, installation Joomla et
    parcours navigateur selon le risque.
11. **Identification RC locale** — tout paquet de recette utilise strictement la
    version `x.y.z-RCxx` dans son nom, le manifeste du composant, les manifestes
    des plugins et l'écran About ; le fichier d'offre Joomla Update reste sur la
    version stable tant qu'un déclenchement explicite n'est pas demandé.
12. **Revue** — vérifier sécurité, ACL, accessibilité, erreurs cumulatives et
    absence de changements hors périmètre.
13. **Publication séparée** — commit, tag, pré-release et Joomla Update restent
    des décisions explicites distinctes.

Ce processus n'interdit pas les ajustements découverts pendant le développement,
mais chaque ajustement doit revenir dans le contrat et les tests afin d'éviter que
le code devienne la seule spécification.
