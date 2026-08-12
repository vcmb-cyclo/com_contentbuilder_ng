# Menu Joomla — List View

## 1. Statut du document

Cette spécification décrit le nouveau type de menu ContentBuilder NG
« List View », type par défaut à partir du cycle 6.1.10-RC09.

- Projet : ContentBuilder NG
- Statut : architecture fonctionnelle validée, corrections fonctionnelles et UX intégrées dans RC09-B7
- Version du document : 1.4
- Dernière mise à jour : 2026-08-12
- Plateforme : Joomla 6 uniquement
- PHP : 8.3 ou version ultérieure
- Base de données : MySQL ou MariaDB uniquement
- Layout Joomla : `list/default`
- Fichier de paramètres : `site/tmpl/list/default.xml`
- Rendu frontend : présentation tabulaire actuelle du List View Classic

Le nouveau List View ne remplace pas les layouts Cards, Compact Table ou
Tiles. Ces types de menu restent séparés et inchangés en RC09.

## 2. Objectif

Le nouveau List View permet de créer plusieurs listes spécialisées à partir
d'une même Vue ContentBuilder NG, par exemple :

- liste de gestion ;
- liste publique ;
- liste d'adhérents ;
- liste de statistiques.

Il reprend uniquement les fonctions utiles de CBList dans une interface
Joomla native. Il ne reproduit ni l'intégration iframe, ni `loading`, ni
`height`, et ne remplace pas le plugin CBList.

Le rendu initial reste le tableau ContentBuilder NG actuel. Le thème peut être
hérité de la Vue ou surchargé par le menu.

## 3. Sélection de la Vue et thème

### 3.1 Vue

Le paramètre `form_id` est obligatoire.

- La liste commence par « Choisir une vue ».
- La première Vue ne doit jamais être sélectionnée silencieusement.
- L'ordre des Vues est celui de ContentBuilder NG.
- Les Vues non publiées restent visibles dans le sélecteur d'administration
  afin de permettre la préparation d'un menu avant publication.
- Une sauvegarde sans Vue sélectionnée produit l'erreur Joomla traduite.
- La Vue sélectionnée est conservée par Reset.

### 3.2 Thème

Le thème utilise le contrôle commun validé en RC08 :

- `Use Default (<theme de la Vue>)` ;
- choix explicite parmi les thèmes disponibles.

Les layouts Cards, Compact Table et Tiles ne deviennent pas des thèmes du
nouveau List View.

## 4. Titre visible de la liste

Le paramètre ne concerne que le titre ContentBuilder NG rendu au-dessus de la
liste. Il ne modifie ni le titre de l'élément de menu Joomla, ni Page Heading,
ni les métadonnées.

Modes :

- `Default (Vue)` ;
- `Custom` ;
- `Hidden`.

Règles du titre personnalisé :

- maximum 255 points de code Unicode ;
- suppression des espaces Unicode en début et fin ;
- texte Unicode et emoji autorisés ;
- HTML interdit ;
- caractères de contrôle invisibles rejetés ;
- sortie échappée ;
- une valeur vide après normalisation est invalide ;
- masquer le titre exige le mode explicite `Hidden`.

## 5. Colonnes affichées, capacités et filtres fixes

### 5.1 Source des champs

La Vue reste la source d'autorité.

- Tout champ publié de la Vue peut devenir une colonne du menu.
- Le mode Default reprend les réglages List, Search, Link, Detail, Edit et
  Published de la Vue. Le mode Custom peut uniquement les restreindre pour ce
  menu sans modifier la Vue.
- Un champ non publié reste indisponible et ne peut jamais être réactivé par le
  menu.
- Tout champ actif et techniquement filtrable peut servir de filtre fixe, même
  s'il n'est pas affiché comme colonne.
- Les titres de colonnes restent ceux définis dans la Vue. Le menu ne propose
  pas d'alias de titre par colonne.

### 5.2 Mode d'affichage

Deux modes sont disponibles :

- `Default (Vue)` : reprend exactement les colonnes visibles et leur ordre ;
- `Custom` : permet de choisir un sous-ensemble autorisé et son ordre propre au
  menu.

### 5.3 Interface RC09-B6

