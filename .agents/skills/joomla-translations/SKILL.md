---
name: joomla-translations
description: >
  Use when adding or changing a user-facing translation key or value in a
  Joomla 6 extension, or when editing a language .ini file. Do not use for a
  refactor that leaves the displayed text unchanged.
---

# Joomla 6 translations

Apply this skill only when the user-facing text or its language key actually
changes. A technical change to an unchanged `Text::_()`, `Text::sprintf()` or
`Text::plural()` call does not trigger translation work.

## Required changes

- Update the changed key in `en-GB`, `fr-FR` and `de-DE` in the corresponding
  component language files, unless the user explicitly scopes the work
  differently.
- Keep the three values semantically equivalent and preserve every format
  placeholder (`%s`, `%d`, `%1$s`, etc.). Use positional placeholders when the
  word order differs between languages.
- Use existing project key conventions and Joomla plural keys. Do not reorder
  unrelated entries merely to make language files identical.
- Keep manifest name and description strings in `.sys.ini`; runtime strings
  belong in the regular `.ini` file.

## Context-specific checks

- For a JavaScript-exposed string, declare the key with
  `Text::script()` in PHP before reading it with `Joomla.Text._()` in JS.
- For a plural change, follow the plural-key pattern already used by the
  target language files and verify the singular, plural and zero forms when
  they exist.
- Check the actual component layout: admin files are under
  `admin/language/<locale>/` and frontend files under `site/language/<locale>/`.

## French and German quality

- French: use correct accents, grammar and interface capitalization. Use
  French quotation marks and non-breaking spaces only when appropriate for the
  output format; do not insert HTML entities into an INI value blindly.
- German: use a consistent formal register (`Sie`) for the administration UI.
- Do not leave a changed key untranslated or silently copy English as a
  placeholder. If a definitive translation cannot be supplied, report it.

## Final check

Review the diff and confirm that every changed key exists in the required
locale files, that placeholders match, and that no unrelated language entries
were modified.
