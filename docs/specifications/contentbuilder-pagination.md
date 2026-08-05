# ContentBuilder NG — spécification de la pagination compacte

## 1. Statut du document

Ce document est la spécification de référence de la pagination commune des
listes ContentBuilder NG pour les humains et les assistants de développement.

- Projet : ContentBuilder NG
- Statut : à implémenter
- Version du document : 1.0
- Dernière mise à jour : 2026-08-06
- Version cible : ContentBuilder NG 6.1.10-RC02
- Plateforme : Joomla 6 uniquement
- PHP : 8.3 ou version ultérieure
- Base de données : MySQL ou MariaDB uniquement

## 2. Objectif

Remplacer l'affichage de tous les numéros de pages par une pagination compacte,
stable et commune aux listes ContentBuilder NG normales et intégrées par
CBList.

La pagination doit :

- rester sur une seule ligne ;
- ne provoquer aucun défilement horizontal ;
- rester lisible avec quelques pages comme avec plusieurs milliers ;
- conserver un accès direct au début, à la fin et aux pages proches de la page
  courante ;
- préserver les paramètres, filtres, tris, ACL et restrictions de la liste.

Cette évolution modifie uniquement la présentation et la navigation entre les
pages. Elle ne modifie ni les requêtes métier, ni les ACL, ni la taille de page,
ni le calcul des résultats.

## 3. Périmètre

Le comportement est implémenté dans la pagination commune de ContentBuilder NG.
Il s'applique :

- aux listes ContentBuilder NG ouvertes normalement ;
- aux mêmes listes rendues dans un contenu par CBList ;
- à tous les layouts de liste qui utilisent cette pagination commune.

CBList ne doit pas posséder un second algorithme ni un second layout de
pagination. CBStats n'est pas concerné puisqu'il n'affiche pas de liste paginée.

## 4. Éléments affichés

Lorsque plusieurs pages existent, la pagination peut afficher :

- `<<` : aller à la première page ;
- `<` : aller à la page précédente ;
- les deux premières pages ;
- la page courante ;
- jusqu'à deux pages avant et deux pages après la page courante ;
- les deux dernières pages ;
- `>` : aller à la page suivante ;
- `>>` : aller à la dernière page ;
- une ellipse non cliquable pour chaque intervalle de pages omis.

Un numéro ne doit jamais être rendu deux fois. Les groupes qui se touchent ou se
chevauchent sont fusionnés et les numéros restent strictement croissants.

Les quatre boutons de navigation sont affichés dans le même ordre :

```text
<<  <  numéros de pages  >  >>
```

À la première page, `<<` et `<` sont désactivés. À la dernière page, `>` et `>>`
sont désactivés. Ils restent visibles afin que la disposition ne change pas
brutalement entre deux pages.

## 5. Algorithme de sélection des numéros

La fenêtre locale contient cinq pages consécutives. Elle est centrée sur la page
courante avec deux pages avant et deux pages après. Aux extrémités, elle est
décalée pour conserver cinq numéros : `1 ... 5` au début et les cinq dernières
pages à la fin.

L'ensemble des numéros visibles est l'union de :

```text
{1, 2}
{fenêtre locale de cinq pages}
{total de pages - 1, total de pages}
```

Les valeurs inférieures à 1 ou supérieures au total sont supprimées. Les
doublons sont supprimés, puis les numéros sont triés. Si la liste contient moins
de cinq pages, la fenêtre contient simplement toutes les pages. Une ellipse est
insérée entre deux numéros visibles lorsque leur différence est supérieure à 1.

Exemples avec 50 pages :

```text
Début    : <<  <  [1]  2  3  4  5  …  49  50  >  >>
Page 10  : <<  <  1  2  …  8  9  [10]  11  12  …  49  50  >  >>
Fin      : <<  <  1  2  …  46  47  48  [49]  50  >  >>
```

Exemples avec 20 pages :

```text
Page 1   : <<  <  [1]  2  3  …  19  20  >  >>
Page 10  : <<  <  1  2  …  8  9  [10]  11  12  …  19  20  >  >>
Page 20  : <<  <  1  2  …  18  19  [20]  >  >>
```

Lorsque tous les numéros tiennent dans l'union sans intervalle, ils sont tous
affichés et aucune ellipse n'est ajoutée.

## 6. Mise en page responsive

- Le conteneur de pagination ne revient jamais à la ligne.
- La page, l'iframe et le conteneur ne doivent jamais recevoir de défilement
  horizontal à cause de la pagination.
