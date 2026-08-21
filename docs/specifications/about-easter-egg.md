# ContentBuilder NG — Easter egg About

## Version

- Version intégrée : 6.1.10-RC11
- Média : `media/com_contentbuilderng/images/cbng-easter-egg-2026.webp`

## Déclenchement

Dans l'écran About, cinq clics sur la zone correspondant à la tête du piranha,
effectués dans une fenêtre maximale de trois secondes, ouvrent l'animation.
Le compteur est ensuite remis à zéro.

## Affichage

L'animation est affichée dans une superposition responsive au-dessus de
l'administration. Elle peut être fermée par le bouton de fermeture, par un clic
sur l'arrière-plan ou avec la touche Échap. Fermer retire la source de l'image ;
chaque nouvelle ouverture relance donc le WebP depuis son début.

Le déclencheur ne présente aucun texte, compteur ou indice visuel dans About.
Le média partagé peut être réutilisé au frontend par son URL publique sans
ajouter d'option CBList ou CBStats.
