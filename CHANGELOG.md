# Changelog

## 6.1.10-RC01 — 2026-08-06

### Added

- Added `limit=` to `{CBList}` to retain the first accessible, filtered and sorted records before pagination, with capped totals and matching full-subset export.

### Changed

- CBList and CBStats numeric options now require unquoted numeric syntax, including `id=15`, `pagination=20`, `limit=10` and `idsum=15+16`.

### Fixed

- Fixed upgrading from a version that predates the `field_size` column aborting with "Unknown column 'field_size'" during the 6.1.7.104 schema migration.
- Fixed `COM_CONTENTBUILDERNG_PERMISSIONS_NEW_NOT_ALLOWED` and the equivalent view-permission message rendering as raw untranslated keys on the front end, when raised by the system and permission-observer plugins outside the component's own dispatch.
- Fixed the admin Audit panel's repair result message not scrolling into view when triggered from deep in a long edit form.

## 6.1.8 — 2026-08-02

### Added

- Added native DATE, DATETIME, INTEGER, DECIMAL and BOOLEAN controls and validation to direct Storage forms, with inline field title, SQL type, size and required-state editing.
- Added `fields=` and `actions=` allow-lists to the `{CBList}` embed tag, restricting which columns and controls (search, state, publish, language, new, edit, delete, export, rating, detail, print) are shown, on top of ACL.
- Added multi-column initial sorting to `{CBList}` via `sort=`/`dir=`, with the same `|`-separated syntax as `{CBStats}`.
- Added a theme switcher to the admin preview banner, alongside the existing layout and colour-mode controls.
- Added a "CBList actions" section to the frontend debug panel, listing which `{CBList actions="..."}` terms apply on each screen and whether each is currently allowed.

### Changed

- Enabled the repository-wide PSR-12 quality gate and modernized CBStats with strict PHP types.
- Direct Storage synchronization now preserves unpublished elements and refreshes native controls when SQL field types change.
- Removed the unused `itemid=` parameter from `{CBList}`.

### Fixed

- Escaped free-text list and template output to prevent stored cross-site scripting.
- Fixed Storage field creation, required-state display, row saving, sorting, system-field publication and schema-audit regressions.
- Preserved CBList field and action context across navigation, saves and record actions, and hardened action permission checks.
- Fixed a fatal error on front-end list views caused by a missing import in the pagination layout.
- Fixed embedded `{CBList sort="..."}` views losing or inverting their order when paging through results, and returning a server error instead of falling back to the view's own order when `sort=` named an unknown column.

## 6.1.7-RC101 — 2026-07-31

### Added

- Added optional Details and Edit template locking, with regeneration from the source form on save.
- Added a versioned schema migration for the template-lock columns.

### Changed

- Refined the form audit, editor layout, preview permissions hint, publication and debug indicators.
- Refused editable-template generation only when it is requested and no published field is editable.

### Fixed

- Kept a failed locked-template regeneration from interrupting an otherwise valid form save.
- Returned a controlled validation error when explicit editable-template generation cannot be completed.

## 6.1.7-RC97

### CBStats 6.1.7-RC96-B05

- Corrected `hide="values"` so it hides only the textual labels-and-values list below charts.
- Preserved all labels, data values, axes, data labels and tooltips drawn inside Pie, Bar, Histogram, Line and Radar charts.
- Kept the RC96-B04 pipe and `%7C` normalization for combined `hide=` values.

### CBStats 6.1.7-RC96-B04

- Normalized complete pipe-separated `hide=` combinations for article and URL requests.
- Changed `hide="values"` to hide category/range labels, numeric values, data legends and tooltips while keeping the chart drawing visible.
- Added regression coverage for shortcode and `%7C` URL parsing plus all chart-only, text-only and total-only combinations.

### CBStats 6.1.7-RC95-B01

- Added `headers=` mappings for the two column headers of `output=table`.
- Preserved `headers=` in `export=manual`; Pie and Bar legends remain unchanged.
- Updated the plugin help and CB → View → API documentation in English, French and German.

### 6.1.7-RC95

- Added CBStats multi-view statistics merging with `idsum=`.
- Added final-result limiting with `limit=` and visual total suppression with `total="hide"`.
- Fixed limited Table, Pie and Bar totals and chart percentages so they use only the values actually displayed.
- Updated the CBStats plugin help, CB → View → API reference and related English, French and German documentation.

### 6.1.7-RC91

- Finalized the CBStats RC91 improvements validated through builds B01 to B07.

### CBStats 6.1.7-RC91-B07

- Added progressive exact-value, wildcard, alternative, cross-field and same-field filter examples to CB → View → API help.
- Clarified the distinction between `value=` and manual-source `values=`.

### CBStats 6.1.7-RC91-B06

- Documented the same-field `value=` filter shorthand in the CB → View → API help in English, French and German.

### CBStats 6.1.7-RC91-B05

- Completed the distributed English, French and German help with cross-field filter and same-field `value=` examples.
- Preserved the existing Pie, Table and Bar examples and escaped every example against execution.

### CBStats 6.1.7-RC91-B04

- Made Bar animation unconditional like Pie for validation, intentionally ignoring `prefers-reduced-motion` in the Bar renderer.

### CBStats 6.1.7-RC91-B03

- Made the horizontal Bar animation clearly visible by painting zero-width data before a native 900 ms Chart.js update.
- Restored the compact adaptive Bar canvas and capped bar thickness while retaining reduced category spacing.

### CBStats 6.1.7-RC91-B02