- Les espacements, dimensions et libellés visuels doivent rester assez compacts
  pour préserver cette règle.
- Les quatre contrôles utilisent les symboles `<<`, `<`, `>` et `>>` ou des
  icônes Joomla équivalentes ; leurs libellés accessibles restent traduits.
- La présentation doit être vérifiée au minimum à 320, 375, 768, 1024 et
  1440 pixels de largeur.
- La pagination ne doit pas être tronquée, superposée ou rendue illisible.

Si la largeur disponible impose une réduction supplémentaire, le rendu peut
masquer progressivement des numéros voisins, mais doit conserver en priorité la
page courante, les boutons précédent et suivant, puis les boutons début et fin.
Cette adaptation reste du code commun ContentBuilder NG et ne doit pas créer une
variante propre à CBList.

## 7. Interaction avec `limit`

Le nombre de pages est calculé sur le total plafonné transmis par CBList :

```text
total paginé = min(nombre de résultats accessibles après filtrage, limit)
```

Avec 1 000 résultats réels :

- `limit=100 pagination=20` produit 5 pages : `1 2 3 4 5` ;
- `limit=10 pagination=20` ne produit aucun contrôle de pagination et le résumé
  affiche `1 - 10 sur 10 affichés` en français ;
- `limit=1000 pagination=20` produit 50 pages présentées avec l'algorithme
  compact.

Sans `limit`, le nombre de pages continue d'utiliser le total normal de la vue
ContentBuilder NG.

## 8. Découpage technique obligatoire

- CBList analyse `limit=` et transmet le plafond dans `cblist_limit`.
- Le modèle de liste ContentBuilder NG applique ce plafond après ACL, recherche,
  filtres et tri.
- Le modèle d'export applique le même plafond.
- La pagination commune reçoit le total normal ou le total plafonné.
- Le layout commun de pagination calcule et affiche les numéros compacts.
- CBList conserve `cblist_limit` dans les recherches, tris, liens, formulaires,
  redirections et retours.

Le calcul des numéros visibles doit être isolé dans une unité testable, sans
dupliquer les règles dans plusieurs layouts.

## 9. Accessibilité et traductions

- La page courante n'est pas un lien et porte `aria-current="page"`.
- Les ellipses ne sont pas des liens et sont ignorées par les technologies
  d'assistance.
- Les contrôles début, précédent, suivant et fin possèdent des libellés
  accessibles traduits.
- Les états désactivés sont transmis sémantiquement et pas uniquement par une
  couleur.
- Le focus clavier reste visible dans les thèmes clair et sombre.
- Toute nouvelle chaîne est ajoutée et alignée en `en-GB`, `fr-FR` et `de-DE`.

## 10. Conservation de l'état et sécurité

Chaque lien conserve les paramètres autorisés de la liste : recherche, filtres,
tri, taille de page et contexte de navigation. Dans CBList, il conserve aussi
`fields`, `actions` et `cblist_limit`.

La pagination ne doit jamais contourner les ACL, modifier le périmètre des
résultats ou faire apparaître le total réel masqué par `limit`.

## 11. Critères d'acceptation

1. Une liste CB normale et la même liste intégrée par CBList utilisent le même
   algorithme et le même layout de pagination.
2. Les deux premières pages, les deux dernières pages et une fenêtre locale de
   cinq pages centrée ou décalée aux extrémités sont sélectionnées sans doublon.
3. Une ellipse non cliquable apparaît exactement pour chaque intervalle omis.
4. `<<`, `<`, `>` et `>>` ciblent respectivement la première, la précédente, la
   suivante et la dernière page.
5. Les boutons impossibles restent visibles mais sont désactivés aux extrémités.
6. La pagination reste sur une seule ligne sans défilement horizontal aux
   largeurs de test définies.
7. La page courante, les ellipses, les états désactivés et le clavier respectent
   les exigences d'accessibilité.
8. La navigation conserve recherche, filtres, tri et restrictions de contexte.
9. Avec `limit`, le nombre de pages et le résumé n'exposent jamais un total
   supérieur au plafond.
10. Si la taille de page est supérieure ou égale au total paginé, aucun contrôle
    de pagination n'est affiché.
11. Les vues sans contexte CBList conservent leur total normal tout en utilisant
    la même présentation compacte.
12. Les tests automatisés couvrent au minimum 1, 2, 5, 6, 20 et 50 pages, ainsi
    que les pages de début, du milieu et de fin.
13. Les traductions EN, FR et DE, les tests unitaires, la validation du paquet et
    le smoke test Joomla passent avant la RC02.
