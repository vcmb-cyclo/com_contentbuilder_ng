# Menu Joomla — List View (Classic)

## 1. Statut du document

Cette spécification décrit le contrat fonctionnel et technique du type de menu
historique ContentBuilder NG « List View », renommé « List View (Classic) » à
partir du cycle 6.1.10-RC09.

- Projet : ContentBuilder NG
- Statut : comportement de référence à préserver
- Version du document : 1.0
- Dernière mise à jour : 2026-08-11
- Plateforme : Joomla 6 uniquement
- PHP : 8.3 ou version ultérieure
- Base de données : MySQL ou MariaDB uniquement
- Layout Joomla : `list/listclassic`
- Fichier de paramètres : `site/tmpl/list/listclassic.xml`
- Rendu frontend initial : `site/tmpl/list/listclassic.php`

Le nouveau type « List View » utilise le layout Joomla par défaut. Le layout
Classic est conservé temporairement comme référence et solution de repli
pendant le cycle RC09.

## 2. Objectif

« List View (Classic) » affiche une vue ContentBuilder NG publiée dans un
élément de menu Joomla. Il fournit le rendu historique en tableau et permet au
menu de surcharger un ensemble limité d'options de la Vue CB.

Le type Classic doit rester stable. Une évolution destinée uniquement au
nouveau « List View » ne doit pas modifier :

- les paramètres enregistrés par les anciens éléments de menu ;
- la résolution des valeurs héritées ;
- les filtres de menu ;
- les permissions et restrictions de la Vue ;
- la pagination, la recherche, le tri ou l'export ;
- le rendu Classic activé par `listclassic.php`.

## 3. Compatibilité des éléments de menu existants

Les éléments existants conservent leur cible Joomla :

```text
option=com_contentbuilderng&view=list&layout=default
```

À partir de RC09, cette cible désigne le nouveau List View. Les anciens menus
sont donc adoptés automatiquement par le nouveau type sans migration SQL ni
réécriture de leur lien. Ce choix est volontaire : la grande majorité des
menus existants n'utilise pas les filtres avancés et doit bénéficier directement
des nouveaux paramètres et rendus.

Un utilisateur qui souhaite conserver temporairement le contrat historique
peut sélectionner explicitement « List View (classic) », dont la cible est :

```text
option=com_contentbuilderng&view=list&layout=listclassic
```

Lorsqu'un ancien filtre Classic est détecté, le nouveau List View doit informer
l'administrateur et proposer soit de conserver Classic, soit de repartir du
nouveau contrat. Le choix explicite du nouveau List View, suivi d'une
sauvegarde, supprime définitivement les anciens paramètres de filtre et
d'ordre. Aucune donnée ne doit être supprimée avant cette sauvegarde.

## 4. Sélection de la Vue

Le paramètre `form_id` est obligatoire.

- La liste commence par « Choisir une vue » et ne sélectionne jamais
  silencieusement la première Vue.
- Les Vues conservent leur ordre de tri ContentBuilder NG.
- Une sauvegarde sans Vue sélectionnée doit produire l'erreur de validation
  Joomla traduite.
- La Vue sélectionnée doit être conservée lors d'une mise à jour du composant
  et lors d'un Reset des options du menu.
- Le menu ne peut jamais étendre les permissions définies par la Vue.

## 5. Héritage et surcharges

Les options de menu utilisent les contrôles Joomla ContentBuilder NG communs.
Le libellé d'une valeur héritée doit afficher sa valeur résolue, par exemple :

```text
Use Default (20)
Use Default (Yes)
Use Default (thoth)
```

Le stockage doit rester compatible avec les valeurs historiques :

- `-1` : héritage pour les options booléennes ;
- `0` : Non, sauf pour la limite de liste où `0` signifie All ;
- `1` : Oui ;
- valeur vide : héritage pour le thème et la limite du menu ;
- `-2` : héritage de la catégorie sélectionnée.

Une valeur explicite dans le menu est une surcharge locale. Une valeur héritée
est résolue depuis la Vue, puis depuis la configuration globale lorsque le
paramètre concerné le prévoit.

## 6. Reset to Default

Le Reset supprime réellement les surcharges du menu. Il ne copie pas les
valeurs courantes de la Vue dans le menu.

Il doit :

