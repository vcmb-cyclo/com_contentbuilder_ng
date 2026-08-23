# Interface partagée et Cards CSS

L'asset Joomla commun `com_contentbuilderng.cards` charge
`media/css/cards.css`. CBList, CBStats et les futures extensions utilisent cet
asset sans dupliquer les styles. Toutes les classes partagées sont préfixées
`cb-`. Le rendu historique reste le défaut et la Card ne fixe jamais la grille
ou le nombre de colonnes de l'article.

La syntaxe commune est `card=h1` à `card=h6`, ou `card=v1` à `card=v6`. Les
classes sont `cb-card`, `cb-card-header`, `cb-card-body`, `cb-card-h1` à
`cb-card-h6` et `cb-card-v1` à `cb-card-v6`. Un header est créé uniquement si
un `title=` explicite contient du texte. Pour toutes les variantes H et V, le
titre reste horizontal et placé au-dessus du contenu. Les variantes H occupent
la largeur disponible. Les variantes V sont compactes, se juxtaposent lorsque
l'espace le permet et repassent en pleine largeur sur petit écran.
Les titres des Cards sont centrés par défaut.

Le titre est rendu en `h4` par défaut. Le dernier `|` d'un `title=` peut
indiquer un niveau `h1` à `h6`, sans distinction de casse, ou une taille
visuelle positive `remX` / `remX.X`. Les espaces autour du séparateur sont
ignorés. Avec `rem`, le niveau sémantique reste `h4`. Un suffixe inconnu reste
dans le titre complet, qui utilise alors le rendu `h4` par défaut.

```text
title="Départements | h4"
title="Départements | rem1.25"
```

L'option publique facultative `w=` règle la largeur de la Card dans la grille :
`w=33` occupe une colonne, `w=66` deux colonnes et `w=100` toute la ligne.
Seules ces trois valeurs numériques sans guillemets sont admises et `w=` exige
la présence de `card=`. Sans `w=`, une variante V vaut 33 et une variante H
vaut 100. Si l'espace restant est insuffisant, CSS Grid place la Card sur la
ligne suivante. Sur petit écran, toutes les largeurs passent à 100.

`w=` règle la largeur de la Card ; l'option CBStats `width=` règle uniquement
la largeur du graphique à l'intérieur de cette Card.

Le conteneur commun facultatif `.cb-cards` organise trois variantes V par ligne
sur PC. Une variante H placée directement dans ce conteneur occupe la ligne
complète. Sur petit écran, le conteneur passe à une colonne. Toutes les Cards à
juxtaposer doivent être dans le même conteneur, sans élément `<br>` entre elles.

```html
<div class="cb-cards">
{CBStats id=15 field=Groupe output=pie title="Groupes" card=v1 w=33}
{CBStats id=15 field=Prenom output=bar title="Prénoms" card=v2 w=66}
{CBList id=15 fields="Nom|Prenom|Email" title="Derniers inscrits" card=h1 w=100}
</div>
```

Les propriétés publiques sont `--cb-card-accent`, `--cb-card-header-bg`, `--cb-card-header-color`,
`--cb-card-bg`, `--cb-card-color` et `--cb-card-border-color` :

```css
.cb-card-h1 {
    --cb-card-header-bg: #005a9c;
    --cb-card-header-color: #fff;
}
```

Palette par défaut : H1/V1 bleu `#2878b5`, H2/V2 vert `#5a9b55`, H3/V3
orange `#e09335`, H4/V4 violet `#8b63a8`, H5/V5 rouge `#c75b5b` et H6/V6
ardoise `#607d8b`.
