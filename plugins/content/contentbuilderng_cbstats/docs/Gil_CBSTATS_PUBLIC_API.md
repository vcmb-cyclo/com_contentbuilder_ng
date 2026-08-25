# CBStats public syntax / API reference

Stable baseline: ContentBuilder NG 6.1.11. This reference includes the
validated `output=distinct`, `titleset` and
`output=remaining target=Number` contracts.

## Syntax validation and public help

CBStats validates the complete tag before querying a view or rendering statistics.
Independent syntax errors are reported together, with the parameter and invalid
value identified whenever applicable. If the tag is invalid, no statistics view
is displayed below the messages.

Each validation block includes a **Help with CBStats syntax** link. It opens the
localized public CBStats help page in a new browser tab, without requiring access
to the Joomla administration or exposing view data. Access-control failures and
unexpected internal errors deliberately remain generic.

## Table column headers

`headers=` customizes only the two column headers of `output=table`:

```text
{CBStats id=15 field=Dpt output=table headers="Dpt=Département;Total=Qté"}
```

The syntax is `key=label;key=label`, with surrounding spaces ignored. Unmapped headers keep their default label; unknown keys and empty labels are ignored. The displayed field can be addressed by its field name or current label, and the numeric column by the stable key `Total`. UTF-8 labels are supported.

`title=` changes the global total label, `titles=` changes category labels and `headers=` changes Table column headers; these options are independent. Pie and Bar have legends rather than associated tables, so `headers=` does not alter them.

## Manual export

Only `export=manual` enables export for `output=pie`, `output=bar` and `output=table` in Joomla content. Other `export=` values are rejected with a validation message:

```text
{CBStats id=25 field=Parcours output=pie title="👥 Total des inscrits" export=manual}
```

The normal output is followed by the final title, labels, values and total, then by a visible frozen `{CBStats source=manual ...}` tag and a centered copy button. The exported `values=` uses the final displayed labels and values after filters, grouping, `add=`, negative-to-zero normalization, `titles=` and sorting. The final labels are embedded directly in `values=` and `titles=` is not copied. Dynamic parameters (`id`, `field`, filters, `add`, `titles`, `sort`, `dir`, `limit` and `export`) are not copied. `title`, `headers=` and the validated visual `background` option are retained when explicitly supplied, so a frozen manual Table keeps its customized column headers.

Labels use the RC83 manual escaping rules: `\;`, `\=` and `\\`. Pie remains Pie, Bar remains Bar and Table remains Table. `output=total` is not exported because it has no category presentation to freeze. With `source=manual export=manual`, CBStats displays one normalized export block without recursion.


> Platform scope is inherited from the repository root `AGENTS.md`: Joomla 6 only, PHP 8.3+, MySQL/MariaDB only. No legacy compatibility layer is required.
> This document is the target public reference for CBStats. In the real repository, Codex must first locate the existing canonical ContentBuilder NG documentation/API reference and merge this information there rather than creating a duplicate documentation island.

## 1. General syntax

```text
{CBStats id=IdVue ...}
```

Common field-based form:

```text
{CBStats id=IdVue field=NomDuChamp output=TYPE}
```

Numeric options `id` and `limit`, and the numeric identifier list `idsum`, are
written without quotation marks: `id=15`, `limit=10`, `idsum=15+16`. Quoted
forms are invalid. Numbers contained inside textual options such as `values`,
`add`, `ranges` or `filter[value]` remain textual data and are not affected by
this lexical rule.

### Merge two to five views with `idsum`

`idsum` is an alternative to `id`; the two parameters cannot be supplied
together. It accepts two to five unique positive view identifiers separated by
`+`. Because the merge groups values, `field=` is required for every `idsum`
output, including `output=total`:

```text
{CBStats idsum=25+27 field="Parcours" output="table" title="Monticyclo / Montigravel"}
{CBStats idsum=25+27 field="Fédération" output="pie"}
{CBStats idsum=31+32+33+34+35 field="Distance" output="bar" title="BRM"}
```

Each view independently enforces STATS and field permissions and applies the
same filter. Its existing engine then groups values. CBStats adds counts whose
labels are exactly identical according to the existing grouping semantics;
labels found in only one view are retained.

