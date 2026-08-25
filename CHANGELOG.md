# Changelog

## 6.1.11-RC03 — 2026-08-25

- Added reusable CBStats `titleset="file.ini"` mappings with safe custom/provided directory resolution and inline `titles=` priority.
- Added silent frontend fallback and Debug-only Warnings for missing or invalid title-set files.
- Added a native Joomla title-set manager from About, including structured metadata, comments, mappings, validation, duplication and custom-file management.
- Added a complete French department title set and multilingual country examples.

## 6.1.11-RC02 — 2026-08-25

- Added editor-safe editorial Cards using a standard `div.cb-card-editorial`, with visually editable marked H1–H6 headers, compatible `data-title` support, and existing Card colours and widths.
- Added automatic removal of empty whitespace and non-breaking-space text nodes between Cards to prevent unwanted grid spacing introduced by editors.

## 6.1.11-RC01 — 2026-08-25

- Added CBStats `output=distinct` to count distinct non-empty field values after the existing view, source and value filters have been applied.
- Added article-tag and URL/API documentation and regression coverage for filtered distinct counts.

## 6.1.10 — 2026-08-23

- Added read-only About Audit detection of orphan BreezingForms rows in `#__contentbuilderng_records`.
- Added an idempotent About Repair step that revalidates the BreezingForms record ID and form ID before removing only proven ContentBuilder orphan rows.
- Hardened CBStats totals and language aggregates for BreezingForms NG sources by joining the real BF record on both identifiers.
- Fixed the BreezingForms NG editable-override deletion path so its ContentBuilder record mapping is removed before the BF record is deleted.

- Prevented empty locked Detail/Edit templates from showing false warning or error states when no corresponding published fields are active.
- Enabled Detail and Edit template locks by default for newly created views.
- Added shared CBList/CBStats Card-title formatting with `h1`–`h6` and positive `remX` / `remX.X` suffixes; Card titles now default to semantic `h4` headings.
- Expanded CBStats outputs, filters and responsive Pie, Bar, Histogram, Line and Radar sizing, with hardened `output=total` handling for BreezingForms NG sources.
- Expanded CBList with filtered `output=value`, field/action allow-lists, multi-column sorting and controlled embedded-list limits.
- Added shared responsive Card rendering and CSS for CBList and CBStats, including configurable widths, colours and heading levels.
- Reworked pagination with centralized choices, compact long-list navigation and a configurable Backend View pagination limit.
- Added concise CBList/CBStats API guidance in the View editor and complete public integration documentation.
- Improved Joomla 6 and PHP 8.3+ compatibility, storage field handling, update migrations, permissions and frontend output safety.
- Coordinated BreezingForms NG deletion cleanup with CBNG synchronization so deleted BF records no longer leave valid-looking ContentBuilder mappings.

## 6.1.10-RC11 — 2026-08-22

- Immediately refreshes all server-derived tab indicators and locked templates after a View-element action saved through AJAX.
- Added coherent green, orange and red status indicators for Introduction, States, Detail and Edit, with contextual explanations in View Help.
- Added contextual Actions for State, Introduction, Detail, Edit and Article resets, including automatic saving after one confirmation.
- Added per-element Export selection, Published master locking and independent ID, State and Published export controls.
- Made locked Edit templates follow the published and editable element selection consistently.
- Reworked the View editor for expert administrators with compact controls, contextual Actions and tab-specific Help.
- Added a hidden five-click ContentBuilder NG piranha animation to the About screen.
- Packaged the reusable 2026 animated WebP in the component's shared media directory.

## 6.1.10-RC11-B15 — 2026-08-21

- Made Published the visible master switch for Export, matching Detail and Edit.
- Unpublished elements now show a lock for a preserved active Export setting or a disabled cross when Export is off.
- Immediately refreshes the Export symbol when an element is published or unpublished without changing its stored Export choice.

## 6.1.10-RC11-B14 — 2026-08-21

- Moved the type-specific Edit control into the compact display-options row.
- Removed its dedicated vertical block so the upload directory and template editor appear earlier.

