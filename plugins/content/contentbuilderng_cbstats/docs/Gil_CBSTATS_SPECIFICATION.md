# CBStats functional and technical specification

## 1. Purpose

CBStats is a generic Joomla content plugin integrated into the ContentBuilder NG repository. It exposes statistics through `{CBStats ...}` tags and must work with arbitrary ContentBuilder views and fields without embedding organization-specific knowledge.

Plugin location:

`plugins/content/contentbuilderng_cbstats`

## 2. Non-negotiable principles

### 2.1 Generic behavior

CBStats must not hardcode:

- a view ID;
- a field name;
- a real field value;
- a business label;
- a business-specific order;
- a business-specific number or percentage.

The caller provides context through tag parameters such as:

```text
id=IdVue
field=NomDuChamp
```

### 2.2 Existing CBStats public API stability

Existing syntax and behavior must remain functional unless a specific approved migration says otherwise.

Approved 6.1.10-RC01 numeric syntax migration: scalar numeric options `id` and
`limit`, and the numeric identifier list `idsum`, must be written without
quotation marks. Valid examples are `id=15`, `limit=10` and `idsum=15+16`.
Quoted forms such as `id="15"`, `limit="10"` and `idsum="15+16"` are invalid
and must produce a localized validation error. Numeric content inside textual
mapping options such as `values`, `add`, `ranges` or `filter[value]` is not
subject to this lexical rule.

### 2.3 One calculation engine

Field statistics must be calculated once in PHP and reused by table, JSON and chart outputs.

No visual renderer may implement its own independent filtering/grouping/counting logic.

## 3. Current public outputs to preserve

The plugin already accepts:

