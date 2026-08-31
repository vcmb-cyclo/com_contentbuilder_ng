# ContentBuilder NG — publication et flux Joomla Update

## Périmètre

- Joomla 6 uniquement ;
- paquets RC locaux ou installés manuellement ;
- branches et pull requests fusionnées dans `main` ;
- releases GitHub et manifeste `com_contentbuilderng_update.xml`.

## Principe

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

Le fichier `com_contentbuilderng_update.xml` reste inchangé et continue de
référencer la dernière release publiée. Le ZIP local n'inclut pas ce manifeste
de mise à jour.

Cette règle s'applique aussi lorsqu'une RC est installée manuellement sur un site
de production : cette installation ne doit pas annoncer la RC aux autres sites.

## Publication

Le manifeste de mise à jour peut être modifié seulement après que la release
GitHub et son ZIP installable sont disponibles. Il doit alors contenir :

- la version publiée exacte ;
- l'URL du ZIP attaché à cette release ;
- le checksum SHA-256 produit et validé par le workflow de publication.

Un merge dans `main` ne constitue pas une publication. Aucun tag, aucune release
et aucune modification du flux Joomla Update ne sont créés pour une simple PR.

## Critères d'acceptation

1. Une URL de téléchargement du manifeste correspond toujours à un ZIP GitHub
   existant et installable.
2. Une RC non publiée ne déclenche aucune proposition Joomla Update.
3. Une installation manuelle en production n'altère pas le flux de mise à jour.
4. La publication officielle met à jour le manifeste uniquement après la
   création et la validation de la release.
