# Einheitliche Syntax von CBStats und CBList

Beide Plugins verwenden dieselbe Darstellungssyntax, soweit sie anwendbar ist:

```text
labels="title=..."
hide="title"
card=h1|h6|v1|v6
w=33|66|100
```

`labels` liefert den Titel der Ansicht und der Card. CBList unterstützt nur den Schlüssel `title`. CBStats unterstützt zusätzlich `category`, `value` und `total` für statistische Ergebnisse. `hide="title"` blendet nur den Ansichts- oder Card-Titel aus; CBList-Spalten, Aktionen und Seitennavigation bleiben unverändert.

Die empfohlene Reihenfolge in Beispielen ist: Quelle (`id`/`idsum`), Felder und Filter, Gruppierung, Ausgabe, labels und hide, Sortierung, Begrenzungen, danach Layout und Card-Darstellung.

Bei CBStats definiert `groups` Wertgruppen; Intervalle sind nur eine mögliche Form. Auch diskrete Gruppen wie `1,2,7,9=Gruppe 1` sind gültig. `groupset` lädt dieselbe Definition aus einer wiederverwendbaren INI-Datei.

`config="dateiname.ini"` lädt wiederverwendbare CBStats-Darstellungseinstellungen. Zulässig sind `[labels]` (`title`, `category`, `value`, `total`), `[presentation]` (`background`, `card`, `w`, `width`, `height`) und `[display]` (`hide`, `sort`, `dir`, `limit`). `w` akzeptiert nur `33`, `66` oder `100` und steuert die Card-Breite; `width` steuert das Diagramm. Inline-Optionen im Tag überschreiben die Datei schlüsselweise.

```text
{CBStats id=15 field=Distanz output=bar config="vcmb-config.ini" width=800 labels="title=Besondere Strecken"}
```

Beispiele verwenden die Ansichts-ID `15` als Platzhalter.

Technische Tag-Namen, Optionsnamen und Aufzählungswerte sind unabhängig von Groß-/Kleinschreibung (`{cbstats ...}`, `OUTPUT=BAR`, `dir=Asc`). Benutzerdaten wie Feldnamen, Filterwerte, Gruppenwerte und Beschriftungen bleiben groß-/kleinschreibungssensitiv.