```text
output=total
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

These outputs are existing public contracts and must not regress.

### 3.1 Distinct values

`output=distinct` requires `field=` and returns the number of distinct,
non-empty values of that field after the normal CBStats selection and filtering
pipeline has completed. It reuses the same filtered `value => count` map as the
other field outputs; it must not implement filtering independently.

`filter[field]`, `filter[value]`, the same-field `value=` shorthand, wildcard
matching, `|` alternatives, view/source restrictions and permissions therefore
all run before the distinct count. With `idsum`, each view is filtered first and
identical remaining values are counted once after the maps are merged.

Examples:

```text
{CBStats id=25 field=Departement output=distinct}
{CBStats id=25 field=Departement value="78" output=distinct}
{CBStats id=25 field=Departement value="78|60" output=distinct}
```

## 4. Existing filtering behavior to preserve

Existing filters include:

```text
filter[field]=NomDuChamp
filter[value]=Valeur
```

The current behavior to preserve includes:

- trimming/normalization behavior already implemented by the plugin;
- wildcard `*` behavior;
- alternatives separated by `|`;
- permissions for STATS;
- `debug=1` behavior;
- existing empty-value behavior for `output=table`.

Before refactoring, Codex must inspect the implementation to capture exact semantics, including case sensitivity, whitespace handling and wildcard matching details. Do not guess or silently change existing behavior.

## 4.1 Multi-view source with `idsum`

`idsum=25+27` is an alternative to `id=` for field statistics. It accepts
two to five unique positive view identifiers separated by `+`; simultaneous
`id` and `idsum`, invalid identifiers and duplicate identifiers are rejected.

For every view, CBStats enforces STATS and field permissions and runs the
existing filter and grouping pipeline. It then adds counts with exactly
identical grouped labels. Values present in only one view remain present.
Signed additions, negative-to-zero normalization, title mappings and sorting
run once after this merge. The total is recalculated from the merged values.
`output=form_name` is not supported because the merged source has no single
view name.

## 5. Normalized field statistics engine

### 5.1 Target internal contract

Create or consolidate a common internal method, for example `getFieldStats()`, using the current `output=table` logic as the behavioral reference.

The exact function name may follow the existing code style; the important requirement is a single normalized source of truth.

Target PHP structure:

```php
[
    ['label' => '<actual field value>', 'value' => <calculated integer>],
]
```

### 5.2 Required responsibilities

The engine must:

- read actual values present in `field=NomDuChamp` for `id=IdVue`;
- apply existing `filter[field]` and `filter[value]` behavior;
- preserve trim, wildcard `*` and alternatives `|` behavior;
- preserve STATS permission checks;
- preserve `debug=1` behavior;
- group identical values according to current semantics;
- count occurrences;
- preserve the current treatment of empty values required by `output=table`.

### 5.3 Consumers

The normalized PHP array becomes the source for:

```text
output=table
output=json
output=pie
output=bar
output=histogram
output=line
output=radar
```

The HTML table uses intrinsic content width within a horizontally scrollable
responsive wrapper. Text cells align to the start, numeric cells align to the
end, and the shared Pie/Bar detail legend uses compact, readable row spacing.

### 5.4 External additions

Field-statistics outputs accept external counts through:

```text
add="Label=Number;Other label=Number"
```

The common engine trims labels and numbers, merges an existing label by signed
addition, creates a missing label and combines repeated labels. Accepted values
are strict signed integers (`5`, `+5`, `0`, `-5`). After the final `add` result is
calculated for a label, a negative result is temporarily normalized to `0` in
memory. That effective zero is used by title mappings, sorting, percentages and
all field-statistics renderers; source data and stored configuration are never
changed. As soon as a later calculated result is zero or positive, that result is
used normally. Label matching is exact after trimming. Invalid syntax still
rejects the complete `add` parameter; no partial addition is applied.

Display labels can be mapped with:

```text
titles="Original=Display title;Other original=Other display title"
```

`titles` is applied after `add` and before sorting. Unmapped labels stay unchanged;
two source labels mapped to the same display title remain separate normalized
items. `sort=title` uses final display titles. Semicolons delimit mappings and the
first equals sign separates the original and display title. Empty sides are invalid.

The complete order is filtering, grouping, signed `add`, `titles`, sorting and
output. `add` and `titles` apply to `table`, `json`, `pie`, `bar`, `histogram`,
`line` and `radar`; they do not change scalar outputs. URL/API list outputs
reuse the same parsers and normalization path.

The distinct `title=` parameter customizes the localized total label in Table,
Pie, Bar, Histogram, Line and Radar. An empty value uses the translated default; a missing final colon is
added with localized punctuation. `background=` optionally applies a validated
background to those HTML containers. Unicode is preserved and HTML is escaped.

## 6. JSON output

### 6.1 Syntax

```text
{CBStats id=IdVue field=NomDuChamp output=json}
```

### 6.2 Contract

Output is the JSON representation of the normalized PHP array:

```json
[
  {"label":"<actual value>","value":42}
]
```

### 6.3 Constraints

- Use the project's safe JSON encoding approach.
- No HTML in `output=json`.
- Valid UTF-8.
- No JavaScript wrapper.
- No chart rendering.
- No CSS.

## 7. Sorting

### 7.1 Public syntax

```text
sort=none|title|value
dir=asc|desc
```

### 7.2 Defaults

```text
sort=none
dir=asc
```

### 7.3 Semantics

- `sort=none`: preserve natural/current result order.
- `sort=title`: sort by actual label.
- `sort=value`: sort by numeric count/value.
- `dir=asc`: ascending.
- `dir=desc`: descending.

Sorting must be generic and usable by table, JSON and graphical consumers when applicable.

Do not add `custom` ordering until a separate generic contract defines how custom order is supplied without embedding business values in code.

## 8. Pie output

### 8.1 Syntax

```text
{CBStats id=IdVue field=NomDuChamp output=pie}
```

### 8.2 Data source

Pie must consume the normalized field statistics engine. It must not recalculate statistics.

### 8.3 Presentation

- Responsive chart.
- Target default maximum chart width: 300 px, without fixing the containing card width.
- Show percentage only inside sectors when labels fit/readable.
- Do not show raw count inside sectors by default.
- Do not systematically show field labels inside sectors.
- Show details in legend and tooltip.

Generic legend format:

```text
● <actual label> — <calculated value> (<calculated percentage> %)
```

Generic tooltip format:

```text
<actual label> : <calculated value> (<calculated percentage> %)
```

Generic total format:

```text
Total : <calculated sum>
```

All static text must use language strings. Do not hardcode a business noun such as `inscrits`.

## 9. Bar output

### 9.1 Syntax

```text
{CBStats id=IdVue field=NomDuChamp output=bar}
```

### 9.2 Data source

Bar must consume the same normalized field statistics engine.

### 9.3 Presentation

- Chart.js bar chart if Chart.js is the selected existing/project-approved renderer.
- Horizontal orientation using `indexAxis: 'y'` when compatible with the installed Chart.js version.
- Same generic detail format as Pie.
- Same tooltip semantics as Pie.
- Display numeric values on bars only when readable and without clutter.

## 10. Histogram, Line, Radar and Average outputs

The RC97 visual outputs consume the same normalized field-statistics array:

```text
{CBStats id=25 field=Age output=histogram ranges="18-29;30-39;40-49;50+"}
{CBStats id=25 field=RegistrationDate output=line sort=title dir=asc limit=30}
{CBStats id=25 field=Age output=radar ranges="18-29;30-39;40-49;50+"}
{CBStats id=25 field=Age output=avg}
```

Histogram is vertical and preserves declared numeric range order. Line plots
the final sorted categories without inventing missing values. Radar requires
3 to 8 axes and recommends 4 to 6. `avg` computes the arithmetic mean of
retained individual numeric values, ignoring empty and non-numeric values;
`ranges=` does not change that scalar calculation.

## 11. Front-end architecture

Target flow:

```text
ContentBuilder NG data
        ↓