The processing order is:

```text
permissions → filter and grouping in each view → merge identical labels
→ add → negative-to-zero normalization → titles → final sorting → output
```

Consequently, `titles=` cannot create artificial merge duplicates, and
`sort=` applies to the final merged result. The global total is the sum of the
real retained record totals from every authorized view; it is not derived from
grouped field values. `output=form_name` is unavailable because an `idsum`
source has no single view name.

CBStats rejects fewer than two or more than five identifiers, invalid or
duplicate identifiers, a missing or inaccessible view, a missing or
unauthorized field, and simultaneous `id`/`idsum` use. Duplicate identifiers
are refused rather than deduplicated to prevent accidental double counting.

## 2. Supported outputs

The following outputs are implemented in RC97 and must remain compatible:

```text
output=total
output=remaining target=200
output=form_name
output=table
output=json
output=pie
output=bar
output=histogram
output=line
output=radar
output=distinct
output=sum
output=min
output=max
output=avg
```

## 3. JSON output

Target syntax after Pass 1:

```text
{CBStats id=IdVue field=NomDuChamp output=json}
```

Target response shape:

```json
[
  {"label":"Valeur A","value":12},
  {"label":"Valeur B","value":7}
]
```

The labels come from actual field data. The plugin must not embed business values in code.

## 4. Pie output

Target syntax after Pass 2:

```text
{CBStats id=IdVue field=NomDuChamp output=pie}
```

Behavior:

- uses the same normalized statistics engine as `table` and `json`;
- responsive;
- shows percentages in sectors when readable;
- details are available in legend and tooltip;
- supports multiple charts on one page.

## 5. Bar output

Target syntax after Pass 3:

```text
{CBStats id=IdVue field=NomDuChamp output=bar}
```

Behavior:

- horizontal bar chart;
- uses the same normalized statistics engine;
- uses the active Joomla locale and exactly one decimal for percentages;
- values are displayed inside bars only when readable;
- uses the same compact detail legend, tooltip semantics, colors and total style as Pie;
- supports multiple charts on one page.

## 6. Histogram output

```text
{CBStats id=25 field=Age output=histogram ranges="18-29;30-39;40-49;50-59;60+"}
```

Histogram is a responsive vertical chart using the same normalized labels and
counts as Table, JSON, Pie and Bar. Use `ranges=` for numeric buckets; bounds
are inclusive and declaration order is preserved. For example,
`ranges="18-29;30-39;40-49;50+"` counts each age range independently and keeps
the real filtered record total separate from the sum of buckets. It is useful
for distributions such as age, distance or price.

## 7. Line output

```text
{CBStats id=25 field=RegistrationDate output=line sort=title dir=asc limit=30}
```

Line charts plot the normalized count for each actual field value in the final
sort order. They do not invent missing dates or categories and apply the same
filters, permissions, `add=`, `titles=`, `limit=` and `hide=` rules as the other
list outputs. For example, a date field can use
`output=line sort=title dir=asc`, while a categorical trend can use
`output=line sort=value dir=desc`.

## 8. Radar output

```text
{CBStats id=25 field=Age output=radar ranges="18-29;30-39;40-49;50-59;60+" hide="graph|total"}
```

Radar displays normalized values as axes in a responsive chart. It requires at
least 3 axes and accepts at most 8; 4 to 6 axes are recommended for readable
labels. Use `ranges=` for numeric dimensions or ordinary grouped values for
categories, for example `{CBStats id=25 field=Skill output=radar}`. Radar uses
the same tooltip, textual values list, permissions and no-data behavior as the
other graphical outputs.

## 9. Average output

```text
{CBStats id=25 field=Age output=avg}
```

`output=avg` returns the arithmetic mean of original individual numeric values
after ACLs, filters and an optional `idsum` merge. Empty and non-numeric values
are ignored; the average is independent from `ranges=` and is not a count of
distinct labels. For example, values `20`, `30`, `40` return `30`, while an
empty value and the text `unknown` do not affect the calculation.

## 10. Filters

Existing generic filter syntax includes:

```text
filter[field]=NomDuChamp
filter[value]=Valeur
```

Existing matching semantics to preserve include:

- trim behavior;
- wildcard `*`;
- alternatives separated by `|`.

Examples:

```text
{CBStats id=15 field=Element-1 filter[field]=Element-2 filter[value]="Dét* | 3 | 4" output=bar}
```

`field=Element-1` is grouped and displayed. `filter[field]=Element-2` is used
only to select records. `*` is a wildcard, `|` separates alternatives and
surrounding spaces are trimmed. Without a wildcard, matching is exact.

When the filter applies to the displayed field, `value=` is a shorthand:

```text
{CBStats id=15 field=Element-2 value="Dét* | 3 | 4" output=bar}
```

This is strictly equivalent to the complete filter where both `field` and
`filter[field]` are `Element-2`. `value=` is independent from `values=`, which
is reserved exclusively for `source=manual`. These examples illustrate syntax
only. No example value may be hardcoded into plugin logic.

## 11. External additions

External counts can be merged into field-statistics outputs with:

```text
add="Label=Number;Other label=Number"
```

Example:

```text
{CBStats id=25 field=Parcours output=pie add="100 km=5;150 km=3;200 km=2"}
```

If a label already exists, its signed delta is added to its calculated count.
Accepted values are strict integers such as `5`, `+5`, `0` and `-5`. Repeated
labels are cumulative. If the final calculated `add` result is negative, CBStats
temporarily uses `0` for that label before title mappings, sorting, percentages
and rendering. This also applies to a missing label receiving a negative delta.
The source data and `add` configuration remain unchanged, and a later zero or
positive result is used normally. Invalid syntax rejects the complete parameter.

`add` applies to `table`, `json`, `pie`, `bar`, `histogram`, `line` and `radar`.
It does not alter scalar outputs and is accepted by the URL/API endpoint for
the corresponding list outputs.

## 12. Display titles

Labels can be renamed for display with:

```text
titles="Original=Display title;Other original=Other display title"
```

The mapping is applied after `add` and before sorting. It does not change source
data, filtering or grouping. Unmapped labels remain unchanged. Two categories
renamed to the same display title are not merged. Semicolons delimit mappings and
the first equals sign separates each original label from its non-empty display
title. `sort=title` uses the final display titles.

## 13. Sorting

### Total label and background

`title=` customizes the total label for `table`, `pie` and `bar`, while
`titles=` continues to map category labels. An empty `title` uses the translated
default. When the custom title has no final colon, CBStats appends the localized
separator; an existing colon is never duplicated. Unicode and emojis are
preserved and all label text is HTML-escaped.

```text
{CBStats id=25 field=Parcours output=pie title="👥 Total des inscrits :"}
{CBStats id=25 field=Parcours output=table title="👥 Total des inscrits :"}
{CBStats id=25 field=Parcours output=pie title="👥 Total des inscrits :" background="transparent"}
```

Without `background=`, the container uses Bootstrap/Joomla body and border
variables with transparent/inherited fallbacks. Accepted custom backgrounds are
`transparent`, 3/6/8-digit hexadecimal colours, `rgb()`, `rgba()`, and these safe
named colours: `aliceblue`, `black`, `blue`, `currentcolor`, `gray`, `green`,
`grey`, `red`, `white`, `yellow`. Invalid values, CSS variables and additional
CSS declarations are ignored.

Target generic syntax:

```text
sort=none|title|value
dir=asc|desc
```

Defaults:

```text
sort=none
dir=asc
```

`sort=title` uses the active Joomla language locale and natural numeric-label
ordering. `sort=value` compares counts numerically. Sorting is performed by the
common normalized engine, so Table, JSON, Pie, Bar, Histogram, Line and Radar
share the same order.

Examples:

```text
{CBStats id=25 field=Parcours output=json sort=title dir=asc}
```

```text
{CBStats id=25 field=Parcours output=bar sort=value dir=desc}
```

The numeric IDs and field names above are documentation examples only.

## 14. JSON contract

Normalized records use:

```json
{
  "label": "<actual field value>",
  "value": 42
}
```

