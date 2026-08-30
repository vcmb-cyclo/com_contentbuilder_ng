# Audit et workflow de réparation

## Portée

Cette spécification définit la présentation et le comportement de
**ContentBuilder NG → À propos → Audit** et du workflow **Démarrer les
réparations**. Elle couvre les contrôles de base, les incohérences de vues et de
formulaires, les enregistrements orphelins ainsi que les réparations proposées.

## Séparation entre audit et réparation

- L’audit est en lecture seule. Il détecte et classe les anomalies sans modifier
  la base.
- Le workflow de réparation reprend les résultats disponibles et demande une
  décision explicite pour chaque étape applicable.
- Une étape peut être réparée ou ignorée. Une réparation n’est jamais appliquée
  du seul fait d’avoir lancé l’audit ou ouvert le workflow.
- Les étapes sans anomalie sont marquées comme non nécessaires et ne demandent
  aucune action.

## Sémantique des états et couleurs

Les couleurs Joomla ont un sens fonctionnel stable :

- **vert / succès** : aucune action n’est nécessaire, ou une réparation demandée
  a été appliquée avec succès ;
- **jaune-orange / avertissement** : une anomalie réparable ou une décision en
  attente nécessite l’attention de l’administrateur ;
- **rouge / erreur** : le contrôle ou la réparation a échoué, ou une incohérence
  bloquante empêche de poursuivre normalement ;
- **neutre** : étape diagnostique, ignorée ou non applicable, sans succès de
  réparation à revendiquer.

Le vert ne doit donc jamais accompagner un texte demandant de choisir ou
d’exécuter une action.

## Démarrage du workflow

Après précontrôle :

- si toutes les étapes sont `not_required` ou diagnostiques, le workflow est
  terminé et Joomla affiche un message de succès indiquant qu’aucune réparation
  n’est nécessaire ;
- si au moins une étape est `pending`, Joomla affiche un avertissement indiquant
  que des anomalies réparables ont été détectées et invite à choisir
  **Réparer** ou **Ignorer** ;
- le panneau de l’étape courante utilise également le niveau avertissement tant
  qu’une décision est attendue.

## Résultats d’étape

- Une étape réellement appliquée et réussie peut être affichée en vert et porter
  une coche de succès.
- Une étape ignorée, diagnostique ou non nécessaire reste neutre.
- Un résultat `warning` reste jaune-orange même si l’étape technique est
  terminée.
- Un résultat `error` ou `danger` reste rouge et ne porte aucune coche de succès.
- Le résumé final distingue les réparations appliquées des étapes simplement
  examinées ou ignorées.

## Traductions et accessibilité

Tous les messages, boutons, statuts et aides utilisent les clés Joomla alignées
en anglais, français et allemand. La compréhension d’un état ne doit pas reposer
uniquement sur la couleur : le texte et, le cas échéant, l’icône expriment aussi
le résultat ou l’action attendue.

## Critères d’acceptation

1. Des enregistrements orphelins détectés produisent un avertissement au
   démarrage du workflow et non un message vert.
2. Le message nomme les choix disponibles : Réparer ou Ignorer.
3. Un audit sans réparation requise produit un message vert explicite.
4. Seules les réparations appliquées avec succès sont présentées en vert.
5. Les échecs sont rouges et les décisions encore attendues sont jaune-orange.
6. Les textes EN, FR et DE restent sémantiquement alignés.