CBStats PHP statistics engine
        ↓
Normalized PHP array
        ↓
JSON-safe payload
        ↓
Pie / Bar / Table renderers
```

The browser layer is a renderer, not a data source.

No AJAX is required merely to reconstruct statistics already available server-side.

## 12. Asset loading

Assets must be loaded once per page, including when multiple charts exist.

Potential assets include:

- Chart.js;
- chart data-label plugin if needed and compatible;
- CBStats CSS;
- CBStats JavaScript.

Codex must inspect the actual plugin manifest and media structure before selecting exact paths or filenames. Do not blindly force a historical suggested path when the repository already uses another convention.

All CBStats CSS classes must start with:

```text
cbstats-
```

## 13. Multiple charts per page

Requirements:

- unique HTML IDs;
- no duplicated global initialization;
- assets loaded once;
- each chart receives only its own dataset/configuration;
- no collision between two Pie charts, two Bar charts, or mixed chart types.

## 14. No-data behavior

When no data is available:

- render a clean localized message or empty-state output consistent with current plugin conventions;
- do not create an invalid chart;
- do not throw a JavaScript error;
- do not emit malformed JSON.

## 15. Escaping and encoding

- Escape field labels and values for HTML context.
- Use proper JSON encoding for JSON and JavaScript payloads.
- Never interpolate unescaped values into JavaScript source.
- Preserve UTF-8 characters.

## 16. Localization

Public labels, errors, empty-state messages, totals and help text must use language keys.

At minimum, preserve/update the language families already maintained by the plugin, including:

- `fr-FR`
- `en-GB`
- `de-DE`

Codex must inspect the actual repository language files and naming conventions before adding keys.

## 17. Debug mode

`debug=1` must recognize every supported output, including `avg`, `histogram`,
`line` and `radar`. Diagnostics must identify invalid output names, fields,
ranges, limits and hide combinations without exposing protected data.

Debug output must remain safe and must not expose secrets or unnecessary internals.

## 18. Documentation contract

Every public syntax change requires updates to:

- plugin-local documentation;
- public shortcode/API syntax reference;
- main ContentBuilder NG documentation/API reference where CBStats is exposed;
- language files/help text when relevant;
- plugin manifest description if the old description no longer reflects capabilities.

Before creating documentation files in the real repository, find and update existing canonical files.

## 19. Implementation phases

### Pass 1 — normalized engine + JSON

Implement the common engine and `output=json`. Do not implement charts.

### Pass 2 — Pie

After Pass 1 is validated, implement `output=pie` using the common engine.

### Pass 3 — Bar

After Pie is validated, implement `output=bar` using the same engine.

### Pass 4 — documentation/API consolidation

Ensure all descriptions, syntax references, examples and API documentation
reflect the implemented RC97 state, including `avg`, `histogram`, `line`,
`radar`, numeric `ranges`, `limit` and `hide`.

## 20. Definition of done

A pass is complete only when:

- implementation matches this specification;
- existing behavior remains compatible;
- relevant tests/checks pass;
- debug mode is updated as needed;
- language keys are updated as needed;
- documentation for the implemented public behavior is updated;
- the final Codex report lists changed files and test results.
## Final result options: `limit` and `hide`

- `limit` is an optional strictly positive integer.
- It slices normalized field statistics only after `add`, `titles`, `sort` and
  `dir` have produced the final ordered list.
- It applies to list outputs: Table, JSON, Pie, Bar, Histogram, Line and Radar.
- With `idsum`, views are merged before sorting and limiting.
- After limiting, the visible total is recalculated from the retained values.
  Pie and Bar percentages use that limited total as their denominator.
- No synthetic `Other` category is created.
- `hide` accepts only `total`, `values` and `graph`, combined with `|`.
- `hide="total"` suppresses the displayed total without changing its internal
  calculation.
- `hide="values"` suppresses only the complementary textual labels-and-values
  list below the graph. It does not change labels, values, axes or tooltips
  drawn inside the graph.
- `hide="graph"` suppresses the chart drawing while retaining lightweight
  textual values.
- These graph options apply to Pie, Bar, Histogram, Line and Radar. A
  non-applicable option produces a soft diagnostic; hiding all result elements
  is rejected instead of rendering an empty container.
- The former `total=hide` syntax is rejected and is not treated as an alias.
- Any invalid `hide` or `limit` value is rejected through the normal CBStats
  invalid-request path.

## Cards and responsive dimensions — 6.1.10-RC10-B3

`card=h1` to `card=h6` and `card=v1` to `card=v6` use the shared component
asset `com_contentbuilderng.cards`. This is opt-in. A header exists only with
an explicit non-empty `title=` and the inner title is not duplicated.
For H and V variants alike, the title is horizontal and above the content.
H variants use the available width. V variants are compact inline cards that
sit next to each other when space permits and become full-width on small screens.

Optional `w=33`, `w=66` and `w=100` make the Card span one, two or all three
columns of a `.cb-cards` grid. Values are strict unquoted numbers and require a
valid `card=` option. Without `w=`, V variants use 33 and H variants use 100.
All variants become full-width on small screens. When the current row has too
few free columns, the Card starts on the next row. `w=` controls the Card;
CBStats `width=` controls the chart inside it.

Example: `{CBStats id=15 field=Group output=bar title="Groups" card=v2 w=66 width=100%}`.

Chart options `width=` and `height=` accept a positive number, `px` or `%`.
A number without a unit means pixels. Without `width=`, Pie uses 80% with a
maximum of 350px and is centred; Bar and other charts use 100%. Explicit width
removes the Pie maximum. Explicit height disables maintained aspect ratio;
percentage height requires a parent with a defined height.

## Editorial Cards — 6.1.11-RC02

An article may group free HTML, CBStats and CBList tags in the shared Card
presentation with a standard editor-safe `div`:

```html
<div class="cb-card-editorial" data-card="v1" data-w="33">
  <h4 data-cb-card-title>Information</h4>
  <p>Total: {CBStats id=15 output=total}</p>
  <p>Distinct groups: {CBStats id=15 field=Group output=distinct}</p>
  {CBList id=15 fields="Nom|Prenom" limit=5}
