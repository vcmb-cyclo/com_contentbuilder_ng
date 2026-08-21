# ContentBuilder NG — aide contextuelle de la vue

## Version

- Implémentation initiale : 6.1.10-RC11-B13
- Plateforme : Joomla 6

## Règle fonctionnelle

Le bouton **Help** de l'éditeur de vue ouvre la rubrique correspondant à l'onglet actif. L'identifiant de l'onglet est transmis par le navigateur à la page d'aide ; une valeur absente ou inconnue ouvre la vue d'ensemble.

Chaque rubrique contient obligatoirement :

- l'objectif de l'onglet ;
- une méthode de travail concise ;
- les contrôles essentiels avant publication ;
- le principal risque ou piège ;
- pour États, Détail et Édition, la signification complète des indicateurs.

Une navigation compacte permet d'ouvrir directement les autres rubriques sans revenir à l'éditeur. Les onglets opérationnels ne dupliquent pas ces explications.

## Rubriques

Vue, Options, Introduction, États, Détail, Édition, Article, API, E-mails, Permissions, Audit, Performance, Données et Debug sont couverts. Les rubriques conditionnelles restent accessibles depuis la navigation d'aide même lorsqu'elles ne sont pas affichées dans la configuration courante.

## Traductions

Tous les contenus et libellés sont maintenus ensemble en anglais, français et allemand.