Un tableau Joomla unifié intitulé « Colonnes affichées » est placé en bas de
l'onglet. Il reprend les indicateurs de la Vue afin que l'administrateur voie
immédiatement le plafond applicable au menu :

| Ordre | Champ / Libellé | Liste | Recherche | Lien | Detail | Modifier | Publié | Filtre de données |
|---|---|---|---|---|---|---|---|---|
| déplacement | libellé de la Vue | choix restrictif | choix restrictif | choix restrictif | choix restrictif | choix restrictif | choix restrictif | valeur facultative |

Comportement attendu :

- en mode `Default (Vue)`, l'affichage et l'ordre sont visibles mais
  verrouillés ;
- en mode `Custom`, les colonnes autorisées sont activables et réordonnables
  par glisser-déposer ;
- un champ non affiché peut recevoir un filtre fixe ;
- une recherche interne permet de retrouver un champ dans une Vue volumineuse ;
- Liste, Recherche, Lien, Detail, Modifier et Publié reprennent le plafond
  défini par la Vue mère ;
- si une capacité vaut Yes dans la Vue, le menu peut la désactiver puis la
  réactiver dans la limite autorisée par la Vue ;
- si une capacité vaut No dans la Vue, sa case est décochée, désactivée et ne
  peut jamais être activée par le menu ;
- les libellés et l'ordre Champ / Libellé, Liste, Recherche, Lien, Detail,
  Modifier, Publié correspondent à ceux de `CB → View` ;
- chaque libellé possède une explication accessible par infobulle ;
- le champ de filtre utilise le message « Add a data filter » ;
- une commande permet de n'afficher dans l'éditeur que les colonnes
  sélectionnées ;
- aucun numéro `Order` n'est saisi manuellement ;
- l'ordre des lignes de filtres n'a aucun effet sur les données.

Cette présentation reste ajustable visuellement sans modifier le contrat
fonctionnel validé.

### 5.4 Syntaxe des filtres fixes

Les filtres sont définis par l'administrateur du menu, invisibles et non
modifiables sur le frontend.

La syntaxe RC08 est conservée :

- valeur simple : `Validé` ;
- commence par : `Route 1*` ;
- finit par : `*Gravel` ;
- contient : `*100*` ;
- alternatives : `Route 100 km|Route 150 km` ;
- combinaison : `Route 1*| *Gravel`.

La normalisation supprime les espaces placés autour de `|`. La dernière valeur
d'exemple devient donc `Route 1*|*Gravel`.

Règles de combinaison :

- `OU` entre les alternatives d'un même champ ;
- `ET` entre les filtres de champs différents.

Les filtres sont appliqués après les ACL et avant le tri, la limite totale et
la pagination.

## 6. Recherche frontend

La recherche frontend est distincte des filtres fixes.

### 6.1 Affichage

`Show Search` propose :

- `Use Default (Vue)` ;
- `Yes` ;
- `No`.

### 6.2 Portée

Le sélecteur séparé `Search fields` est supprimé. Lorsque les colonnes sont en
mode Custom, la colonne Recherche du tableau permet de choisir directement les
champs publiés recherchables pour ce menu. Ce choix peut surcharger le réglage
Search de la Vue, sans pouvoir utiliser un champ non publié.

Un champ peut participer à la recherche sans être affiché comme colonne. La
surcharge reste propre au menu et ne modifie jamais la configuration de la Vue.

## 6.3 Lien, champ Detail et template

La colonne `Lien` décide uniquement si une colonne de liste ouvre la fiche
Detail. La nouvelle capacité `Detail` décide si la valeur d'un champ peut être
injectée dans cette fiche.

Dans `CB → View`, `Detail` est placé juste avant `Edit` et constitue une
autorisation de la Vue mère :

- tout champ publié existant ou nouvellement créé utilise `Detail = Yes` par
  défaut afin de conserver le comportement historique ;
- `Detail = No` interdit effectivement l'affichage de la valeur, même si un
  ancien template contient encore son marqueur ;
- un template Detail verrouillé est généré uniquement avec les champs
  `Published = Yes` et `Detail = Yes` ;
- un template manuel non verrouillé n'est pas réécrit automatiquement, mais
  `Detail = No` empêche quand même l'injection de la donnée ;
- le template reste responsable de la structure, de l'ordre et de la mise en
  page des champs autorisés.

