# ContentBuilder NG — publication et flux Joomla Update

## Périmètre

- Joomla 6 uniquement ;
- paquets RC locaux ou installés manuellement ;
- branches et pull requests fusionnées dans `main` ;
- releases GitHub et manifeste `com_contentbuilderng_update.xml`.

## Principe

### État validé au 4 septembre 2026

- La version stable `6.1.15` est publiée sous le tag `v6.1.15`, avec son ZIP
  officiel et le manifeste Joomla Update correspondant. La PR #132 est fusionnée.
- La branche de développement suivante est `gil_6.2.0`, créée depuis `main`
  après publication du manifeste. Les corrections et spécifications de 6.2.0
  sont poussées sur cette même branche, sans modifier la release 6.1.15.
- Les trois appels inutiles à `ReflectionMethod::setAccessible()` ont été
  supprimés des tests pour PHP 8.5 : 988 tests et 5 445 assertions passent sans
  dépréciation.
- La sortie des fichiers de langue FR et DE du paquet principal est prévue
  pour 6.2.0. Le conditionnement, l’installation et les mises à jour des langues
  restent à spécifier avant toute modification des paquets.

Après publication réussie, une branche de release peut être supprimée localement
et sur GitHub uniquement après vérification de son intégration complète dans
`origin/main`. Le tag et la release sont conservés. Les développements suivants
restent sur leur branche `gil_<version>` ; la branche locale `main` peut être
synchronisée sans y effectuer de développement.

La version du code et la version proposée par Joomla Update sont deux états
distincts. Une RC peut être préparée, testée, installée manuellement et fusionnée
dans `main` sans être publiée comme release GitHub.

`com_contentbuilderng_update.xml` doit toujours annoncer la dernière version
réellement publiée, dont le tag, la release GitHub et le ZIP installable existent.
Il ne doit jamais pointer vers une RC locale, une branche, une pull request ou un
artefact qui n'est pas disponible dans les releases GitHub.

## Préparation d'une RC non publiée

Les fichiers suivants portent la version RC :

- `com_contentbuilderng.xml` ;
- `media/joomla.asset.json` ;
- `CHANGELOG.md` ;
- `com_contentbuilderng_changelog.xml`.

La version de `media/joomla.asset.json` doit correspondre à celle du manifeste
du composant, notamment lorsque le CSS change. Elle permet aux URL des
ressources de changer de version pour éviter la réutilisation du CSS précédent
depuis le cache du navigateur. Pour la version finale validée : `6.1.15`.

Le fichier `com_contentbuilderng_update.xml` reste inchangé et continue de
référencer la dernière release publiée. Le ZIP local n'inclut pas ce manifeste
de mise à jour.

Cette règle s'applique aussi lorsqu'une RC est installée manuellement sur un site
de production : cette installation ne doit pas annoncer la RC aux autres sites.

## Publication

Le manifeste de mise à jour peut être modifié seulement après que la release
GitHub et son ZIP installable sont disponibles. Le workflow publie d'abord la
release, vérifie son succès, puis met automatiquement à jour et commit le
manifeste. Celui-ci doit alors contenir :

- la version publiée exacte ;
- l'URL du ZIP attaché à cette release ;
- le checksum SHA-256 produit et validé par le workflow de publication.

La préparation de la release ne modifie donc pas manuellement le manifeste en
avance.

Un merge dans `main` ne constitue pas une publication. Aucun tag, aucune release
et aucune modification du flux Joomla Update ne sont créés pour une simple PR.

## Critères d'acceptation

1. Une URL de téléchargement du manifeste correspond toujours à un ZIP GitHub
   existant et installable.
2. Une RC non publiée ne déclenche aucune proposition Joomla Update.
3. Une installation manuelle en production n'altère pas le flux de mise à jour.
4. La publication officielle met à jour le manifeste uniquement après la
   création et la validation de la release.
