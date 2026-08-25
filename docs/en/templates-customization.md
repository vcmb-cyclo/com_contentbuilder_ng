# Templates and customization

ContentBuilder NG stores view-specific templates for record details, editing,
generated Joomla articles, emails, and some list output.

## Start from a generated example

The Details and Edit tabs can generate an example from the selected theme. Use that
output as the starting point instead of building the complete markup from memory.

Recommended workflow:

1. select a theme;
2. generate an example;
3. save and test it unchanged;
4. make one change at a time;
5. retain a copy outside the production database.

## Field variables

Templates and email settings reference fields by their technical name. The rendering
service (`TemplateRenderService`) replaces the following placeholders:

```text
{fieldname:label}     the field label
{fieldname:value}     the field value
{fieldname:item}      the editable field control inside an edit template
{value}               the raw value inside a column wrapper
{value_inline}        the raw value inside an article wrapper
{webpath fieldname}   absolute web path of an uploaded file
{CBSite} / {cbsite}   the site root URL
{hide-if-empty fieldname} ... {/hide}   hides a block when the field is empty
{hide-if-matches fieldname value} ... {/hide-if-matches}   hides a block when the field exactly matches that value
```

Use technical field names that remain stable when labels are translated.

## Display Conditions

`{hide-if-empty fieldname} ... {/hide}` hides the block when the field value is
empty. `{hide-if-matches fieldname value} ... {/hide-if-matches}` hides the block
when the current field value exactly matches `value`.

In details templates, these conditions apply to displayed values. In edit templates,
they also apply to read-only blocks using `{fieldname:value}`. A block containing
`{fieldname:item}` stays visible even when the value is empty or matches
`hide-if-matches`, so the user can enter or correct the field.

## Simple email example

```html
<p>New request:</p>
<p><strong>{name:label}</strong>: {name:value}</p>
{hide-if-empty message}
<p><strong>{message:label}</strong>: {message:value}</p>
{/hide}
```

Test both user and administrator templates and verify HTML/text mode.

## PHP preparation

Details and edit templates can run PHP preparation code before rendering. This is
powerful and carries the same risks as custom code:

- syntax errors can break the page;
- unescaped output can introduce XSS;
- SQL built manually can introduce injection vulnerabilities;
- upgrades do not validate custom business logic.

Restrict editing to trusted administrators. Prefer Joomla APIs, escape output, and
keep the code under version control.

## Column wrappers

Field options can wrap or transform display values. The language help describes
plain HTML wrappers, PHP transformations, and content-plugin tags.

Example:

```html
<strong>{value}</strong>
```

Do not inject untrusted values into raw attributes or scripts without escaping.

## Content plugins

Bundled content plugins include:

- `CBList`;
- `CBDownload`;
- `CBImageScale`;
- `CBRating`;
- `CBStats`;
- verification and permission-related output.

### Embedding a list view with CBList

The `CBList` content plugin embeds a complete interactive ContentBuilder NG
list view in a Joomla article. The view keeps its ACL permissions, filters,
pagination and record actions.

Simple example - displays view 15 with its configured fields, title, sorting,
pagination, actions, layout and theme, without any CBList override:

```text
{CBList id=15}
```

Realistic example with the principal options:

```text
{CBList id=15 fields="Nom|Prenom|Email" title="Registration list" sort="Nom|Prenom" dir="asc" pagination=25 limit=10 actions="detail|edit|export" layout=cards height=700 loading=lazy}
```

For `fields` and `sort`, use only exact, case-sensitive source element names
or exact reference IDs. Display labels and accent or case variants are invalid:
if the source name is `Prenom`, `Prénom` and `prenom` are rejected.
For `fields`, an exact existing element remains valid when it is unpublished or
disabled for list display. ContentBuilder simply omits that column; this state is
not a syntax error. Fields used by `sort` must still be visible list columns.

`id` identifies the ContentBuilder NG view and is required; project examples use
view `15`. `fields` filters and orders the displayed columns. `title` replaces
the visible view title; omit it to keep the configured title, or use the clearer
`title=hide` to hide it completely. `title=""` remains equivalent. `sort`
defines the initial order, and one `dir="asc"` or `dir="desc"`
applies to every sorted field. Use one `|`-separated direction per field only
when mixing directions. `pagination` sets the number of records per page.
`limit` keeps at most the first N accessible records after ACLs, search, filters
and effective sorting. It is not pagination: pagination and export apply to the
limited subset, and the displayed total does not reveal records beyond it.
`actions` selects the available controls on top of ACL. `layout`, `height` and
`loading` control the presentation and iframe loading.

