# CBStats plugin description — proposed wording

> Codex must locate the real plugin manifest and language description keys before applying these texts. Do not invent a second description system.

## Current concise description and validation help

The manifest description now explains that CBStats displays a ContentBuilder NG
statistics view directly in Joomla content, preserves the view's access rights and
configuration, and starts with a simple `{CBStats id=ViewID output=total}` example.
The localized plugin description below it documents the main outputs and options.

An invalid tag displays all independent syntax errors together and no statistics
result. Each message identifies the affected parameter and invalid value whenever
applicable. The error block links to a localized public syntax page in a new browser
tab; it does not require administrator access or expose view data. Permission and
unexpected internal errors remain generic.

## Recommended short description — French

**ContentBuilder NG - CBStats - Content - Statistiques** permet d'afficher dans les contenus Joomla des statistiques dynamiques issues des vues ContentBuilder NG au moyen de balises `{CBStats ...}`, ou de publier les sorties de données prises en charge via URL/API. Le plugin gère les totaux, tableaux, agrégats `sum`, `min`, `max` et `avg`, JSON, graphiques Pie, Bar, Histogram, Line et Radar, filtres, tris, groupes de valeurs `groups=`, limites `limit=`, masquage `hide=`, deltas externes signés avec `add=`, libellés de présentation avec `labels=` et libellés de catégories avec `titles=`. Exemples : `{CBStats id=15 field=Age output=avg}` et `{CBStats id=15 field=Age output=radar groups="18-29;30-39;40-49;50+"}`. Les réglages API de la vue ainsi que les permissions STATS et de champ restent appliqués.

## Recommended long description — French

CBStats est le plugin de statistiques de ContentBuilder NG. Il interroge de façon générique une vue et ses champs depuis une balise `{CBStats ...}`, applique les filtres exacts, jokers et alternatives, puis produit les outputs `total`, `view_name`, `table`, `json`, `pie`, `bar`, `histogram`, `line`, `radar`, `sum`, `min`, `max` et `avg`. `field=` désigne le champ regroupé et affiché ; `filter[field]` et `filter[value]` permettent de filtrer sur un autre champ, par exemple `{CBStats id=15 field=Element-1 filter[field]=Element-2 filter[value]="Dét* | 3 | 4" output=bar}`. Lorsque le filtre porte sur le champ affiché, `value=` est le raccourci strictement équivalent ; il reste distinct de `values=`, réservé à `source=manual`. `groups=` crée des groupes de valeurs à partir d'intervalles inclusifs ou de listes explicites non contiguës, `limit=` limite le résultat après tri et `hide=` contrôle séparément le total, la liste textuelle et le graphique. `avg` ignore les valeurs vides ou non numériques, tandis que Histogram, Line et Radar réutilisent le même moteur normalisé que Table, JSON, Pie et Bar. Les outputs de données sont aussi accessibles par URL/API et respectent les ACL, STATS et permissions de champ.

Exemples :

```text
{CBStats id=15 field=Route output=pie add='100 km=-3'}
{CBStats id=15 field=Route output=table titles='1=Group 1;2=Group 2'}
{CBStats id=15 field=Route output=bar add='1=-2;2=3' titles='1=Group 1;2=Group 2' sort=value dir=desc}
{CBStats id=15 field=Element-1 filter[field]=Element-2 filter[value]="Dét* | 3 | 4" output=bar}
{CBStats id=15 field=Element-2 value="Dét* | 3 | 4" output=bar}
```

### Fusion de vues

`idsum=15+27` remplace `id=` pour additionner les statistiques de deux à
cinq vues. Chaque vue applique ses droits, filtres et regroupements avant la
fusion des libellés exactement identiques. `add=`, `titles=` et le tri
s’appliquent ensuite au résultat final. Exemples :

```text
{CBStats idsum=15+27 field="Parcours" output="table" labels="title=Monticyclo / Montigravel"}
{CBStats idsum=31+32+33+34+35 field="Distance" output="bar" labels="title=BRM"}
```

## Recommended short description — English

**ContentBuilder NG - CBStats - Content statistics** displays dynamic statistics from ContentBuilder NG views in Joomla content through `{CBStats ...}` tags, or publishes supported data outputs through URL/API requests. It supports totals, tables, `sum`/`min`/`max`/`avg`, JSON, Pie, Bar, Histogram, Line and Radar charts, numeric `groups=`, `limit=`, `hide=`, filtering, sorting, signed external deltas with `add=`, presentation labels with `labels=` and data-category labels with `titles=`. Examples: `{CBStats id=15 field=Age output=avg}` and `{CBStats id=15 field=Age output=radar groups="18-29;30-39;40-49;50+"}`. View API settings and STATS and field permissions remain enforced.

