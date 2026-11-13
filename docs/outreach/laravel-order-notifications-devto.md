# Send WhatsApp Order Notifications in Laravel with a PHP SDK

When an order ships, a customer wants a useful notification—not another dashboard to check. This walkthrough shows how to add a queued WhatsApp notification to an existing Laravel application using the [RelayWA PHP SDK](https://packagist.org/packages/relaywa/sdk).

**Disclosure:** RelayWA connects through QR-linked WhatsApp Web sessions. It is an unofficial integration, *not* Meta's WhatsApp Cloud API. Session connectivity and account availability may be interrupted. Evaluate that risk before using it for business-critical notifications.

## 1. Install the package

You need PHP 8.1+, Composer, cURL, JSON, and an existing Laravel project.

```bash
composer require relaywa/sdk
```

The [published Composer package](https://packagist.org/packages/relaywa/sdk) includes the `RelayWA\Client` class.

## 2. Connect a session and configure the key

Create a RelayWA account at [relaywa.com](https://relaywa.com), pair your own WhatsApp account by scanning the QR code, and create an API session key. Keep credentials on the server, not in JavaScript, the repository, or logs.

In your Laravel environment:

```dotenv
RELAYWA_SESSION_KEY=your_server_side_key
```

Add to `config/services.php`:

```php
'relaywa' => [
    'session_key' => env('RELAYWA_SESSION_KEY'),
],
```

On a configuration-cached Laravel deployment, refresh config according to your deployment practices.

## 3. Create a queued shipping notification

Create `app/Jobs/SendOrderShippedWhatsApp.php`:

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RelayWA\Client;
use Throwable;

final class SendOrderShippedWhatsApp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // A timeout could occur after the provider accepted the message.
    public int $tries = 1;

    public function __construct(
        public readonly string $recipient,
        public readonly string $orderNumber,
    ) {}

    public function handle(): void
    {
        $key = (string) config('services.relaywa.session_key', '');
        if (trim($key) === '') {
            throw new \RuntimeException('RelayWA session key missing.');
        }

        try {
            (new Client($key))->sendText(
                $this->recipient,
                sprintf('Your order #%s has shipped.', $this->orderNumber),
            );
        } catch (Throwable $e) {
            Log::warning('RelayWA order notification failed', [
                'error_type' => $e::class,
            ]);
            throw $e;
        }
    }
}
```

The queued job keeps the order workflow responsive and avoids automatically retrying a possibly delivered message. Use your existing Laravel queue setup and worker. This example does **not** guarantee exactly-once delivery.

## 4. Dispatch only to opted-in customers

Adapt this example to your Order and Customer model fields:

```php
use App\Jobs\SendOrderShippedWhatsApp;

// Require recorded WhatsApp consent for transactional notifications.
if ($order->customer->whatsapp_opted_in && $order->customer->phone_e164) {
    SendOrderShippedWhatsApp::dispatch(
        preg_replace('/\D+/', '', $order->customer->phone_e164),
        (string) $order->number,
    );
}
```

Only dispatch once per shipping event. In a production system, add an order-event outbox/deduplication key; do not assume the queue alone prevents duplicate events. Use international-format phone numbers with digits only.

## 5. Validate safely

You can inspect and run the SDK's [offline smoke checks](https://github.com/Haider-Shakoori/relaywa-php-sdk/blob/main/tests/offline-smoke.php) without an API key or sending a message:

```bash
php tests/offline-smoke.php
```

To exercise the real integration later, use a connected session and a recipient who explicitly expects the message. A successful API response is **not** a delivery guarantee. Monitor send status, connection health and failed messages; investigate uncertain outcomes before resending.

## 6. Common problems

- **401/403:** Check session key, account permissions and session status.
- **Cannot connect:** Pair the QR session again and confirm connectivity.
- **Transport timeout:** Do not immediately retry; the message may already have been accepted.
- **Composer install fails:** Verify PHP extensions and the [Packagist listing](https://packagist.org/packages/relaywa/sdk).
- **Unexpected duplicate:** Verify the order event is dispatched only once and implement idempotent outbox handling.

## Links and next steps

The [complete Laravel example](https://github.com/Haider-Shakoori/relaywa-php-sdk/tree/main/examples/laravel-order-notifications) contains code and deployment notes. Explore the [RelayWA API documentation](https://relaywa.com/api-docs), [QR session guide](https://relaywa.com/whatsapp-api-qr-code), [Web API vs Cloud API comparison](https://relaywa.com/guides/whatsapp-web-vs-cloud-api), or [plans and trial](https://relaywa.com/pricing).

Have a concrete Laravel integration question? Open a GitHub issue with sanitized reproduction steps. Never post your API key or recipient data.

---

**Suggested DEV Community metadata**

- Title: Send WhatsApp Order Notifications in Laravel with a PHP SDK
- Description: Build an opted-in, queued Laravel shipping notification with PHP 8.1+, error handling and offline checks.
- Tags: `laravel`, `php`, `tutorial`, `webdev`
- Canonical URL: Set only if the same article is first published on an accessible canonical website; otherwise omit.
- Campaign tracking: Use standard tagged links only after confirming the site accepts them, and measure registrations, connected sessions and first-message activations—not merely visits.
