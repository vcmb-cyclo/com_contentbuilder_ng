# ContentBuilder NG — indicateurs des onglets de vue

## Périmètre

- Version initiale : 6.1.10-RC11-B2
- Convention révisée : 6.1.10-RC11-B14
- Plateforme : Joomla 6
- Écrans concernés : édition d'une vue, onglets Introduction, Détail et Éditer

## Introduction

L'onglet Introduction configure le message d'en-tête affiché au-dessus de la
liste frontend.

- aucun symbole : aucun message n'est défini ;
- coche verte : un message est configuré et affiché.

L'infobulle décrit la fonction et l'état réel de l'onglet.

## Détail et Éditer

L'activité d'un écran dépend des autorisations frontend :

- Détail est actif si une autorisation view est accordée ;
- Éditer est actif si une autorisation edit ou new est accordée.

Les indicateurs signifient :

- aucun symbole : écran non configuré ;
- coche verte : configuration complète et cohérente ; une désactivation
  volontaire du bouton Edit est également conforme ;
- triangle orange : configuration existante mais réellement incomplète ;
- croix rouge : configuration contradictoire ou inutilisable, par exemple un
  écran activé dont le template requis est absent ou invalide.

Pour Détail, l'accessibilité exige le droit frontend View et au moins un champ
publié cliquable. Pour Edit, un écran actif exige le droit correspondant et le
bouton Edit ou Nouveau actif. Lorsque ces points d'entrée sont volontairement
désactivés, leur absence ne constitue pas une anomalie et l'indicateur reste vert.

Le cadenas est complémentaire au symbole : il indique que le template est
verrouillé et régénéré à chaque enregistrement. Il ne masque jamais l'état
fonctionnel. Les combinaisons vert + cadenas, orange + cadenas et rouge + cadenas
sont donc possibles.

Chaque onglet possède une infobulle adaptée à son état. La légende complète et
les rappels de permissions sont centralisés dans l'aide contextuelle ouverte par
le bouton **Aide** ; ils ne prennent pas de place dans les panneaux de travail.

## Critères d'acceptation

1. Les indicateurs correspondent aux autorisations et au contenu enregistrés.
2. Une incohérence bloquante détectée par l'audit produit une croix rouge.
3. Le cadenas et le symbole sont visibles simultanément.
4. Les infobulles expliquent la fonction, l'état et, le cas échéant, le verrou.
5. Les textes anglais, français et allemands restent alignés.
6. Après chaque action enregistrée par AJAX sur un élément de l'onglet Vue,
   l'écran recharge automatiquement la configuration sauvegardée en restaurant
   la position dans la liste. Tous les indicateurs, résultats d'audit et
   templates verrouillés sont ainsi recalculés immédiatement.
Le cadenas des onglets Détail et Édition est affiché immédiatement après leur libellé et aligné sur sa ligne basse. L'indicateur fonctionnel est placé juste au-dessus, à une hauteur identique pour la coche, le triangle et la croix. Le groupe reste compact et ne masque jamais le texte.

## Actions contextuelles Détail et Édition

Le menu **Actions** dépend de l'onglet actif. Dans Détail et Édition, il propose :

- la restauration des barres d'affichage par défaut ;
- la régénération du template depuis les éléments actuels ;
- la désactivation de l'accès frontend, sans supprimer le template ni les réglages des éléments ;
- en mode Debug pour un Super User, une réinitialisation complète qui efface le template et le code Prepare, retire le verrouillage, masque les commandes frontend et retire les droits concernés.

Chaque action demande confirmation et ne devient définitive qu'après l'enregistrement de la vue.

Après cette confirmation unique, ContentBuilder NG applique automatiquement l'enregistrement. L'action **Désactiver Édition** retire uniquement le bouton et le droit Edit ; l'accès New reste indépendant. La réinitialisation complète retire Edit et New.

La création et la régénération du template sont accessibles uniquement dans **Actions**. Le libellé devient **Créer le template** lorsque celui-ci est vide, sans confirmation supplémentaire, et **Régénérer le template** lorsqu'il existe, avec confirmation avant remplacement. Le cadenas reste un réglage permanent regroupé à gauche avec les options d'affichage.

Les explications des indicateurs et les rappels de permissions de Détail, Édition et États sont regroupés dans l'aide générale de la vue, accessible par le bouton **Aide** de la barre d'outils. Les onglets de travail affichent uniquement les réglages opérationnels. Les réinitialisations Introduction et Article sont également des actions contextuelles avec confirmation unique et enregistrement automatique.
