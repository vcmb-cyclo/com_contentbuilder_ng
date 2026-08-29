# CBStats and CBList syntax alignment

Both plugins use the same presentation syntax where it applies:

```text
labels="title=..."
hide="title"
card=h1|h6|v1|v6
w=33|66|100
```

`labels` supplies the view and Card title. CBList supports the `title` key only. CBStats also supports `category`, `value` and `total` labels for statistical results. `hide="title"` hides the view/Card heading only; it does not hide CBList columns, actions or pagination.

Common option order in examples is: source (`id`/`idsum`), fields and filters, grouping, output, labels and hide, sorting, limits, then layout and Card presentation.

CBStats `groups` defines value groups; intervals are only one form. Discrete groups such as `1,2,7,9=Group 1` are equally valid. `groupset` loads the same definition from a reusable INI file.

`config="filename.ini"` loads reusable CBStats presentation settings. Supported sections are `[labels]` (`title`, `category`, `value`, `total`), `[presentation]` (`background`, `card`, `w`, `width`, `height`) and `[display]` (`hide`, `sort`, `dir`, `limit`). `w` accepts only `33`, `66` or `100` and controls the Card width; `width` controls its chart. Inline tag options override the file key by key.

```text
{CBStats id=15 field=Distance output=bar config="vcmb-config.ini" width=800 labels="title=Special routes"}
```

Examples use view ID `15` as the documentation placeholder.

Technical tag names, option names and enumerated values are case-insensitive (`{cbstats ...}`, `OUTPUT=BAR`, `dir=Asc`). User data such as field names, filter values, group values and labels remains case-sensitive.