</div>
```

The complete syntax is recommended. Missing or invalid `data-card` uses `v1`;
missing or invalid `data-w` uses `33`. A direct child `h1`–`h6` carrying
`data-cb-card-title` becomes the coloured Card header; an unmarked heading
remains in the body. The legacy `data-title` attribute remains supported and
reuses the shared H1–H6 and positive rem suffix parser. A visible marked
heading takes priority when both forms are present. The body
preserves normal Joomla article HTML and nested content-plugin tags.

The renderer uses `DOMDocument`, not a regular expression, to process nested
HTML. It converts the marker to the existing shared Card structure and loads
`com_contentbuilderng.cards`. Direct text nodes containing only whitespace or
non-breaking spaces are removed from `.cb-cards` grids so editor formatting
cannot create anonymous grid items or extra spacing. Custom elements such as
`<cb-card>` are unsupported because TinyMCE splits them and JCE removes them.

## 18. Reusable `titleset` mappings

`titleset="filename.ini"` loads display-only category mappings from an INI
file after the normal CBStats data and filtering pipeline. Files are resolved
first from `media/contentbuilderng/cbstats/titlesets/` and then from
`media/com_contentbuilderng/cbstats/titlesets/`. Only a safe basename ending
in `.ini` is accepted. Inline `titles=` mappings are applied last and therefore
override mappings loaded from `titleset`.

```ini
; Reusable labels
[metadata]
name="Main countries"
locale="en-GB"

