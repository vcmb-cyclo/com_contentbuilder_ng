# Colonnes d’une Vue et priorité des autorisations

Cette page explique les colonnes **List**, **Search**, **Link**, **Detail**,
**API**, **Edit** et **Publish** de l’onglet Vue de ContentBuilder NG.

## Deux publications différentes

ContentBuilder NG utilise le mot « publication » à deux niveaux distincts :

1. **Publish dans le tableau des champs** publie un champ dans la Vue. Il
   détermine si ce champ peut être utilisé par List, Search, Link, Detail, API
   ou Edit.
2. **Publication d’un enregistrement** détermine l’état publié ou dépublié
   d’une ligne de données. Elle est contrôlée par les options de la Vue et par
   les ACL Publier/Dépublier.

Publier un champ ne donne donc pas à l’utilisateur le droit de publier un
enregistrement.

## Signification des colonnes

| Colonne | Fonction |
| --- | --- |
| **List** | Affiche le champ comme colonne dans la liste. |
| **Search** | Autorise la recherche de la liste à interroger le champ. Un champ peut être recherchable sans être affiché. |
| **Link** | Transforme la valeur affichée en lien vers la fiche Détail. Cette option ne sélectionne pas le contenu de la fiche. |
| **Detail** | Rend le champ disponible dans la fiche Détail. Un template Détail et la permission d’accéder à l’enregistrement restent nécessaires. |
| **API** | Autorise l’exposition ou l’interrogation du champ par l’API, les statistiques et certaines sorties de plugins. Les permissions API continuent de s’appliquer. |
| **Edit** | Rend le champ disponible pour la modification. Un template Modifier et les permissions sur l’enregistrement restent nécessaires. |
| **Publish** | Commutateur principal du champ dans cette Vue. |

## Ordre de priorité

L’autorisation effective suit cet ordre :

1. **Publish du champ** : le champ doit être publié dans la Vue ;
2. **fonction du champ** : List, Search, Link, Detail, API ou Edit doit être
   autorisée ;
3. **menu Joomla** : un menu List View peut réduire certaines fonctions, mais
   ne peut pas dépasser la Vue mère ;
4. **permissions de la Vue et ACL Joomla** : l’utilisateur doit conserver le
   droit d’accéder à l’enregistrement ou d’effectuer l’action ;
5. **état de l’enregistrement** : les options de visibilité des enregistrements
   publiés restent appliquées.

Une couche inférieure peut retirer un droit, jamais réactiver ce qu’une couche
supérieure interdit.

## Champ dépublié et cadenas

Quand **Publish = No**, toutes les fonctions du champ sont inactives. Leurs
valeurs sont cependant conservées afin de retrouver la configuration si le
champ est republié.

Les cadenas affichés dans **Detail** et **Edit** signalent cette situation pour
les deux usages les plus sensibles : exposition d’une valeur et modification
d’une valeur. Ils ne représentent pas une ACL supplémentaire.

## Exemples

### Champ consultable mais absent de la liste

```text
Publish = Yes
List    = No
Search  = Yes
```

Le champ n’apparaît pas comme colonne, mais participe à la recherche.

### Champ modifiable mais non visible en détail

```text
Publish = Yes
Detail  = No
Edit    = Yes
```

Le champ peut être modifié par un utilisateur autorisé, mais sa valeur n’est
pas fournie à la fiche Détail.

### ACL prioritaire

```text
Publish du champ = Yes
Edit du champ    = Yes
ACL Modifier     = No
```

La modification reste interdite.

