# Laravel order shipped notification with RelayWA

A small integration example for an **existing Laravel application**, not a full Laravel project. It sends one transactional WhatsApp message using the published [relaywa/sdk](https://packagist.org/packages/relaywa/sdk) package.

**Important:** RelayWA connects through an unofficial QR-linked WhatsApp Web session. It is not Meta's official WhatsApp Cloud API. Connections and account availability are not guaranteed.

## Install

1. In your Laravel application, run `composer require relaywa/sdk`. Requires PHP 8.1+, cURL and JSON.
2. Register a RelayWA account, connect a WhatsApp session using its QR code, and generate a session API key.
3. Add `RELAYWA_SESSION_KEY=...` to the application's server-side environment. Do not commit the key.
4. Add the following entry inside the array returned by `config/services.php`:

```php
'relaywa' => [
    'session_key' => env('RELAYWA_SESSION_KEY'),
],
```

5. Copy [SendOrderShippedWhatsApp.php](app/Jobs/SendOrderShippedWhatsApp.php) to `app/Jobs/SendOrderShippedWhatsApp.php`.
6. Set up your normal Laravel queue worker, such as `php artisan queue:work --tries=1`. Make sure `QUEUE_CONNECTION` is configured appropriately. Refresh Laravel's configuration cache if you use it.

## Dispatch only after consent

When an order is marked shipped, check the business's stored opt-in status and recipient number **before** dispatching the job. For example, from your existing application service:

```php
use App\Jobs\SendOrderShippedWhatsApp;

// The application must record and verify permission to send WhatsApp updates.
// Adapt these property names to your real Order/Customer models.
if ($order->customer->whatsapp_opted_in && $order->customer->phone_e164) {
    SendOrderShippedWhatsApp::dispatch(
        preg_replace('/\D+/', '', $order->customer->phone_e164),
        (string) $order->number
    );
}
```

This snippet is illustrative and assumes your models already have those fields. Supply the phone number in international format with digits only.

## Safety and testing

- Start in a development environment, using a **connected session and a recipient who has opted in**.
- This repository's automated checks use **no API credentials and make no network calls**. They never send messages.
- Do not automatically retry unknown/timeout outcomes: a message may already have been delivered. This example sets `$tries = 1`. Investigate the failed job and message history before manually retrying.
- In a real ecommerce system, use an outbox or deduplication key to ensure you dispatch each order event only once; the sample job alone does not guarantee exactly-once delivery.
- A successful API response does not guarantee WhatsApp delivery. Monitor status and failures separately.
- Keep the API key server-side and avoid logging phone numbers, message bodies or tokens.

## Next steps

- [RelayWA API docs](https://relaywa.com/api-docs)
- [QR onboarding](https://relaywa.com/whatsapp-api-qr-code)
- [WhatsApp Web vs Cloud API](https://relaywa.com/guides/whatsapp-web-vs-cloud-api)
- [Pricing and trial](https://relaywa.com/pricing)