Le menu peut désactiver `Detail` pour sa liste spécialisée, mais ne peut pas
l'activer lorsque la Vue mère l'interdit. Cette restriction doit être appliquée
côté serveur au rendu de la fiche Detail.

Infobulle anglaise du libellé `Detail` :

> Detail displays selected fields of a record. Fields are selected in the
> View tab and require a template configured in the Detail tab. A menu item
> may hide a field enabled by the parent View, but cannot enable a field
> disabled by that View.

### 6.4 Relation Published, Detail et Edit

`Published` est l'autorisation principale d'un champ :

- lorsque `Published = No`, `Detail` et `Edit` sont sans effet et présentés
  comme indisponibles ;
- les valeurs Detail et Edit enregistrées sont conservées afin d'être
  restaurées si le champ est republié ;
- dans un menu, désactiver Published neutralise pour ce menu Liste, Recherche,
  Lien, Detail, Modifier et le filtre de données du champ ;
- cette restriction locale ne modifie jamais la Vue mère.

La valeur effective d'une capacité suit toujours la règle : autorisation de
la Vue ET autorisation du menu.

## 7. Tri initial

Le tri initial est séparé de l'ordre horizontal des colonnes et des filtres.

Modes :

- `Default (Vue)` ;
- `Custom`.

Le mode personnalisé reprend le principe de
`CB → View → Options → Sorting → Initial Sort Order` :

- jusqu'à trois critères ordonnés ;
- `ID` toujours disponible, même s'il n'est pas affiché ;
- champs actifs techniquement triables, visibles ou non ;
- aucun champ dupliqué ;
- direction `ASC` ou `DESC` propre à chaque critère.

Le visiteur peut ensuite retrier la liste en cliquant sur un titre de colonne
compatible. Ce tri interactif ne modifie pas le paramètre du menu.

## 8. Limite totale et pagination

### 8.1 Limite totale

La limite totale est distincte du nombre de lignes par page.

La Vue ContentBuilder NG définit `Maximum records` immédiatement sous
`Pagination size`. Sa valeur usine est `All` : aucune limite totale.

Le menu utilise le même contrôle Joomla :

- `Use Default (<valeur de la Vue>)` ;
- valeurs standard de pagination ;
- `All` ;
- saisie Custom.

Le menu peut réduire ou supprimer cette limite pour sa liste spécialisée sans
modifier la Vue. `All` est stocké comme `0` et l'héritage du menu comme `-1`.

Ordre d'application :

1. ACL et permissions ;
2. recherche ;
3. filtres ;
4. tri ;
5. limite totale ;
6. pagination.

L'export et la pagination travaillent sur le sous-ensemble limité.

### 8.2 Nombre de lignes par page

Le libellé `Initial list limit` est remplacé dans `CB → View → Options` et
dans le menu Joomla par `Default page size`.

`Default page size` conserve le contrôle partagé RC06 :

- héritage de la Vue puis de la configuration globale ;
- `0` = All ;
- entier positif = valeur explicite ou Custom ;
- valeurs issues de `pagination_choices`.

`List limit selector` utilise `Use Default (Yes/No) / Yes / No`.

Le contrôle `Maximum records` utilise le même composant Joomla à liste
déroulante et saisie Custom que `Default page size`.

Le libellé reste court. Son aide inline explique précisément qu'il s'agit du
nombre d'enregistrements affichés par page à l'ouverture de la liste, que la
valeur peut être héritée de la configuration globale puis surchargée par un
menu Joomla, et que `All` affiche la liste complète sans pagination.

## 9. Actions d'interface

Les options d'interface utilisent individuellement :

- `Use Default (Yes/No)` ;
- `Yes` ;
- `No`.

Actions concernées :

- Export ;
- Print ;
- Rating, uniquement lorsque la Vue fournit déjà cette fonction.

`Back button` et `Default page size` ne sont pas dupliqués dans ce groupe :
ils conservent les contrôles communs déjà validés en RC08/RC06.

Toute option `Use Default` affiche la valeur réellement héritée de la Vue,
par exemple `Use Default (Yes)` ou `Use Default (No)`.

Les listes Oui/Non utilisent l'état couleur Joomla validé en RC08 : Oui vert,
Non rouge, héritage neutre.