Numeric options `id`, `height`, `pagination` and `limit` must be written without
quotation marks: use `limit=10`, not `limit="10"`.

- `layout=cards` displays records as cards instead of the standard table. It is
  the readable alias of the `listcard` layout. Omitting `layout` keeps the
  view's normal list layout.
- `height=700` sets the iframe's initial minimum height to 700 pixels. The frame
  can still grow automatically when the list needs more room.
- `loading=lazy` is the default and waits until the list approaches the visible
  part of the page before loading it. Use `loading=eager` to load it immediately.
- CBList uses the theme configured in the ContentBuilder NG view. **Thoth** is
  the default and fallback theme; there is no `theme=` option in the CBList tag.

CBList and CBStats can be used together in the same article, for example a
summary chart followed by its detailed registration list:

```text
{CBStats id=15 field=Ville output=bar}
{CBList id=15 fields="Nom|Prenom|Email" title="Registration list" sort="Nom|Prenom" dir="asc"}
```

CBStats inserts dynamic statistics from a ContentBuilder NG view into Joomla
content. Its general syntax is:

```text
{CBStats id=ViewID ...}
```

Examples:

```text
{CBStats id=3 output=total}
{CBStats id=3 output=form_name}
{CBStats id=3 field=FieldName output=table}
{CBStats id=3 field=FieldName output=json sort=title dir=asc}
{CBStats id=3 field=FieldName output=pie sort=value dir=desc}
{CBStats id=3 field=FieldName output=bar sort=value dir=desc}
{CBStats id=3 field=Age output=avg}
{CBStats id=3 field=Age output=histogram ranges="18-29;30-39;40-49;50+"}
{CBStats id=3 field=RegistrationDate output=line sort=title dir=asc limit=30}
{CBStats id=3 field=Age output=radar ranges="18-29;30-39;40-49;50+"}
{CBStats id=25 field=Route output=pie title="👥 Total registrations" export=manual}
{CBStats id=3 field=Category output=pie add="Existing=-2;External=3"}
{CBStats id=3 field=Category output=table titles="1=Group 1;2=Group 2"}
{CBStats id=3 field=Category output=bar add="1=-2;2=3" titles="1=Group 1;2=Group 2" sort=value dir=desc}
{CBStats id=3 field=Department value="78|60" output=distinct}
{CBStats id=3 field=FieldName output=sum}
{CBStats id=3 field=FieldName output=min}
{CBStats id=3 field=FieldName output=max}
{CBStats id=3 filter[field]=Status filter[value]="Open" output=total}
{CBStats id=3 filter[field]=Status filter[value]="Open*" output=total}
{CBStats id=3 filter[field]=Status filter[value]="Open* | Pending" output=total}
{CBStats idsum=25+27 field="Route" output="table" title="Monticyclo / Montigravel"}
{CBStats idsum=31+32+33+34+35 field="Distance" output="bar" title="BRM"}
```

### Merging views with `idsum`

Use `idsum=25+27` instead of `id=` to add statistics from two to five
ContentBuilder NG views. Identifiers are unique positive integers separated by
`+`; `id` and `idsum` cannot be combined. `field=` is required for every
`idsum` output, including `output=total`.

Each view first applies its STATS and field permissions, filters and normal
grouping. Counts with exactly identical labels are then added. Labels present
in only one view remain in the result. Processing continues once on the merged
data with `add`, negative-to-zero normalization, `titles`, final sorting and
output. The total is the sum of the final merged categories.

Invalid or duplicate identifiers, fewer than two or more than five views,
missing or inaccessible views, missing fields and `output=form_name` are
rejected. Duplicate identifiers are refused to prevent double counting.

### Frozen manual export

Add `export=manual` to a Pie, Bar or Table tag to show the final labels, values and total together with a visible `source=manual` tag. Filters, additions, renamed titles and sorting are already incorporated in the frozen data. The centered copy button copies exactly the displayed syntax, which can be pasted into another article without depending on the original view.

