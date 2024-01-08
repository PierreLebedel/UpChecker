<?php

namespace App\Jobs;

use App\Events\CheckupCreatedEvent;
use App\Models\Checkup;
use App\Models\Endpoint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class EndpointCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $endpoint;

    public $project;

    public function __construct(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
        $this->project = $this->endpoint->project;
    }

    public function handle(): void
    {
        $checkup_started_at = now();
        $response_microtime = microtime(true);

        try {
            $response = Http::withoutVerifying()
                ->timeout($this->endpoint->timeout)
                ->get($this->endpoint->url);
            $response_status_code = $response->status();
            $response_error_message = null;

        } catch (\Exception $e) {
            $response_status_code = null;
            $response_error_message = $e->getMessage();
        }

        $response_microtime = microtime(true) - $response_microtime;

        $checkup = Checkup::create([
            'endpoint_id'       => $this->endpoint->id,
            'started_at'        => $checkup_started_at,
            'microtime'         => $response_microtime,
            'url'               => $this->endpoint->url,
            'status_code'       => $response_status_code,
            'exception_message' => $response_error_message,
        ]);

        CheckupCreatedEvent::dispatch($checkup);
    }
}