Les options `State` et `State filter` proposent séparément
`Use Default (Yes/No) / Yes / No`.

Une option d'affichage ne contourne jamais une permission applicable et ne
crée pas une fonctionnalité absente de la Vue.

## 10. Restrictions de sécurité du menu

La Vue et les ACL Joomla constituent toujours le plafond des droits. Le menu
peut ajouter une interdiction, mais ne peut jamais accorder un droit.

Chaque opération protégée propose uniquement :

- `Use Default (Yes/No)`, où la valeur indique si la Vue accorde cette
  permission à au moins un groupe frontend ou au propriétaire du record ;
- `Disabled`.

`Disabled` utilise l'état rouge des listes Joomla. L'héritage reste neutre.

Restrictions individuelles :

- Create ;
- Detail ;
- Edit ;
- Delete ;
- Publish/Unpublish ;
- State actions.

Une restriction `Disabled` doit être contrôlée côté serveur. Masquer seulement
le bouton est insuffisant. Un administrateur autorisé à modifier un menu ne
peut pas s'attribuer une permission ContentBuilder NG par ce menu.

Lorsque Detail est désactivé, aucun lien vers la fiche détaillée n'est
généré dans la liste.

Le libellé visible de l'ACL historique `View` devient le mot unique `Detail`.
Sa clé technique reste `view` afin de conserver toutes les permissions
existantes. `List Access` contrôle l'accès à la liste ; `Detail` contrôle
l'ouverture et l'affichage d'un enregistrement individuel.

Dans le tableau des colonnes, une case héritée non modifiable reçoit un fond
gris et une infobulle explicative. Une capacité absente de la Vue reste
verrouillée et ne peut jamais être activée dans le menu.

## 11. Export

L'export conserve le mécanisme actuel et respecte le contrat du nouveau menu :

- uniquement les colonnes affichées ;
- valeurs des états lorsque les états sont présents ;
- ACL ;
- filtres fixes ;
- recherche frontend ;
- tri courant ou initial ;
- limite totale ;
- toutes les lignes du résultat autorisé, pas seulement la page visible ;
- aucune donnée provenant d'un champ masqué.

## 12. Groupes de paramètres

Ordre retenu pour RC09-B6 :

1. **List View**
   - Select View ;
   - Theme ;
   - Reset to Default.
2. **Display**
   - Title.
3. **Data**
   - Initial Sort Order ;
   - Maximum records, hérité de la Vue.
4. **Search and pagination**
   - Show Search ;
   - State ;
   - State filter ;
   - Default page size ;
   - List limit selector.
5. **Actions**
   - actions d'interface ;
   - restrictions de sécurité.
6. Groupes existants **Detail**, **Edit** et **Article**, sans régression.
7. **Displayed columns**, en bas de page.

Le groupe Actions reste unique dans le premier prototype. Son éventuel
découpage sera décidé après validation visuelle.

## 13. Reset to Default

Le Reset du nouveau List View :

- conserve la Vue sélectionnée ;
- supprime réellement toutes les surcharges propres au nouveau menu ;
- rétablit l'héritage dynamique de la Vue ;
- supprime les colonnes personnalisées ;
- supprime les filtres fixes du nouveau menu ;
- supprime tout ancien paramètre de filtre Classic encore présent ;
- déclenche l'état modifié du formulaire Joomla.

Il ne copie jamais les valeurs courantes de la Vue dans le menu.

## 13.1 Persistance Joomla

Le constructeur enregistre toute sa configuration dans `cb_new_config`.

- la synchronisation vers le champ Joomla est assurée par l'asset JavaScript
  externe déclaré dans `joomla.asset.json` ;
- aucun script inline n'est requis ;
- la valeur est resynchronisée avant la soumission du formulaire ;
- après Save ou Save & Close, les valeurs personnalisées sont restaurées à
  l'identique ;
- chaque modification déclenche l'état modifié natif du formulaire Joomla.

## 14. Adoption des anciens menus et nettoyage Classic

Les anciens menus `layout=default` sans filtre Classic adoptent directement le
nouveau List View.

Lorsqu'un ancien filtre Classic est détecté :

