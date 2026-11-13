<?php
declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RelayWA\Client;
use Throwable;

/**
 * Illustrative Laravel job; dispatch only for an opted-in recipient.
 * No automatic retries: a timeout may occur after the message was sent.
 */
final class SendOrderShippedWhatsApp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(
        public readonly string $recipient,
        public readonly string $orderNumber
    ) {}

    public function handle(): void
    {
        $key = (string) config('services.relaywa.session_key', '');
        if (trim($key) === '') {
            throw new \RuntimeException('RelayWA session key is not configured.');
        }

        // Only dispatch after recording recipient consent in your application.
        // Send only transactional notifications that the recipient expects.
        $message = sprintf('Your order #%s has shipped.', $this->orderNumber);
        try {
            (new Client($key))->sendText($this->recipient, $message);
        } catch (Throwable $error) {
            // Avoid logging message contents, recipient numbers, session keys or tokens.
            Log::warning('RelayWA order notification failed', [
                'error_type' => $error::class,
            ]);
            // Mark the job failed; do not blindly resend on an ambiguous timeout.
            throw $error;
        }
    }
}