| Output | Result | `field` required |
| --- | --- | --- |
| `total` | Number of matching records | No |
| `form_name` | View title, or its name when the title is empty | No |
| `table` | Static HTML value/count table | Yes |
| `json` | Raw JSON array of `{label,value}` objects | Yes |
| `pie` | Responsive Pie chart | Yes |
| `bar` | Responsive horizontal bar chart | Yes |
| `histogram` | Responsive vertical histogram | Yes |
| `line` | Responsive line chart using normalized counts | Yes |
| `radar` | Responsive radar chart with 3 to 8 axes | Yes |
| `sum` | Count-weighted sum of numeric field values | Yes |
| `min`, `max` | Smallest and largest numeric value | Yes |
| `avg` | Arithmetic mean of retained numeric values | Yes |

`table`, `json`, `pie`, `bar`, `histogram`, `line` and `radar` consume the same
normalized PHP data. An empty table displays `0`; empty charts display a
localized no-data message. Histogram uses vertical buckets, Line preserves the
final sorted categories, and Radar requires at least 3 and at most 8 axes (4 to
6 are recommended). `avg` ignores empty and non-numeric values and returns the
mean of individual numeric values. JSON has no HTML or JavaScript wrapper:

```json
[
  {"label":"Value A","value":12},
  {"label":"Value B","value":7}
]
```

Use `filter[field]=FieldName` and `filter[value]="Value"` together. Without a
wildcard, `filter[value]="Open"` matches only the exact value. With
`filter[value]="Open*"`, values such as `Open` and `Open (external)` can
match. The `|` character separates alternatives and surrounding spaces are
trimmed. In article tags, `field=FieldName value="Value"` is also accepted as a
filter shorthand when `filter[field]` is absent.

The grouped field and filtered field may differ:

```text
{CBStats id=15 field=Element-1 filter[field]=Element-2 filter[value]="Dét* | 3 | 4" output=bar}
```

Here, `field=Element-1` is grouped and displayed, while
`filter[field]=Element-2` is used only to select records. `*` is a wildcard,
`|` separates alternatives, and surrounding spaces are trimmed. Without a
wildcard, matching is exact.

When the displayed field is also filtered, the following shorthand is strictly
equivalent to the complete filter on `Element-2`:

```text
{CBStats id=15 field=Element-2 value="Dét* | 3 | 4" output=bar}
```

`value=` is reserved for this same-field shorthand. Do not confuse it with
`values=`, which is used exclusively by `source=manual`.

Field-statistics outputs support `sort=none|title|value` and `dir=asc|desc`.
The defaults are `sort=none` and `dir=asc`. `sort=none` preserves the engine's
natural order; `sort=title` uses locale-aware natural label ordering;
`sort=value` compares counts numerically. `dir` changes the chosen sort direction.

For `table`, `json`, `pie`, `bar`, `histogram`, `line` and `radar`, `add="Label=SignedInteger"` applies
cumulative deltas: positive adds, zero changes nothing and negative removes
occurrences. If the final calculated result is negative, CBStats temporarily
uses `0` for that label before sorting, percentage calculation and rendering;
source data remains unchanged, and a later zero or positive result is used
normally. `titles="Original=Display title"` changes display labels
without changing source data or merging categories. Unmapped labels stay
unchanged. Processing order is data, filters, grouping, `add`, `titles`, sorting,
then output; `sort=title` uses final display titles. Semicolons delimit entries
and the first equals sign separates each pair.

Pie, Bar, Histogram, Line and Radar use the same normalized data, tooltips and
localized chart text. Charts are responsive and can coexist in any combination
on one page. Use `ranges="18-29;30-39;40-49;50+"` for inclusive numeric buckets,
`output=line sort=title dir=asc` for a date/category sequence, and
`output=radar` for a compact comparison of 3 to 8 dimensions.

`sum`, `min` and `max` return `0` when the matching field values are empty or not
all numeric. Date fields may provide chronological `min` and `max`, while `sum`
remains `0`. All field-based outputs enforce the field's API/Stats availability.

CBStats always enforces the view's STATS permission. For URL/API use, check the
view's **API + Rights** settings, API/Stats field availability and the **API** tab.
The supported URL outputs are `json`, `table`, `pie`, `bar`, `histogram`, `line`,
`radar`, `total`, `distinct`, `sum`, `min`, `max`, `avg` and `form_name`; list outputs also
accept `add`, `titles`, `sort`, `dir`, `ranges` and `limit`. In Joomla articles,
CBStats reports all independent tag syntax errors together, identifies the
affected parameter and value, and does not display statistics when the tag is
invalid. The error block links to localized public CBStats syntax help in a new
browser tab. Access-control and unexpected internal errors remain generic.
`debug=1` requests diagnostics only when DEBUG is enabled on the
target ContentBuilder NG view; it never grants access or changes view, field or
STATS permissions.

