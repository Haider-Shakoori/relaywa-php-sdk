<?php
declare(strict_types=1);

// Offline smoke coverage: no API keys, no network, no actual WhatsApp messages.
require __DIR__ . '/../src/Client.php';

use RelayWA\Client;

function expectInvalid(callable $callback, string $label): void
{
    try {
        $callback();
    } catch (InvalidArgumentException $e) {
        echo "PASS: {$label}\n";
        return;
    }
    throw new RuntimeException("Expected InvalidArgumentException: {$label}");
}

expectInvalid(fn () => new Client(''), 'missing session key');
expectInvalid(fn () => new Client('test-key', 'http://example.com/api'), 'HTTP rejected');
expectInvalid(fn () => (new Client('test-key'))->sendText('', 'hello'), 'empty recipient');
expectInvalid(fn () => (new Client('test-key'))->sendText('123456789', ''), 'empty text');
expectInvalid(fn () => (new Client('test-key'))->createSession([]), 'missing session name');
expectInvalid(fn () => (new Client('test-key'))->session(''), 'missing session ID');
expectInvalid(fn () => (new Client('test-key'))->sendImage([
    'to' => '12025550123', 'url' => 'http://example.com/image.jpg',
    'mimeType' => 'image/jpeg', 'mediaSizeBytes' => 1024,
]), 'non-HTTPS media rejected');
expectInvalid(fn () => (new Client('test-key'))->createWebhook([
    'name' => 'Example', 'url' => 'http://example.com/webhook',
]), 'non-HTTPS webhook rejected');

echo "All offline smoke checks passed. No network calls performed.\n";