## 6.1.10-RC11-B13 — 2026-08-21

- Rebuilt View Help as a contextual manual that opens directly on the active tab.
- Added concise purpose, workflow, essential checks and warnings for every main configuration tab.
- Added compact navigation across all help sections and retained complete State, Detail and Edit indicator guidance.

## 6.1.10-RC11-B12 — 2026-08-21

- Moved Introduction and Article resets into contextual Actions with automatic saving.
- Moved Detail, Edit and State status and permission guidance into the View Help page.
- Compacted the expert-oriented Edit tab, including a single-line upload-directory row and earlier template editor placement.

## 6.1.10-RC11-B11 — 2026-08-21

- Removed the duplicated legacy Create Template buttons from Detail and Edit.
- Made the contextual action read Create or Regenerate according to the current template, with confirmation only before replacement.
- Grouped the template lock on the left with the display options in one compact panel.

## 6.1.10-RC11-B10 — 2026-08-21

- Aligned the Detail/Edit functional symbol above the lock at a consistent height.
- Aligned the lock with the bottom of the tab label and reduced the surrounding horizontal spacing.

## 6.1.10-RC11-B9 — 2026-08-21

- Stacked the functional Detail/Edit indicator directly above the template lock.
- Automatically applies every contextual Detail, Edit and State action after its single confirmation.
- Kept New-record access independent when disabling Edit; the full reset still disables both.

## 6.1.10-RC11-B8 — 2026-08-21

- Moved the Detail/Edit lock beside the tab label so it no longer masks the functional indicator.
- Added contextual Detail and Edit actions for display reset, template regeneration and frontend disabling.
- Reserved the full Detail/Edit reset for Debug mode and Super Users.
- Hidden View element actions outside the View tab.

## 6.1.10-RC11-B7 — 2026-08-21

- Replaced the small gray/green/red tab dots with a green check, an orange
  triangle, and a red cross for Intro, Detail, Edit, and State.
- Added an incomplete state when a valid screen configuration is not fully
  accessible because a permission, button, or link is missing.
- Kept the absence of a symbol for features that are not configured.

## 6.1.10-RC11-B6 — 2026-08-21

### Added

- Added an Export capability per element, with a visible View column, column-selector entry and bulk Actions commands.
- Added a compact export-system-column selector for ID, State and Published, independent of frontend display settings.

### Changed

- Exports now include only elements that are both published and enabled for Export.
- Locked and newly generated Edit templates now contain only published editable elements; Thoth no longer adds non-editable read-only values.
- Existing views preserve their system-column export behavior during migration.

## 6.1.10-RC11-B4 — 2026-08-21

### Fixed

- Synchronized the Coloris swatch, native color input and text preview immediately after every State reset, without requiring a Save and reload.

## 6.1.10-RC11-B3 — 2026-08-21

### Fixed

- Targeted the actual Joomla child toolbar elements so the Actions menu switches reliably between View commands and State reset commands.
- Made State reset clicks work with Joomla dropdown items whose custom data attributes are not rendered by the native toolbar layout.
- Changed the asset version to prevent browsers from retaining the previous contextual Actions script.

## 6.1.10-RC11-B2 — 2026-08-21

### Changed

- Moved the State reset commands into the global Actions toolbar and made that toolbar contextual to the active View editor tab.
- Added precise dynamic indicators, hover explanations and legends for the Introduction, Details and Edit tabs, including combined template status and lock states.

### Fixed

- Closed and disabled the Actions dropdown when the last View element is deselected or when the active tab no longer supports its commands.

## 6.1.10-RC11-B1 — 2026-08-21

### Added

- Added a multi-level State reset menu for cleaning inactive states, restoring the four-color base palette, disabling view states, and performing a Debug-only full reset for Super Users.
- Added contextual State-tab indicators, precise hover explanations, a complete indicator legend, and help tooltips for Published, Title, Color, and Action.
- Added a dedicated internal specification for list-state configuration and reset behavior.

### Changed

