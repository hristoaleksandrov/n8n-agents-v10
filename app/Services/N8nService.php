<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AdScriptTask;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class N8nService
{
    private string $webhookUrl;

    private string $secret;

    private int $timeout;

    public function __construct()
    {
        $this->webhookUrl = config('services.n8n.webhook_url');
        $this->secret = config('services.n8n.secret');
        $this->timeout = (int) config('services.n8n.timeout', 60);
    }

    public function triggerWorkflow(AdScriptTask $task): Response
    {
        $payload = $this->buildPayload($task);
        $signature = $this->generateSignature($payload);

        return Http::timeout($this->timeout)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'X-N8N-Signature' => $signature,
            ])
            ->post($this->webhookUrl, $payload);
    }

    public static function generateSignature(array $payload): string
    {
        $secret = config('services.n8n.secret');
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return hash_hmac('sha256', $json, $secret);
    }

    public static function verifySignature(string $signature, array $payload): bool
    {
        $expectedSignature = self::generateSignature($payload);

        return hash_equals($expectedSignature, $signature);
    }

    private function buildPayload(AdScriptTask $task): array
    {
        return [
            'task_id' => $task->id,
            'reference_script' => $task->reference_script,
            'outcome_description' => $task->outcome_description,
            'callback_url' => route('ad-scripts.result', ['task' => $task->id]),
        ];
    }
}
