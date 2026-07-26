# CBStats plugin description — proposed wording

> Codex must locate the real plugin manifest and language description keys before applying these texts. Do not invent a second description system.

## Recommended short description — French

**ContentBuilder NG - CBStats - Content - Statistiques** permet d'afficher dans les contenus Joomla des statistiques dynamiques issues des vues ContentBuilder NG au moyen de balises `{CBStats ...}`, ou de publier les sorties de données prises en charge via URL/API. Le plugin gère les totaux, tableaux, agrégats, JSON, graphiques Pie et Bar, filtres, tris, deltas externes signés avec `add=`, titre du total avec `title=` et libellés d'affichage avec `titles=`. Exemple : `{CBStats id=25 field=Parcours output=pie title="👥 Total des inscrits :"}`. Les réglages API de la vue ainsi que les permissions STATS et de champ restent appliqués.

## Recommended long description — French

CBStats est le plugin de statistiques de ContentBuilder NG. Il interroge de façon générique une vue et ses champs depuis une balise `{CBStats ...}`, applique les filtres exacts, jokers et alternatives, puis produit les outputs `total`, `form_name`, `table`, `sum`, `min`, `max`, `json`, `pie` et `bar`. `field=` désigne le champ regroupé et affiché ; `filter[field]` et `filter[value]` permettent de filtrer sur un autre champ, par exemple `{CBStats id=15 field=Element-1 filter[field]=Element-2 filter[value]="Dét* | 3 | 4" output=bar}`. Lorsque le filtre porte sur le champ affiché, `value=` est le raccourci strictement équivalent ; il reste distinct de `values=`, réservé à `source=manual`. `add=` applique des deltas externes signés ; si le résultat calculé devient négatif, CBStats utilise temporairement `0` pour les tris, pourcentages et rendus, sans modifier les données sources. `titles=` renomme les libellés affichés avant le tri. Les outputs de données `json`, `total`, `sum`, `min`, `max` et `form_name` sont aussi accessibles par URL/API ; JSON accepte également `add` et `titles`. Les calculs de champs reposent sur une source normalisée commune et respectent les ACL, STATS et permissions de champ.

Exemples :

```text
{CBStats id=25 field=Route output=pie add='100 km=-3'}
{CBStats id=25 field=Route output=table titles='1=Group 1;2=Group 2'}
{CBStats id=25 field=Route output=bar add='1=-2;2=3' titles='1=Group 1;2=Group 2' sort=value dir=desc}
{CBStats id=15 field=Element-1 filter[field]=Element-2 filter[value]="Dét* | 3 | 4" output=bar}
{CBStats id=15 field=Element-2 value="Dét* | 3 | 4" output=bar}
```

### Fusion de vues

`idsum="25+27"` remplace `id=` pour additionner les statistiques de deux à
cinq vues. Chaque vue applique ses droits, filtres et regroupements avant la
fusion des libellés exactement identiques. `add=`, `titles=` et le tri
s’appliquent ensuite au résultat final. Exemples :

```text
{CBStats idsum="25+27" field="Parcours" output="table" title="Monticyclo / Montigravel"}
{CBStats idsum="31+32+33+34+35" field="Distance" output="bar" title="BRM"}
```

## Recommended short description — English

**ContentBuilder NG - CBStats - Content statistics** displays dynamic statistics from ContentBuilder NG views in Joomla content through `{CBStats ...}` tags, or publishes supported data outputs through URL/API requests. It supports totals, tables, aggregates, JSON, Pie and Bar charts, filters, sorting, signed external deltas with `add=`, total labels with `title=` and category display labels with `titles=`. Example: `{CBStats id=25 field=Route output=pie title="👥 Total registrations:"}`. View API settings and STATS and field permissions remain enforced.

## Recommended long description — English

CBStats is the statistics plugin for ContentBuilder NG. It generically queries a view and its fields from a `{CBStats ...}` tag, applies exact, wildcard and alternative filters, and produces `total`, `form_name`, `table`, `sum`, `min`, `max`, `json`, `pie` and `bar`. `field=` selects the grouped and displayed field; `filter[field]` and `filter[value]` can filter another field, for example `{CBStats id=15 field=Element-1 filter[field]=Element-2 filter[value]="Dét* | 3 | 4" output=bar}`. When the displayed field is filtered, `value=` is the strictly equivalent shorthand; it remains distinct from `values=`, which is reserved for `source=manual`. `add=` applies signed external deltas; when a calculated result becomes negative, CBStats temporarily uses `0` for sorting, percentages and rendering without changing source data. `titles=` renames display labels before sorting. The `json`, `total`, `sum`, `min`, `max` and `form_name` data outputs are also available through URL/API requests; JSON also accepts `add` and `titles`. Field statistics share one normalized source and enforce ACLs, STATS and field permissions.

Examples:

```text
{CBStats id=25 field=Route output=pie add='100 km=-3'}
{CBStats id=25 field=Route output=table titles='1=Group 1;2=Group 2'}
{CBStats id=25 field=Route output=bar add='1=-2;2=3' titles='1=Group 1;2=Group 2' sort=value dir=desc}
{CBStats id=15 field=Element-1 filter[field]=Element-2 filter[value]="Dét* | 3 | 4" output=bar}
{CBStats id=15 field=Element-2 value="Dét* | 3 | 4" output=bar}
```

### Merging views

`idsum="25+27"` replaces `id=` to add statistics from two to five views. Each
view applies permissions, filters and grouping before exactly identical labels
are merged. `add=`, `titles=` and sorting then apply to the final result.

