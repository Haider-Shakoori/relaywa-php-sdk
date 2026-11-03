# RelayWA PHP SDK

[![Latest Version](https://img.shields.io/packagist/v/relaywa/sdk.svg)](https://packagist.org/packages/relaywa/sdk)
[![PHP Version](https://img.shields.io/packagist/php-v/relaywa/sdk.svg)](https://packagist.org/packages/relaywa/sdk)
[![License: MIT](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

PHP and Laravel client for the [RelayWA WhatsApp API](https://relaywa.com). Send WhatsApp messages, manage sessions, and work with webhooks from your own backend.

- Send text messages over a QR-linked WhatsApp session
- List and inspect sessions
- Works in plain PHP and Laravel
- Minimal dependencies: PHP 8.1+, `ext-curl`, `ext-json`

> **Note:** RelayWA connects through QR-linked WhatsApp Web sessions. It is an unofficial integration, not the Meta WhatsApp Cloud API. See [Web API vs Cloud API](https://relaywa.com/guides/whatsapp-web-vs-cloud-api).

> **Package availability:** The repository has been prepared for Packagist, but publication has not been verified. The Composer command below requires a published package and installable release.

## Installation

```bash
composer require relaywa/sdk
```

Requirements: PHP 8.1 or newer, `ext-curl`, `ext-json`, and Composer.

For local pre-release testing, from a project with a checkout of the RelayWA monorepo:

```bash
composer config repositories.relaywa path "/absolute/path/to/wa/sdks/php"
composer require "relaywa/sdk:@dev"
```

## Get your key

1. Create an account at [relaywa.com](https://relaywa.com).
2. Link a WhatsApp number by scanning its QR code ([how QR linking works](https://relaywa.com/whatsapp-api-qr-code)).
3. Create an API key for your session in the dashboard.

Keep the key in a server-side environment variable. Never commit credentials or expose them in browser code.

## Quick start (plain PHP)

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use RelayWA\Client;

$key = getenv('RELAYWA_SESSION_KEY');
if ($key === false || trim($key) === '') {
    throw new RuntimeException('Set RELAYWA_SESSION_KEY first');
}

$relaywa = new Client($key);

try {
    // List sessions (read-only)
    print_r($relaywa->sessions());

    // Sends a real WhatsApp message when the session is connected
    $result = $relaywa->sendText('12025550123', 'Hello from RelayWA');
    print_r($result);
} catch (RuntimeException $e) {
    fwrite(STDERR, 'RelayWA request failed: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
```

Run it:

```bash
RELAYWA_SESSION_KEY=your_key php example.php
```

Only run the send example with a connected session and a recipient who expects your message. Use a country code and digits-only phone number.

## Laravel

Add the key to your deployment environment or `.env` (never commit the file):

```dotenv
RELAYWA_SESSION_KEY=your_key
```

Register it in `config/services.php`:

```php
'relaywa' => [
    'session_key' => env('RELAYWA_SESSION_KEY'),
],
```

Use the client from server-side application code:

```php
<?php

use RelayWA\Client;

class OrderNotifier
{
    public function orderShipped(string $phone, string $orderNumber): void
    {
        $relaywa = new Client((string) config('services.relaywa.session_key'));
        $relaywa->sendText($phone, "Your order #{$orderNumber} has shipped.");
    }
}
```

For queues and retries, read [WhatsApp API order notifications in Laravel](https://relaywa.com/blog/laravel-whatsapp-api-order-notifications). After changing Laravel environment settings in production, refresh the config cache as appropriate.

## More examples

**Session details and chats (read-only)**

```php
$details = $relaywa->session('your-session-id');
$chats = $relaywa->sessionChats('your-session-id');
```

**Send an image by HTTPS URL**

```php
$result = $relaywa->sendImage([
    'to' => '12025550123',
    'url' => 'https://example.com/image.jpg',
    'mimeType' => 'image/jpeg',
    'mediaSizeBytes' => 102400,
]);
```

The media URL must be HTTPS and the size must fit the SDK's limit.

## Webhooks

RelayWA supports webhook endpoints for events such as incoming messages and session changes. Verify the signature before trusting an incoming request and handle repeated deliveries idempotently.

**Important:** This SDK does not currently provide a webhook-signature verification helper. Consult the [webhook documentation](https://relaywa.com/whatsapp-webhooks) for the exact header name, signature encoding, signed payload, and secret configuration before implementing verification. Do not assume a raw hex HMAC or a particular header name.

After verification, decode the JSON and store an event identifier to avoid processing duplicate deliveries.

## Supported methods

| Category | Methods |
| --- | --- |
| Text/media | `sendText`, `sendImage`, `sendVideo`, `sendAudio`, `sendDocument` |
| Interactive messages | `sendLocation`, `sendContact`, `sendPoll`, `sendReply`, `sendReaction` |
| Sessions | `sessions`, `session`, `createSession`, `updateSession`, `connectSession`, `restartSession`, `disconnectSession`, `deleteSession` |
| Session data | `sessionQrCode`, `sessionStatistics`, `sessionLogs`, `sessionMessages`, `sessionMessage`, `sessionContacts`, `sessionChats`, `sessionGroups` |
| Webhooks | `webhooks`, `createWebhook`, `updateWebhook`, `deleteWebhook`, `webhookDeliveries` |

Methods return decoded JSON arrays. Requests target `https://relaywa.com/api` by default.

## Safe sending tips

- Message people who expect to hear from you, and make opting out easy.
- Increase volume gradually on a new number rather than sending large bursts.
- Avoid repetitive bulk messaging; personalize where appropriate.
- Do not retry failed sends in a tight loop.
- Do not automatically retry ambiguous timeouts: a send might have succeeded despite the missing response.

## Error handling

The client throws `RuntimeException` for request failures. Catch exceptions and log only non-sensitive details.

| Problem | Fix |
| --- | --- |
| Composer cannot find `relaywa/sdk` | Check Packagist publication and release status; use a local path repository for pre-release testing. |
| `Class "RelayWA\\Client" not found` | Run `composer install` and include `vendor/autoload.php`. |
| Request rejected (401/403) | Check the key, account permissions, and session connection. |
| No response / `curl_init()` missing | Confirm `ext-curl` is enabled and check DNS, HTTPS connectivity, and firewall settings. |
| Session key required | Set `RELAYWA_SESSION_KEY` in the server environment. |

The SDK uses a 30-second request timeout, 10-second connection timeout, and disables HTTP redirects.

## Documentation and links

- Website: <https://relaywa.com>
- API documentation: <https://relaywa.com/api-docs>
- Pricing: <https://relaywa.com/pricing>
- Help center: <https://relaywa.com/help>
- Webhooks guide: <https://relaywa.com/whatsapp-webhooks>
- Source: <https://github.com/Haider-Shakoori/relaywa-php-sdk>
- Issues: <https://github.com/Haider-Shakoori/relaywa-php-sdk/issues>

## Maintainer publishing note

After CI validation, tag an installable release and register this repository on Packagist. Keep tags and package metadata aligned.

## License

MIT. See [LICENSE](LICENSE).
