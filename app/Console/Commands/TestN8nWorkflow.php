<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\N8nService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestN8nWorkflow extends Command
{
    protected $signature = 'n8n:test
                            {--url= : Custom n8n webhook URL to test}
                            {--timeout=30 : Request timeout in seconds}';

    protected $description = 'Test n8n workflow connectivity and response';

    public function handle(N8nService $n8nService): int
    {
        $url = $this->option('url') ?? config('services.n8n.webhook_url');
        $timeout = (int) $this->option('timeout');

        $this->info('Testing n8n workflow...');
        $this->newLine();

        $this->line("Webhook URL: <comment>{$url}</comment>");
        $this->line("Timeout: <comment>{$timeout}s</comment>");
        $this->newLine();

        $testPayload = [
            'task_id' => 0,
            'reference_script' => 'Test script for connectivity check.',
            'outcome_description' => 'Verify n8n workflow is responding.',
        ];

        $this->info('Sending test request...');

        $timestamp = time();
        $payload = json_encode($testPayload);
        $signature = hash_hmac('sha256', $payload.$timestamp, config('services.n8n.secret'));

        try {
            $startTime = microtime(true);

            $response = Http::timeout($timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Webhook-Signature' => $signature,
                    'X-Webhook-Timestamp' => $timestamp,
                ])
                ->post($url, $testPayload);

            $duration = round((microtime(true) - $startTime) * 1000);

            $this->newLine();

            if ($response->successful()) {
                $this->info("Status: <fg=green>OK</> ({$response->status()})");
                $this->line("Response time: <comment>{$duration}ms</comment>");
                $this->newLine();

                $body = $response->json();
                if ($body) {
                    $this->info('Response:');
                    $this->line(json_encode($body, JSON_PRETTY_PRINT));
                }

                $this->newLine();
                $this->info('n8n workflow is working correctly!');

                return Command::SUCCESS;
            }

            $this->error("Status: FAILED ({$response->status()})");
            $this->line("Response time: <comment>{$duration}ms</comment>");
            $this->newLine();
            $this->error('Response body:');
            $this->line($response->body());

            return Command::FAILURE;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $this->newLine();
            $this->error('Connection failed: '.$e->getMessage());
            $this->newLine();
            $this->warn('Possible issues:');
            $this->line('  - n8n is not running');
            $this->line('  - Webhook URL is incorrect');
            $this->line('  - Network connectivity issues');
            $this->line('  - Workflow is not activated');

            return Command::FAILURE;
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('Error: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