The complete syntax of every other content plugin is not exhaustively documented
in the repository: **To verify** from its installed plugin help and templates.

## Joomla overrides

Frontend layouts live in `site/tmpl/<view>/` in the source (installed under
`components/com_contentbuilderng/tmpl/`). Use Joomla template overrides for component
layout changes that should remain outside the extension package.

Bundled list layouts (the `list` view):

- `default` (table);
- `listcompact`;
- `listcard`;
- `listtiles`;
- `listone`, `listtwo`, `listthree`.

The standard Joomla override path is:

```text
templates/<your_template>/html/com_contentbuilderng/list/default.php
```

> ℹ️ **Note:** the Joomla **System > Site Templates > [your template] > Create
> Overrides** screen lists the component views and copies the chosen layout to the
> right location. The exact path depends on the view name (`list`, `details`, `edit`,
> `latest`, `publicforms`) and the layout — *to verify* in your installation.

## Do not edit directly

Avoid direct changes to:

- files under `components/com_contentbuilderng`;
- files under `administrator/components/com_contentbuilderng`;
- bundled plugin files;
- generated release dependencies.

An update can replace them. Use stored view templates, Joomla overrides, a custom
plugin, or a maintained project patch instead.

## Good practices

- keep templates small;
- escape user-controlled data;
- avoid direct SQL;
- test guest and authenticated contexts;
- test empty values and uploads;
- keep a versioned copy;
- review custom PHP after updates;
- disable Debug after diagnosis.

> 📷 *Screenshot to add: generating a template example and opening the PHP preparation editor — `docs/en/img/templates-preparation.png`*
### Limiting CBStats values and hiding result elements

Use `limit` after an existing sort to retain only the first statistical values.
Use `hide` with `total`, `values` or `graph`, separated by `|`. `total` hides
the displayed total, `values` hides only the textual labels-and-values list
below the graph without changing the graph itself, and `graph` hides the
drawing while retaining that lightweight textual list:

```text
{CBStats id=25 field="Town" output="table" sort="value" dir="desc" limit=10}
{CBStats idsum=25+27 field="Club" output="bar" sort="value" dir="desc" limit=10 hide="total"}
{CBStats id=25 field="Age" output="histogram" hide="total|values"}
{CBStats id=25 field="Age" output="radar" hide="graph|total"}
```

The displayed total and chart percentages are recalculated from the values
retained by `limit`. Hiding does not change calculations, ACLs or filters.
Hiding all three elements is rejected. The former `total=hide` syntax is no
longer supported; use `hide="total"`.

### Editorial Cards for free content

Use an editor-safe standard `div` to group free HTML, CBStats and CBList tags
with the shared Card presentation. The complete syntax is recommended:

```html
<div class="cb-cards">
  <div class="cb-card-editorial" data-card="v1" data-w="33">
    <h4 data-cb-card-title>Information</h4>
    <p>Total: {CBStats id=15 output=total}</p>
    <p>Distinct groups: {CBStats id=15 field=Group output=distinct}</p>
    {CBList id=15 fields="Nom|Prenom" limit=5}
  </div>
</div>
```

A visible `<h1>` to `<h6 data-cb-card-title>` becomes the coloured Card header
and remains editable in the visual editor. An Hx heading without this attribute
stays in the body. The legacy `data-title` syntax remains supported with its
shared H1–H6 and positive rem suffixes. Without either title form, no header is
rendered. `data-card` defaults to `v1` and
`data-w` defaults to `33`; accepted widths are `33`, `66` and `100`. Empty
whitespace and non-breaking spaces inserted between Cards by an editor are
ignored in `cb-cards` grids.

### Reusable CBStats title sets

Use a managed INI file when several statistics share the same display labels:

```text
{CBStats id=15 field=Department titleset="example-en-GB.ini" output=table}
```

Custom files in `media/contentbuilderng/cbstats/titlesets/` override provided
files with the same name. Inline `titles=` mappings remain higher priority. A
missing or invalid file leaves original values visible and only records a
Warning when Joomla Debug is enabled.

The editor is available from **ContentBuilder NG → About → Actions → CBStats
title sets**. Clicking a filename displays its contents. Provided files remain
read-only and can be duplicated into the site's directory; site-specific files
are editable. The list column selector remembers hidden columns in the browser.
The list also supports file-title search and sorting on its data columns.
In the editor, the `.ini` filename extension is optional. Validate and Save
display their result, and discarding unsaved changes requires confirmation.
