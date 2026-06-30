# Settings

The Settings page is the central configuration hub for the entire application. It's organized into seven tabs covering email, addresses, courier, payments, and system settings. All settings are saved to the database and can be changed without redeploying.

## Tabs

### Email / SMTP

Configure how the application sends emails. Has a nested sub-tab structure:

**SMTP & Sender**
- **Mail Driver** — Choose from Log (development), SMTP, Sendmail, Mailgun, Postmark, Resend, or Amazon SES.
- **Host / Port / Username / Password / Encryption** — SMTP server credentials. Password is revealable and can be left blank to keep the existing value.
- **From Address / From Name** — The sender identity shown in email headers.

**Email Templates** (six templates, each with the same structure):
- Welcome Email
- Email Verification
- Password Reset
- Challenge Enrollment
- Challenge Completion
- Order Receipt

Each template has:
- **Subject** — The email subject line.
- **Button Text** — The CTA button label (if applicable).
- **Title** — The heading inside the email body.
- **Body** — The main email content.
- **Placeholder pills** — Click to insert dynamic variables like `{{user_name}}`, `{{challenge_name}}`, `{{order_id}}`, etc.
- **Test Recipient** — Enter an email address and click **Send Test Email** to preview. Requires SMTP driver to be selected.

### HQ Addresses

Manage headquarters / medal origin addresses used for shipping label generation and delivery calculations.

- Add multiple addresses (reorderable, deletable).
- Each address has: location name, contact name, phone, email, full address, and country code.
- Toggle **Use as default origin address** to set which address is used by default.

### Courier

Configure the courier provider for medal shipments.

- **Provider** — None (manual fulfillment), EasyParcel, or DHL.
- **Sandbox Mode** — Use the provider's test environment.
- **EasyParcel Service ID** — Leave blank to auto-select the cheapest service.
- **Auto-pay orders** — Pay EasyParcel orders automatically using account credit.
- **Pickup Instructions** — Instructions for the courier.
- **API Credentials** — API key and secret, stored encrypted. Leave blank to keep existing values.
- **Parcel Defaults** — Default weight, dimensions, content description, parcel value, and fallback phone number.
- **Test Courier Connection** — Header action to verify API credentials are valid.

### Stripe

Configure Stripe payment processing.

- **Enable Stripe Payments** — Toggle on to accept real card payments.
- **Public Key / Secret Key / Webhook Secret** — Stripe API credentials. Secret key is revealable.
- **Test Stripe Connection** — Header action to verify the API key is valid. Shows available balance on success.

### Payments

Configure payment behavior.

- **Enable sandbox payment mode** — When enabled, users see a dummy "Pay" button instead of real Stripe processing. Use for development and demos.
- Sandbox notice is displayed when the toggle is on.

### SEO

Search engine optimization settings.

- **Site Title** — The `<title>` tag for the site.
- **Meta Description** — The meta description tag.
- **Keywords** — Comma-separated keywords.
- **Robots Meta** — The robots meta tag (default: `index, follow`).
- **Open Graph Image URL** — The default image for social media sharing.

### Logging

System logging configuration.

- **Default Channel** — Stack, Single File, Daily File, Slack, Syslog, Error Log, or Null.
- **Log Level** — Debug through Emergency.
- **Retention Days** — How many days daily log files are kept (default: 14).

## Saving Settings

Click the **Save** button (in the page header) to persist all changes. Settings are stored in the database and loaded on each request.

## Related Files

- `app/Filament/Pages/Settings.php` — The settings page with all tab definitions
- `app/Services/Settings.php` — The service that reads/writes settings to the database
- `app/Models/Setting.php` — The Eloquent model for the settings table
- `resources/views/filament/pages/settings.blade.php` — The blade template
