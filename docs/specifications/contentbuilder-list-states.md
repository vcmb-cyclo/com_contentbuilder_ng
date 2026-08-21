# ContentBuilder NG — spécification des états de liste

## 1. Périmètre

- Projet : ContentBuilder NG
- Version initiale : 6.1.10-RC11-B1
- Plateforme : Joomla 6 uniquement
- PHP : 8.3 ou version ultérieure
- Base de données : MySQL ou MariaDB uniquement

Les états sont propres à une vue ContentBuilder NG. Ils permettent d'associer
à un enregistrement un libellé et une couleur, ainsi qu'une éventuelle action
automatique.

## 2. Configuration d'un état

Chaque vue possède dix emplacements d'état, affichés dans l'ordre de leur
identifiant. Chaque état contient :

- **Publier** : rend l'état sélectionnable et assignable ;
- **Titre** : libellé affiché dans les listes, filtres et sélecteurs ;
- **Couleur** : couleur d'identification des enregistrements affectés ;
- **Action** : plugin facultatif exécuté lorsqu'un enregistrement reçoit cet
  état. Une action peut modifier des données ou l'article Joomla associé.

Un état dépublié n'est pas proposé pour une nouvelle affectation. Son titre,
sa couleur et son action restent enregistrés.

## 3. Palette de base

La palette de base publie les quatre premiers états :

1. vert `#60E309` ;
2. orange `#FF9800` ;
3. jaune `#FCFC00` ;
4. rouge `#FC0000`.

Les états 5 à 10 sont dépubliés, blancs (`#FFFFFF`), sans action, et portent
leur titre localisé par défaut « État 5 » à « État 10 ».

## 4. Indicateur de l'onglet États

Le symbole de l'onglet indique la cohérence globale de trois réglages : présence
d'un état publié, affichage des états sur le frontend et permission frontend
« État ».

- **aucun symbole** : aucun état publié, aucun affichage et aucune permission ;
- **triangle orange** : configuration existante mais incomplète ;
- **coche verte** : au moins un état publié, affichage activé et permission accordée ;
- **croix rouge** : affichage ou permission activé sans aucun état publié.

L'infobulle de l'onglet décrit toujours sa fonction. Lorsqu'un point est
présent, elle explique aussi son état actuel. Pour une croix rouge, elle
indique précisément le réglage absent ou contradictoire.

L'onglet contient une aide repliable donnant la légende complète.

## 5. Menu Actions et réinitialisation

Les commandes de réinitialisation apparaissent dans le bouton Joomla
**Actions** lorsque l'onglet **États** est actif. Elles ne dépendent pas de la
sélection d'un élément de la vue.

Le même bouton est contextuel :

- dans **Vue**, il contient les actions des éléments et n'est actif que si au
  moins une ligne est sélectionnée ;
- dans **États**, il contient les commandes de réinitialisation et reste actif
  sans sélection ;
- dans les autres onglets, il reste visible mais désactivé.

Le menu déroulant se ferme après la désélection du dernier élément et à chaque
changement d'onglet. Aucun menu de réinitialisation supplémentaire n'est rendu
dans le contenu de l'onglet États.

Chaque opération demande une confirmation unique, modifie le formulaire courant
puis l'enregistre automatiquement. Aucun second clic sur **Enregistrer** n'est
nécessaire. Une confirmation précise toujours les données conservées.

### 5.1 Nettoyer les états inactifs

Pour chaque état dépublié : titre par défaut, couleur blanche et aucune action.
Les états publiés, l'affichage, les permissions et les affectations existantes
restent inchangés.

### 5.2 Restaurer la palette de base

Restaure les dix titres, les quatre couleurs de base et publie les quatre
premiers états. Les six autres deviennent blancs et dépubliés. Toutes les
actions sont retirées. L'affichage, les permissions et les affectations
existantes restent inchangés.

### 5.3 Désactiver les états de la vue

Dépublie les dix états et désactive leur affichage frontend. Les titres,
couleurs, actions, permissions et affectations existantes sont conservés.

### 5.4 Réinitialisation complète

Cette entrée est visible uniquement si le mode Debug de la vue est actif et si
l'utilisateur possède l'autorisation Joomla `core.admin`.

Elle restaure la palette de base, désactive l'affichage frontend et retire la
permission frontend « État » des groupes et du propriétaire. Elle ne supprime
jamais les états déjà affectés aux enregistrements.

## 6. Non-objectifs RC11

- aucun tri permanent ou glisser-déposer des états ;
- aucune modification de l'identifiant d'un état ;
- aucune suppression des affectations existantes ;
- aucune suppression automatique des affectations d'état existantes.

## 7. Critères d'acceptation

1. Les quatre opérations produisent exactement les effets documentés.
2. Annuler une confirmation ne modifie aucun champ.
3. Les Reset enregistrent automatiquement la vue après leur confirmation unique.
4. Le Reset complet est absent hors Debug ou sans `core.admin`.
5. Les affectations d'état des enregistrements ne sont jamais supprimées.
6. Les infobulles des colonnes décrivent Publier, Titre, Couleur et Action.
7. L'infobulle de l'onglet distingue absence de symbole, triangle orange,
   coche verte et chaque incohérence signalée par une croix rouge.
8. Les textes anglais, français et allemands restent alignés.
9. Le bouton Actions reste visible, affiche uniquement les commandes du
   contexte actif et ne reste jamais ouvert après une désélection ou un
   changement d'onglet.