- l'administration affiche un message informatif ;
- elle propose `List View (Classic)` pour conserver le comportement existant ;
- elle propose le nouveau `List View` pour repartir du nouveau contrat ;
- le choix Classic conserve les paramètres historiques ;
- le choix New puis la sauvegarde supprime définitivement les anciens
  paramètres de filtre et d'ordre Classic ;
- aucune conversion automatique de la syntaxe historique n'est effectuée ;
- aucun message n'est affiché sur le frontend.

Le choix Classic doit être présenté comme le choix prudent lorsque des filtres
historiques non vides sont détectés. Aucune donnée n'est supprimée avant une
sauvegarde explicite du choix New.

## 15. Interface, langues et aide

- Utiliser les FormFields, layouts, tableaux, boutons et comportements Joomla
  natifs.
- Aucun CSS ne doit imiter un contrôle Joomla.
- Le JavaScript personnalisé doit rester minimal.
- Tous les textes passent par des clés de langue alignées en `en-GB`, `fr-FR`
  et `de-DE`.
- Chaque option possède une aide inline courte.
- Les aides courtes utilisent le mécanisme Joomla `Toggle Inline Help` et sont
  masquées par défaut. L'aide de syntaxe des filtres de données reste visible
  en permanence.
- Les sélecteurs utilisent une largeur limitée à 80 % de la largeur RC08
  précédente, tout en revenant à 100 % de la largeur disponible sur petit
  écran.
- Les exemples de filtres utilisent du code visuellement identifiable.
- Aucun texte technique de stockage n'est exposé à l'utilisateur.

## 16. Critères d'acceptation RC09-B6

1. Le nouveau List View est le type par défaut et le Classic reste sélectionnable.
2. La sélection de Vue est obligatoire et le thème est héritable.
3. Default reprend les colonnes et leur ordre depuis la Vue.
4. Custom sélectionne et réordonne uniquement les colonnes autorisées.
5. Un champ masqué peut filtrer ou participer à la recherche sans être affiché.
6. Les filtres supportent `|` et `*` conformément à RC08.
7. La recherche Default/Custom respecte la portée choisie.
8. Le tri accepte ID et jusqu'à trois champs avec directions indépendantes.
9. La limite totale intervient après filtrage et tri.
10. Pagination, All et Custom ne régressent pas.
11. Les restrictions du menu réduisent les ACL et ne les étendent jamais.
12. L'export ne divulgue aucune colonne masquée et conserve les états présents.
13. Le Reset conserve la Vue et supprime toutes les surcharges New.
14. Le choix New supprime les anciens filtres Classic uniquement à la sauvegarde.
15. L'interface est utilisable sur une Vue comportant de nombreux champs.
16. Le rendu frontend reste celui du tableau actuel avec tri par en-tête.
17. Save et Save & Close conservent toutes les options du constructeur.
18. Le constructeur est aligné sur la largeur des autres groupes Joomla.
19. Les valeurs héritées Oui/Non sont visibles dans les options Use Default.
20. Back button et Default page size ne sont présents qu'une seule fois.
21. Export No masque l'export et Detail Disabled bloque les liens et l'accès direct.
22. Les cases Liste, Recherche, Lien, Detail, Modifier et Publié du mode Custom
    sont modifiables uniquement dans la limite autorisée par la Vue mère.
23. Les sélecteurs sont limités à une largeur lisible et les valeurs Oui/Non utilisent les couleurs Joomla verte/rouge.
24. State et State filter proposent séparément Use Default/Yes/No.
25. Maximum records utilise le contrôle commun à liste déroulante, All et saisie Custom.
26. Les nouvelles options exposent une aide courte via Toggle Inline Help.
27. Detail No empêche toute injection de la valeur dans la fiche, quel que soit
    le contenu historique du template.
28. Un champ Published No ne peut pas être activé dans Detail ou Edit par un
    menu Joomla.
29. L'ACL visible Detail conserve la clé technique historique `view`.

## 17. Hors périmètre RC09-B6

- intégration de Cards, Compact Table ou Tiles dans ce type de menu ;
- suppression des types de menu de layouts spécialisés ;
- iframe, `height`, `loading=lazy` ou `loading=eager` ;
- modification du plugin CBList ;
- ajout de permissions depuis un menu ;
- modification du parser Classic ;
- migration automatique des filtres Classic vers le nouveau format.