Rules:

- `label` is a string from actual data after existing normalization/grouping semantics;
- `value` is numeric;
- no HTML wrapper in `output=json`;
- valid UTF-8;
- valid JSON.

## 15. Generic chart text

Default generic format:

```text
<label> — <value> (<percentage> %)
```

Total:

```text
Total : <sum>
```

The plugin core must not hardcode domain-specific nouns such as `inscrits`.

## 16. Compatibility and permissions

All outputs must preserve the plugin's existing:

- STATS permissions;
- debug behavior;
- filter semantics;
- security/escaping requirements.

## 17. URL/API data outputs

The existing `action=cbstats` endpoint supports:

```text
output=json|table|pie|bar|histogram|line|radar|total|distinct|sum|min|max|avg|form_name
```

`field` is required for list, chart, `distinct` and numeric aggregate outputs. It is not
required for `total` or `form_name`. Filters and permissions reuse the common
CBStats engine. The JSON output remains the raw normalized array. Table and
chart names return their normalized `total` and `items` data, without HTML;
scalar outputs use the standard ContentBuilder NG API success envelope.
`ranges`, `titles`, `add`, `sort`, `dir` and `limit` use the same validation
and normalization path as article tags.

## 18. Status tracking

Codex should update this section in the real canonical documentation after each pass:

| Feature | Target pass | Status |
|---|---:|---|
| Existing outputs | Existing | Preserve |
| Common normalized engine | 1 | Implemented and validated |
| `output=json` | 1 | Implemented and validated |
| `output=pie` | 2 | Implemented and validated |
| `output=bar` | 3 | Implemented and validated |
| `ranges` | RC97 | Implemented and validated |
| `output=avg` | RC97 | Implemented and validated |
| `output=histogram` | RC97 | Implemented and validated |
| `output=line` | RC97 | Implemented and validated |
| `output=radar` | RC97 | Implemented and validated |
| `add` external counts | Intermediate | Implemented and validated |
| Signed `add` deltas and `titles` | Finalization | Implemented; awaiting prod-test validation |
| URL scalar outputs | 1C | Implemented and validated |
| Security/error hardening | Finalization | Implemented and validated |
| Cross-repository docs/API | 4 | Completed |
## Limiting the final result and hiding result elements

`limit` is optional and accepts a strictly positive integer written without
quotation marks:

```text
{CBStats id=25 field="Town" output="table" sort="value" dir="desc" limit=10}
{CBStats idsum=25+27 field="Club" output="bar" sort="value" dir="desc" limit=15}
```

It is applied after the existing `sort=none|title|value` and `dir=asc|desc`.
Without `limit`, every value is preserved. Empty, non-numeric, zero, negative,
decimal or out-of-range integer values are rejected as invalid requests.
It applies to `table`, `json`, `pie`, `bar`, `histogram`, `line` and `radar`;
scalar outputs are unchanged.

`hide` accepts `total`, `values` and `graph`, combined with `|` in any order.
`total` hides only the rendered total, `values` hides only the textual labels
and values list below the graph without changing labels, values, axes or
tooltips drawn inside the graph, and `graph` hides the chart drawing while
retaining that lightweight textual list. Without `hide`, all elements remain
visible. Hiding all three elements is rejected instead of producing an empty
block.

Common combinations are `hide="total|values"` for the complete graph only,
`hide="graph|total"` for the textual list only, `hide="graph"` for the textual
list and total, and `hide="graph|values"` for the total only. Order, surrounding
spaces and duplicates do not change the result.

The chart options apply to `pie`, `bar`, `histogram`, `line` and `radar`.
`output="table"` accepts only `hide="total"`. `json`, `min`, `max` and `avg`
reject presentation options that would hide their primary result. The former
`total=hide` syntax is no longer supported; use `hide="total"`.

The processing order is:

```text
source → filters → grouping → optional idsum merge → add/titles
→ sort/dir → limit → limited-total recalculation
→ rendering → validated presentation hiding
```

The displayed total and chart percentages are recalculated from the retained
values only. With `hide="total"`, that limited total remains available
internally for percentages. No `Other` category is added.