- New views now receive four published base states in green, orange, yellow, and red; the remaining six states are clean, white, and unpublished.

## 6.1.10-RC10 — 2026-08-20

### Added

- Added `{CBList output=value}` with strict single-field selection, implicit `sort=ID dir=desc offset=0`, optional `offset=` and plain Unicode text output without an iframe.
- Added `actions=none` and `pagination=0` to render embedded CBList data without list actions, selectors, pagination controls or empty control containers.
- Added shared responsive Cards for CBList and CBStats with `card=h1` to `card=h6`, `card=v1` to `card=v6`, the `.cb-cards` grid and strict `w=33`, `w=66` and `w=100` card widths.
- Added strict CBStats `width=` and `height=` chart dimensions, accepting positive pixel values or percentages.
- Added a configurable default page size for the administrator View tab.

### Changed

- Made CBStats Pie charts responsive and centred by default at 80% width with a 350px maximum; Bar, Histogram, Line and Radar now use the available width without causing horizontal scrolling.
- Simplified the View API tab into concise CBList and CBStats summaries with examples, test URLs and links to the complete public help pages.
- Expanded the CBList and CBStats English, French and German help into example-driven references covering the complete public syntax, Card grids and visual overrides.
- Applied precise option-and-value syntax validation messages consistently to CBList and CBStats.

### Fixed

- Removed empty CBList toolbar containers when no control is rendered.
- Corrected shared Card titles so they remain horizontal above their content and aligned Cards occupy the intended responsive grid columns.

## 6.1.10-RC09 — 2026-08-14

### Added

- Added the Joomla List View menu builder with ordered field visibility, labels, links, search, sorting, filters, actions, access restrictions, themes and inherited view defaults.
- Added dedicated Cards, Compact Table and Tiles menu layouts alongside the standard List View.
- Added schema support and migration for the persisted menu list configuration.

### Changed

- Made List View the single standard tabular menu type and migrated legacy Classic menu items to it.
- Kept menu access restrictions separate from visible list action buttons and propagated the effective configuration consistently to list, detail, edit and export screens.
- Improved administrator previews, read-only field presentation and multilingual menu configuration help.

## 6.1.10-RC08 — 2026-08-11

### Changed

- Changed the ContentBuilder NG administrator landing page from Storages to Views.
- Made the About page Options return to the Views screen after saving or closing the component configuration.

## 6.1.10-RC07 — 2026-08-10

### Fixed

- Fixed the View list-limit control not enabling Joomla's Save actions after selecting a standard, custom, inherited or All value.

## 6.1.10-RC06 — 2026-08-10

### Added

- Added reusable Joomla-style menu controls with dynamic inheritance labels, a per-menu theme choice and a true reset of menu overrides.
- Added centralized global pagination choices shared by component configuration, ContentBuilder views, Joomla menus and frontend lists.

### Changed

- Reorganized ContentBuilder menu parameters into clear native Joomla sections and placed the List View menu types first.
- Consolidated the five legacy numbered list menu layouts into the standard List View while retaining the dedicated Cards, Compact Table and Tiles layouts.

### Fixed

- Preserved the selected ContentBuilder view during component updates and menu override resets.
- Fixed menu language loading, encoded group values, filter help and ordering controls across the supported list layouts.
- Fixed inherited and custom list limits, including numeric placement of custom frontend values and accurate `Use Default (…)` labels.

## 6.1.10-RC03 — 2026-08-09

### Fixed

- Fixed the dynamic filter order label rendering as the raw `COM_CONTENTBUILDERNG_ORDER_LABEL` key in the Joomla Menu Item editor.
- Fixed ContentBuilder List menu items reverting to the first published view after a component update by preserving Joomla's root-level menu parameters and migrating previously nested values back to the standard format.
- Updated the vulnerable development dependencies PHP_CodeSniffer, fast-uri, js-yaml and PostCSS to their patched compatible releases.

## 6.1.10-RC02 — 2026-08-06

### Changed

- Replaced long frontend list pagination with a compact shared layout showing the first, last and nearby pages without horizontal scrolling.

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