## Recommended long description — English

CBStats is the statistics plugin for ContentBuilder NG. It generically queries a view and its fields from a `{CBStats ...}` tag, applies exact, wildcard and alternative filters, and produces `total`, `view_name`, `table`, `json`, `pie`, `bar`, `histogram`, `line`, `radar`, `sum`, `min`, `max` and `avg`. `field=` selects the grouped and displayed field; `filter[field]` and `filter[value]` can filter another field, for example `{CBStats id=15 field=Element-1 filter[field]=Element-2 filter[value]="Dét* | 3 | 4" output=bar}`. When the displayed field is filtered, `value=` is the strictly equivalent shorthand; it remains distinct from `values=`, which is reserved for `source=manual`. `groups=` creates value groups from inclusive intervals or explicit non-contiguous value lists, `limit=` is applied after sorting, and `hide=` controls chart, text list and total independently. `avg` ignores empty/non-numeric values; Histogram, Line and Radar share the normalized engine with the other list outputs. All supported data outputs are also available through URL/API requests and enforce ACLs, STATS and field permissions.

Examples:

```text
{CBStats id=15 field=Route output=pie add='100 km=-3'}
{CBStats id=15 field=Route output=table titles='1=Group 1;2=Group 2'}
{CBStats id=15 field=Route output=bar add='1=-2;2=3' titles='1=Group 1;2=Group 2' sort=value dir=desc}
{CBStats id=15 field=Element-1 filter[field]=Element-2 filter[value]="Dét* | 3 | 4" output=bar}
{CBStats id=15 field=Element-2 value="Dét* | 3 | 4" output=bar}
```

### Merging views

`idsum=15+27` replaces `id=` to add statistics from two to five views. Each
view applies permissions, filters and grouping before exactly identical labels
are merged. `add=`, `titles=` and sorting then apply to the final result.

```text
{CBStats idsum=15+27 field="Route" output="table" labels="title=Monticyclo / Montigravel"}
{CBStats idsum=31+32+33+34+35 field="Distance" output="bar" labels="title=BRM"}
```

## Recommended short description — German

**ContentBuilder NG - CBStats - Inhalt - Statistiken** zeigt dynamische Statistiken aus ContentBuilder-NG-Ansichten in Joomla-Inhalten über `{CBStats ...}`-Tags an oder veröffentlicht unterstützte Datenausgaben über URL/API-Anfragen. Das Plugin unterstützt Gesamtzahlen, Tabellen, `sum`/`min`/`max`/`avg`, JSON, Kreis-, Balken-, Histogramm-, Linien- und Radardiagramme, numerische `groups=`, `limit=`, `hide=`, Filter, Sortierungen, vorzeichenbehaftete externe Deltas mit `add=`, Darstellungsbezeichnungen mit `labels=` und Datenkategoriebezeichnungen mit `titles=`. Beispiele: `{CBStats id=15 field=Age output=avg}` und `{CBStats id=15 field=Age output=radar groups="18-29;30-39;40-49;50+"}`. API-Einstellungen der Ansicht sowie STATS- und Feldberechtigungen bleiben wirksam.

## Recommended long description — German

CBStats ist das Statistik-Plugin für ContentBuilder NG. Es fragt eine Ansicht und ihre Felder generisch über ein `{CBStats ...}`-Tag ab, wendet exakte Filter, Platzhalter und Alternativen an und erzeugt `total`, `view_name`, `table`, `json`, `pie`, `bar`, `histogram`, `line`, `radar`, `sum`, `min`, `max` und `avg`. `field=` bestimmt das gruppierte und angezeigte Feld; mit `filter[field]` und `filter[value]` kann ein anderes Feld gefiltert werden, zum Beispiel `{CBStats id=15 field=Element-1 filter[field]=Element-2 filter[value]="Dét* | 3 | 4" output=bar}`. Wird das angezeigte Feld gefiltert, ist `value=` die exakt gleichwertige Kurzform; sie bleibt von `values=` getrennt, das ausschließlich für `source=manual` bestimmt ist. `groups=` erzeugt Wertgruppen aus inklusiven Intervallen oder expliziten, nicht zusammenhängenden Wertelisten, `limit=` wird nach der Sortierung angewendet und `hide=` steuert Grafik, Textliste und Summe unabhängig. `avg` ignoriert leere/nicht numerische Werte; Histogramm, Linie und Radar verwenden dieselbe normalisierte Quelle wie die übrigen Listenausgaben. Feldstatistiken berücksichtigen ACLs sowie STATS- und Feldberechtigungen.

