# Harmonisation de la syntaxe CBStats et CBList

Les deux plugins utilisent la même syntaxe de présentation lorsque celle-ci s’applique :

```text
labels="title=..."
hide="title"
card=h1|h6|v1|v6
w=33|66|100
```

`labels` fournit le titre de la vue et de la Card. CBList accepte uniquement la clé `title`. CBStats accepte également les clés `category`, `value` et `total` pour les résultats statistiques. `hide="title"` masque uniquement le titre de la vue ou de la Card ; il ne masque ni les colonnes, ni les actions, ni la pagination de CBList.

L’ordre recommandé dans les exemples est : source (`id`/`idsum`), champs et filtres, regroupement, sortie, labels et hide, tri, limites, puis mise en page et présentation en Card.

Dans CBStats, `groups` définit des groupes de valeurs ; les intervalles ne sont qu’une forme possible. Les groupes discrets, comme `1,2,7,9=Groupe 1`, sont également valides. `groupset` charge la même définition depuis un fichier INI réutilisable.

`config="nom-du-fichier.ini"` charge une configuration de présentation CBStats réutilisable. Les sections autorisées sont `[labels]` (`title`, `category`, `value`, `total`), `[presentation]` (`background`, `card`, `w`, `width`, `height`) et `[display]` (`hide`, `sort`, `dir`, `limit`). `w` accepte uniquement `33`, `66` ou `100` et règle la largeur de la Card ; `width` règle celle du graphique. Les options écrites directement dans la balise surchargent le fichier clé par clé.

```text
{CBStats id=15 field=Distance output=bar config="vcmb-config.ini" width=800 labels="title=Parcours spécial"}
```

Les exemples utilisent l’identifiant de vue `15` comme valeur indicative.

Les noms techniques des balises, des options et les valeurs énumérées ignorent la casse (`{cbstats ...}`, `OUTPUT=BAR`, `dir=Asc`). Les données utilisateur, comme les noms de champs, les valeurs de filtre, les valeurs de groupes et les libellés, restent sensibles à la casse.
