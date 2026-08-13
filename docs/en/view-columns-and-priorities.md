# View columns and permission priority

This page explains the **List**, **Search**, **Link**, **Detail**, **Edit**,
**API** and **Publish** columns in a ContentBuilder NG View.

## Two different publication levels

ContentBuilder NG uses publication at two distinct levels:

1. **Publish in the field table** publishes a field in the View. It determines
   whether List, Search, Link, Detail, Edit or API may use that field.
2. **Record publication** controls the published or unpublished state of a
   data row. View options and the Publish/Unpublish ACL control this state.

Publishing a field therefore does not grant permission to publish a record.

## Column meanings

| Column | Function |
| --- | --- |
| **List** | Displays the field as a column in the list. |
| **Search** | Allows list search to query the field. A field may be searchable without being displayed. |
| **Link** | Turns the displayed value into a link to the Detail screen. It does not select the Detail content. |
| **Detail** | Makes the field available to the Detail screen. A Detail template and permission to access the record are still required. |
| **Edit** | Makes the field available for editing. An Edit template and record permissions are still required. |
| **API** | Allows the API, statistics and some plugin outputs to expose or query the field. API permissions still apply. |
| **Publish** | Master switch for the field in this View. |

## Priority order

Effective access follows this order:

1. **Field Publish**: the field must be published in the View;
2. **field function**: List, Search, Link, Detail, Edit or API must be enabled;
3. **Joomla menu item**: a List View menu may reduce selected functions but
   cannot exceed its parent View;
4. **View permissions and Joomla ACL**: the user must retain permission to
   access the record or perform the action;
5. **record state**: published-record visibility options still apply.

A lower layer may remove access but can never restore access denied by a higher
layer.

## Unpublished fields and locks

When **Publish = No**, every field function is inactive. Their configured
values are preserved so that publishing the field again restores its previous
configuration.

The locks displayed under **Detail** and **Edit** indicate this state for the
two most sensitive uses: exposing and modifying a value. They are not an
additional ACL.

Read-only fields use the same frontend presentation whether the restriction
comes from the parent View or from a Joomla List View menu: a **Read-only**
badge, a forbidden cursor, italic text and a still-visible selected value for
radio groups.