[titles]
fr="France"
be="Belgium"
de="Germany"
```

```text
{CBStats id=15 field=Country titleset="example-en-GB.ini" output=table}
```

The administrator can manage these files from **ContentBuilder NG → About →
Actions → CBStats title sets**. Clicking a filename displays its contents.
Provided files remain read-only and can be duplicated into the site's custom
directory; site-specific files are editable. The list's column selector is
stored locally in the browser. Field tooltips document the INI metadata and
mapping values; a separate help screen is intentionally unnecessary.
The administration list can be searched by file title and sorted by any data
column; the Actions column is intentionally not sortable.
The editor accepts filenames with or without the `.ini` extension and appends
it when necessary. Validate and Save report their result, while Cancel and
browser navigation request confirmation when the form contains unsaved edits.
The list searches both filenames and file titles, displays 10 rows by default
with 5/10/25/50/All choices, and limits long titles to two visual lines. Source
labels identify the storage origin as `CBStats` or `Site`.
The list uses Joomla-style row selection and an Actions menu. CBStats files can
be duplicated; Site files can be edited, copied or deleted. Save as Copy writes
`name-copy.ini`, then `name-copy-2.ini` when needed. The language field suggests
installed Joomla languages while remaining free-form for tags such as `it-IT`.
Selected title sets can be exported as one `.ini` file or, for multiple files,
one `.zip` archive. Import accepts multiple `.ini` files up to 1 MB each. Every
file is parsed and validated before installation; invalid files and filenames
already present in the Site directory abort the import without overwriting.
Joomla selection state (`boxchecked`) enables Duplicate and Export for selected
CBStats files. The Language editor combines a visible installed-language
selector with the free-form language-tag input. Validation failures are errors
and identify Filename, Title or Entries directly.

Missing, unreadable, empty or invalid files preserve original values and never
replace the frontend result with an error. Joomla Debug records one Warning per
file and request. ContentBuilder NG About links to the native Joomla manager;
provided files are read-only and can be duplicated into the custom directory.
