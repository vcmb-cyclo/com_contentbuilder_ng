# ContentBuilder NG — sélection des champs Edit et Export

## Règle générale

Publié est l'interrupteur maître d'un élément. Un élément dépublié n'est jamais
affiché, modifié, exposé par API ni exporté, même si une capacité spécialisée
reste enregistrée.

## Template Edit

Le générateur Edit des thèmes Thoth, Dark, Khepri et Blank inclut uniquement
les éléments publiés dont la capacité Edit est active. Chaque élément utilise
les marqueurs label et item. Les éléments non modifiables ne sont pas ajoutés
automatiquement en lecture seule.

Un template non verrouillé peut toujours contenir des marqueurs value ajoutés
manuellement. Un template verrouillé est régénéré selon la sélection Edit à
chaque enregistrement.

## Export des éléments

La capacité Export est activée par défaut. Un élément est exporté uniquement
s'il est à la fois publié et activé pour Export. La grille Vue propose une
colonne Export, disponible dans son sélecteur de colonnes, ainsi que les actions
groupées Exporter et Ne pas exporter.

Publié reste l'interrupteur maître du rendu de la colonne Export :

- publié avec Export actif : coche verte cliquable ;
- publié avec Export inactif : croix cliquable ;
- dépublié avec Export mémorisé actif : cadenas non cliquable ;
- dépublié avec Export mémorisé inactif : croix grisée non cliquable.

La publication ou dépublication actualise immédiatement ce rendu. Republier
l'élément restaure sa capacité Export mémorisée sans la modifier.

Les restrictions de contexte continuent de s'appliquer : ACL, filtres,
recherche, limites CBList, fields CBList et colonnes de menu.

## Colonnes système

Options propose un sélecteur compact et indépendant de l'affichage frontend
pour ID, État et Publié. Les valeurs par défaut des nouvelles vues sont :

- ID : oui ;
- État : non ;
- Publié : non.

Lors de la migration d'une vue existante, chaque choix reprend le réglage
d'affichage correspondant afin de conserver exactement son ancien export.