When `ranges=` is present, the displayed total is instead always the real
retained record count after permissions and filters. It is never the sum of
ranges, including after `limit`, because overlapping ranges can legitimately
count one record several times.

```text
{CBStats id=25 field="Name" output="table" sort="title" dir="asc" hide="total"}
{CBStats id=25 field="Email" output="histogram" sort="title" dir="asc" limit=50 hide="total"}
{CBStats idsum=25+27 field="Town" output="radar" sort="value" dir="desc" limit=10 hide="graph|total"}
{CBStats id=25 field="RegistrationDate" output="line" sort="value" dir="asc" hide="values"}
index.php?option=com_contentbuilderng&task=api.display&format=json&action=cbstats&id=25&field=Town&output=bar&hide=graph%7Ctotal
```

## Explicit numeric ranges

```text
ranges="18-29;30-39;40-49;50-59;60+"
```

Bounds are inclusive. `minimum-maximum` and `minimum+` are accepted. Empty and
non-numeric field values are ignored. Declaration order is preserved and ranges
are evaluated independently, so overlaps are intentionally supported:

```text
ranges="18-35;30-45;40-55;50+"
```

`ranges=` is strictly numeric. For example, `ranges="Gravel;Route"` is rejected
with a diagnostic identifying the first invalid item. To count text values, omit
`ranges=` and use the normal grouped output:

```text
{CBStats id=25 field=pratique output=bar}
```

`titles=` renames range labels. Ranges work with Table, JSON, Pie, Bar,
Histogram, Line and Radar, including an `idsum` source.

## Average, Histogram, Line and Radar

```text
{CBStats id=25 field=Age output=avg}
{CBStats id=25 field=Age output=histogram ranges="18-29;30-39;40-49;50-59;60+"}
{CBStats id=25 field=RegistrationDate output=line sort=title dir=asc limit=30}
{CBStats id=25 field=Age output=radar ranges="18-29;30-39;40-49;50-59;60+"}
```

`avg` is the arithmetic mean of original individual numeric values after ACLs,
filters and an optional `idsum` merge. It ignores empty and non-numeric values
and is independent from ranges.

Histogram and Line use the same normalized counts; neither creates missing
dates or values. Histogram stays vertical and uses horizontal scrolling when
needed. Radar requires at least 3 axes, accepts at most 8 and is recommended
with **4 to 6 axes**.

## Editorial Card markup

Joomla articles may group free HTML, CBStats and CBList tags in the shared Card
presentation with this standard HTML marker:

```html
<div class="cb-card-editorial" data-card="v1" data-w="33">
  <h4 data-cb-card-title>Information</h4>
  <p>Total: {CBStats id=15 output=total}</p>
</div>
```

A direct child `h1`–`h6` carrying `data-cb-card-title` becomes the coloured
Card header and remains visible in visual editors. An unmarked heading stays
in the body. The legacy `data-title` attribute remains supported with the
shared Hx/rem syntax and is used when no marked heading is present. `data-card`
accepts `h1`–`h6` or `v1`–`v6` and defaults to `v1`; `data-w` accepts `33`,
`66` or `100` and defaults to `33`. The complete explicit syntax is
recommended. Invalid Card or width values use their defaults. The standard
`div` is compatible with TinyMCE and JCE and renders through the existing
shared Card HTML and CSS.

## Reusable category titles with `titleset`

Use a managed INI file when the same value labels are needed in several tags:

```text
{CBStats id=15 field=Country titleset="example-en-GB.ini" output=table}
```

The custom directory `media/contentbuilderng/cbstats/titlesets/` is searched
before the provided directory `media/com_contentbuilderng/cbstats/titlesets/`.
Inline `titles=` mappings have priority over file mappings. The INI file uses
an optional `[metadata]` section and a required `[titles]` section containing
`original="Display label"` entries; semicolon-prefixed comments are supported.

Only a safe `.ini` filename without a path is accepted. Missing or invalid
files preserve original values without a frontend error. With Joomla Debug
enabled, the issue is recorded as a CBStats Warning. Manage custom files from
ContentBuilder NG → About → CBStats title sets.