- Aligned the one-shot Bar appearance animation with Pie's native Chart.js 450 ms animation while disabling it for `prefers-reduced-motion: reduce`.

### CBStats 6.1.7-RC91-B01

- Added a discreet one-shot horizontal Bar animation from zero, disabled when `prefers-reduced-motion: reduce` is active.
- Reduced the empty space between Bar categories through Chart.js dataset sizing without reducing the adaptive chart height.
- Documented cross-field filters and the same-field `value=` shorthand, distinct from manual `values=`.
- Standardized the width of all CBStats administrator help blocks.

## 6.1.7-RC87 — 2026-07-19

### Changed

- Updated PhpSpreadsheet from 5.8.0 to 5.9.0 for XLSX import and export.

### Fixed

- Fixed administrator XLSX export routing and restored BreezingForms NG source loading after its Joomla 6 type-file rename.

## 6.1.7-RC86 — 2026-07-19

### Added

- CBStats `export=manual` displays the final normalized labels, values and total below Pie, Bar and Table outputs, together with the visible frozen `source=manual` syntax and an accessible centered copy action.

### Fixed

- CBStats manual export uses the final displayed labels and values after `titles=`, additions and sorting; only `export=manual` enables the export block.
- Restored the official Joomla plugin name `ContentBuilder NG - Content - CBStats` and the concise `title`/`titles` extension summary while retaining the full CBStats description.
- Added manual-export and case-sensitivity guidance to the CB / Views / API / CBStats help, with case-insensitive tag, option-name and keyword handling while preserving free-value casing.

## 6.1.7-RC83 — 2026-07-17

### Fixed

- CBStats Help now keeps a single `title=` tag example and presents `title=`, `titles=`, localized separator handling and rendered examples in separate readable paragraphs; the plugin descriptions use the same concise distinction in EN, FR and DE.
- Restored the RC84 CBStats Help typography so article tags use the native burgundy monospace style while URL examples remain blue links, and expanded the EN/FR/DE documentation for the singular `title=` total-label option without changing the statistics engine.
- CBStats total labels now use localized colon spacing and support a distinct Unicode-safe `title=` override across Table, Pie and Bar outputs; the total box uses a subtle theme-adaptive background and result containers accept a validated optional `background=` value.
- CBStats tables now use compact intrinsic-width columns with aligned numeric values, while the shared Pie/Bar detail legend uses tighter readable row spacing.
- Front-end Edit form: a non-group editable field entirely absent from the submitted data (for example rendered read-only by a stale `{name:value}` marker left in the editable template) no longer has its stored value silently wiped on save. Only a field genuinely posted empty by the user still clears it.

### Added

- CBStats supports frozen manual statistics with `source=manual` and escaped `values=` pairs for Pie, Bar, Table and Total, while reusing `add=`, `title=`, `titles=`, sorting, percentages and the existing rendering pipeline without querying a ContentBuilder view.
- CBStats now provides one normalized field-statistics engine shared by HTML tables, raw JSON, responsive Pie charts and horizontal Bar charts, with generic filters (`*`, `|`), locale-aware sorting, signed external `add=` deltas, display-label mappings through `titles=`, multi-chart pages and synchronized EN/FR/DE help.
- CBStats now normalizes a negative final `add=` result to `0` in memory before sorting, percentage calculation and rendering, without changing source data or blocking independent statistics.
- The ContentBuilder NG API now exposes CBStats data through `action=cbstats` with `json`, `total`, `sum`, `min`, `max` and `form_name` outputs, while preserving STATS and field permissions and concise production errors.
- New **Audit** button on the admin form edit screen (`view=form&layout=edit`), disabled while the form has unsaved changes. Reports form/source/element/record counts and consistency checks: elements out of sync with the data source, an unavailable data source, theme plugin issues, fields missing from the Details or Edit template, editable fields lacking an `{name:item}` marker (and the reverse), and unknown template markers.
- Debug-mode warning when an editable field's Edit template only uses `{name:value}`/`{name:label}` instead of `{name:item}`, surfacing the root cause of the save issue above instead of leaving it silent.

### Changed

- Renamed the default theme plugin from `joomla6` to `thoth` (`contentbuilderng_themes/thoth`). Existing form references to `joomla3` or `joomla6` are migrated to `thoth` on update, and the old `joomla6` plugin is uninstalled.
- Added a per-form "Show title breadcrumb" option (`show_title_breadcrumb`, enabled by default): the page title on the front-end Details and Edit views renders as a breadcrumb linking back to the list.
- Removed the Field/Value column headers from the front-end Edit screen and the dead `cb_filter_calendar_format` parameter.
- Removed the old component-specific AJAX stack based on `task=ajax.display`.
- Moved former AJAX actions to the component API endpoint with JSON responses.
- Migrated `rating` and `get_unique_values` to:
  - `index.php?option=com_contentbuilderng&task=api.display&format=json&action=rating`
  - `index.php?option=com_contentbuilderng&task=api.display&format=json&action=get-unique-values`
- Added `action=stats` for form-level statistics:
  - `index.php?option=com_contentbuilderng&task=api.display&format=json&action=stats&id=25`

### Migration Note

Old URL example:

```text
index.php?option=com_contentbuilder&task=ajax.display&id=25&subject=rating&record_id=16
```

New URL example:

```text
index.php?option=com_contentbuilderng&task=api.display&format=json&action=rating&id=25&record_id=16
```

There is no backward compatibility for `task=ajax.display`.
