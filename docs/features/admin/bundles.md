# Bundle Management

Bundles let you package multiple challenges together at a discounted price. For example, a "Beginner Bundle" might include three beginner-level challenges for less than buying them individually.

## Bundle List

The table shows all bundles:

- **SKU** — Auto-generated as `BUND-00001`. Bold and searchable.
- **Name** — The bundle display name. Searchable.
- **Slug** — URL-friendly identifier. Hidden by default.
- **Challenges** — How many challenges are included.
- **Price USD / Price MYR** — The bundle price in both currencies.
- **Active** — Whether the bundle is visible on the storefront.

## Creating a Bundle

### Bundle Details

- **Name** — The display name. Slug auto-generates from this.
- **Slug** — Auto-generated, must be unique.
- **SKU** — Auto-generated as `BUND-XXXXX`. Can be overridden.
- **Description** — Rich text editor for the bundle description shown on the storefront.

### Pricing

- **Price USD** — Price in US dollars.
- **Price MYR** — Price in Malaysian ringgit.
- **Active** — Toggle to show/hide on the public site.

### Challenges

- **Challenges** — Multi-select dropdown of active challenges. Searchable. Pick which challenges are included in this bundle.

## How Bundles Work

When a player purchases a bundle, they get enrolled in all the challenges included in that bundle. The bundle price replaces the individual challenge prices. Bundles appear on the Browse Challenges page alongside individual challenges.

## Related Files

- `app/Filament/Resources/Bundles/BundleResource.php` — Resource definition
- `app/Filament/Resources/Bundles/Schemas/BundleForm.php` — Create/edit form
- `app/Filament/Resources/Bundles/Tables/BundlesTable.php` — List table