- conserver `form_id` ;
- supprimer les surcharges déclarées par `data-cb-menu-override="true"` ;
- vider les filtres et leur ordre associés au menu ;
- rétablir l'héritage dynamique depuis la Vue ;
- déclencher l'état modifié du formulaire Joomla afin de permettre la
  sauvegarde.

## 7. Groupes et paramètres

L'ordre des groupes est contractuel.

### 7.1 List View (Classic)

- `form_id` : Vue CB obligatoire ;
- `cb_theme_plugin` : thème, hérité de la Vue par défaut ;
- `cb_menu_reset` : suppression des surcharges.

### 7.2 Detail

- `cb_show_details_top_bar` ;
- `cb_show_details_bottom_bar`.

### 7.3 Edit

- `cb_show_top_bar` ;
- `cb_show_bottom_bar`.

### 7.4 Others

- `cb_show_details_back_button` ;
- `cb_list_limit`.

La limite de liste respecte le contrat partagé :

- valeur vide : héritage ;
- `0` : All ;
- entier positif : limite explicite ou personnalisée ;
- choix issus exclusivement de la configuration globale
  `pagination_choices`.

### 7.5 Article

- `cb_show_author` ;
- `cb_category_id` ;
- `cb_category_menu_filter` ;
- `cb_filter_in_title` ;
- `cb_prefix_in_title`.

### 7.6 Filter

- `cb_list_filterhidden` : stockage des valeurs de filtre ;
- `cb_list_orderhidden` : position des valeurs filtrées dans le titre ;
- `cb_list_filter` : interface native de saisie.

La syntaxe de filtre existante est conservée :

- valeur simple : `peter` ;
- alternatives : `peter|paul|mary` ;
- joker : `*` ;
- sortie groupée : `value1,value2` ;
- ordre dans le titre : entier positif.

Le parser, le format stocké et la logique de filtrage ne doivent pas être
modifiés par une évolution ergonomique du menu.

## 8. Rendu frontend

Le layout Classic réutilise la Vue, son thème, ses ACL et ses paramètres, puis
applique uniquement les surcharges explicites du menu.

Le contrat inclut :

- tableau historique ContentBuilder NG ;
- recherche et filtres autorisés par la Vue ;
- tri ;
- pagination et choix de limite partagés ;
- valeur All ;
- actions Detail, Edit et suppression selon permissions ;
- export selon permissions ;
- compatibilité avec les liens de retour administrateur et frontend ;
- décodage correct des entités dans les valeurs de groupes.

## 9. Langues et interface Joomla

- Aucun texte ne doit être codé en dur.
- Les chaînes de type de menu doivent être disponibles en `en-GB`, `fr-FR`
  et `de-DE` dans les domaines chargés par le sélecteur Joomla.
- Les champs utilisent les FormFields et layouts Joomla natifs ou les champs
  ContentBuilder NG communs déjà validés.
- Aucun CSS ne doit imiter un contrôle Joomla natif.

## 10. Séparation avec le nouveau List View

Le nouveau type utilise :

```text
option=com_contentbuilderng&view=list&layout=default
```

En RC09-B1, son XML et son rendu sont fonctionnellement identiques au Classic.
Les futures spécifications du nouveau List View doivent énumérer explicitement
leurs différences par rapport au présent document.

Une modification propre au nouveau List View doit rester dans son layout, ses
paramètres ou des services communs rétrocompatibles. Elle ne doit pas changer
le contrat Classic par effet de bord.

## 11. Critères d'acceptation

1. Le sélecteur Joomla affiche séparément « List View » et
   « List View (Classic) ».
2. Un ancien menu `layout=default` ouvre le nouveau List View sans migration SQL.
3. Un nouveau menu Classic sauvegarde et réouvre la Vue et les surcharges.
4. Le Reset conserve la Vue et supprime les surcharges, filtres compris.
5. Les valeurs héritées affichent leur valeur réellement résolue.
6. Les limites Default, Custom et All restent fonctionnelles.
7. Recherche, filtres, tri, pagination, export et actions ne régressent pas.
8. Les libellés sont traduits en anglais, français et allemand.
9. Le layout `default` donne initialement le même rendu que Classic.
10. Un filtre historique est conservé avec Classic et supprimé uniquement
    après choix et sauvegarde explicites du nouveau List View.