```text
{CBStats idsum="25+27" field="Route" output="table" title="Monticyclo / Montigravel"}
{CBStats idsum="31+32+33+34+35" field="Distance" output="bar" title="BRM"}
```

## Recommended short description — German

**ContentBuilder NG - CBStats - Inhalt - Statistiken** zeigt dynamische Statistiken aus ContentBuilder-NG-Ansichten in Joomla-Inhalten über `{CBStats ...}`-Tags an oder veröffentlicht unterstützte Datenausgaben über URL/API-Anfragen. Das Plugin unterstützt Gesamtzahlen, Tabellen, Aggregate, JSON, Kreis- und Balkendiagramme, Filter, Sortierungen, vorzeichenbehaftete externe Deltas mit `add=`, Gesamtbezeichnungen mit `title=` und Kategoriebezeichnungen mit `titles=`. Beispiel: `{CBStats id=25 field=Strecke output=pie title="👥 Gesamtzahl der Anmeldungen:"}`. API-Einstellungen der Ansicht sowie STATS- und Feldberechtigungen bleiben wirksam.

## Recommended long description — German

CBStats ist das Statistik-Plugin für ContentBuilder NG. Es fragt eine Ansicht und ihre Felder generisch über ein `{CBStats ...}`-Tag ab, wendet exakte Filter, Platzhalter und Alternativen an und erzeugt `total`, `form_name`, `table`, `sum`, `min`, `max`, `json`, `pie` und `bar`. `field=` bestimmt das gruppierte und angezeigte Feld; mit `filter[field]` und `filter[value]` kann ein anderes Feld gefiltert werden, zum Beispiel `{CBStats id=15 field=Element-1 filter[field]=Element-2 filter[value]="Dét* | 3 | 4" output=bar}`. Wird das angezeigte Feld gefiltert, ist `value=` die exakt gleichwertige Kurzform; sie bleibt von `values=` getrennt, das ausschließlich für `source=manual` bestimmt ist. `add=` wendet vorzeichenbehaftete externe Deltas an. `titles=` benennt Anzeigebezeichnungen vor der Sortierung um. Feldstatistiken verwenden eine gemeinsame normalisierte Quelle und berücksichtigen ACLs sowie STATS- und Feldberechtigungen.

Beispiele:

```text
{CBStats id=25 field=Route output=pie add='100 km=-3'}
{CBStats id=25 field=Route output=table titles='1=Group 1;2=Group 2'}
{CBStats id=25 field=Route output=bar add='1=-2;2=3' titles='1=Group 1;2=Group 2' sort=value dir=desc}
{CBStats id=15 field=Element-1 filter[field]=Element-2 filter[value]="Dét* | 3 | 4" output=bar}
{CBStats id=15 field=Element-2 value="Dét* | 3 | 4" output=bar}
```

### Ansichten zusammenführen

`idsum="25+27"` ersetzt `id=`, um Statistiken aus zwei bis fünf Ansichten zu
addieren. Jede Ansicht wendet Berechtigungen, Filter und Gruppierung an, bevor
exakt gleiche Bezeichnungen zusammengeführt werden. `add=`, `titles=` und die
Sortierung gelten anschließend für das Endergebnis.

```text
{CBStats idsum="25+27" field="Strecke" output="table" title="Monticyclo / Montigravel"}
{CBStats idsum="31+32+33+34+35" field="Distanz" output="bar" title="BRM"}
```

## Application rules

When updating the real plugin:

1. The official element is `contentbuilderng_cbstats`; `contentbuilderng_stats` is retained only as an installer migration source.
2. Update manifest description keys and corresponding `fr-FR`, `en-GB` and `de-DE` language strings.
3. Do not claim `json`, `pie` or `bar` is available until the corresponding pass is actually implemented and validated.
4. Document the exact outputs implemented and validated in the current release.
5. Keep short descriptions short enough for Joomla extension listings and administrator views.
### Result limit and total display

`limit="10"` keeps the first ten values after sorting, then recalculates the
displayed total and chart percentages from those values. `total="hide"` hides
that total in Table, Pie and Bar without preventing its internal calculation.
Both options work with `id` and `idsum`. No Other category is added.

```text
{CBStats id="25" field="Town" output="table" sort="value" dir="desc" limit="10"}
{CBStats idsum="25+27" field="Club" output="bar" sort="value" dir="desc" limit="10" total="hide"}
```

### Limite du résultat et affichage du total

`limit="10"` conserve les dix premières valeurs après le tri.
Le total affiché et les pourcentages sont ensuite recalculés sur ces seules
valeurs. `total="hide"` masque ce total dans Table, Pie et Bar sans empêcher
son calcul interne. Les deux options fonctionnent avec `id` et `idsum`.
Aucune catégorie Autres n’est ajoutée.

```text
{CBStats id="25" field="Ville" output="table" sort="value" dir="desc" limit="10"}
{CBStats idsum="25+27" field="Club" output="bar" sort="value" dir="desc" limit="10" total="hide"}
```

### Ergebnisbegrenzung und Summenanzeige

`limit="10"` behält die ersten zehn Werte nach der Sortierung.
Die angezeigte Gesamtsumme und die Prozentsätze werden anschließend nur aus
diesen Werten neu berechnet. `total="hide"` blendet diese Summe in Tabelle,
Kreis- und Balkendiagramm aus, ohne ihre interne Berechnung zu verhindern.
Beide Optionen funktionieren mit `id` und `idsum`. Eine Kategorie Sonstige
wird nicht hinzugefügt.