Beispiele:

```text
{CBStats id=15 field=Route output=pie add='100 km=-3'}
{CBStats id=15 field=Route output=table titles='1=Group 1;2=Group 2'}
{CBStats id=15 field=Route output=bar add='1=-2;2=3' titles='1=Group 1;2=Group 2' sort=value dir=desc}
{CBStats id=15 field=Element-1 filter[field]=Element-2 filter[value]="Dét* | 3 | 4" output=bar}
{CBStats id=15 field=Element-2 value="Dét* | 3 | 4" output=bar}
```

### Ansichten zusammenführen

`idsum=15+27` ersetzt `id=`, um Statistiken aus zwei bis fünf Ansichten zu
addieren. Jede Ansicht wendet Berechtigungen, Filter und Gruppierung an, bevor
exakt gleiche Bezeichnungen zusammengeführt werden. `add=`, `titles=` und die
Sortierung gelten anschließend für das Endergebnis.

```text
{CBStats idsum=15+27 field="Strecke" output="table" labels="title=Monticyclo / Montigravel"}
{CBStats idsum=31+32+33+34+35 field="Distanz" output="bar" labels="title=BRM"}
```

## Application rules

When updating the real plugin:

1. The official element is `contentbuilderng_cbstats`; `contentbuilderng_stats` is retained only as an installer migration source.
2. Update manifest description keys and corresponding `fr-FR`, `en-GB` and `de-DE` language strings.
3. Do not claim `json`, `pie` or `bar` is available until the corresponding pass is actually implemented and validated.
4. Document the exact outputs implemented and validated in the current release.
5. Keep short descriptions short enough for Joomla extension listings and administrator views.
### Result limit and display options

`limit=10` keeps the first ten values after sorting, then recalculates the
displayed total and chart percentages from those values. `hide=` accepts
`title`, `total`, `values` and `graph`, combined with `|`: `title` hides the
block or Card heading defined by `labels="title=..."`, `total` hides the displayed
total, `values` hides the textual labels-and-values list below the graph without
changing the graph itself, and `graph` hides the drawing but keeps that
lightweight textual list.
Without `hide=`, everything is
shown. Hiding all three elements produces a message. The former `total=hide`
syntax is rejected; use `hide="total"`.

```text
{CBStats id=15 field="Town" output="table" sort="value" dir="desc" limit=10}
{CBStats idsum=15+27 field="Club" output="bar" sort="value" dir="desc" limit=10 hide="total"}
{CBStats id=15 field="Age" output="histogram" groups="18-29;30-39;40-49;50-59;60+" hide="total|values"}
{CBStats id=15 field="Age" output="radar" groups="18-29;30-39;40-49;50-59;60+" hide="graph|total"}
{CBStats id=15 field="RegistrationDate" output="line" sort="value" dir="asc" hide="values"}
```

### Limite du résultat et affichage du total

`limit=10` conserve les dix premières valeurs après le tri. `hide=` accepte
`title`, `total`, `values` et `graph`, combinés avec `|`. `title` masque le titre
du bloc ou de la Card défini par `labels="title=..."`, `total` masque le Total,
`values` masque la liste textuelle des libellés et valeurs sous le graphique
sans modifier le graphique lui-même, et `graph` masque le dessin tout en
conservant cette liste textuelle légère. Sans `hide=`, tout
est affiché. Masquer les trois éléments produit un message. L’ancienne syntaxe
`total=hide` est refusée ; utilisez `hide="total"`.

```text
{CBStats id=15 field="Ville" output="table" sort="value" dir="desc" limit=10}
{CBStats idsum=15+27 field="Club" output="bar" sort="value" dir="desc" limit=10 hide="total"}
{CBStats id=15 field="Age" output="radar" hide="graph|total"}
{CBStats id=15 field="DateInscription" output="line" hide="values"}
```

### Ergebnisbegrenzung und Summenanzeige

`limit=10` behält die ersten zehn Werte nach der Sortierung. `hide=` akzeptiert
`title`, `total`, `values` und `graph`, mit `|` kombiniert. `title` blendet den
mit `labels="title=..."` definierten Block- oder Card-Titel aus, `total` die Summe
aus, `values` blendet nur die textuelle Beschriftungs- und Werteliste unter der
Grafik aus, ohne die Grafik zu ändern, und `graph` blendet die Zeichnung aus und
behält diese leichte Textliste bei. Ohne
`hide=` wird alles angezeigt. Alle drei Werte zusammen erzeugen eine Meldung.
Die frühere Syntax `total=hide` wird abgelehnt; verwenden Sie `hide="total"`.
