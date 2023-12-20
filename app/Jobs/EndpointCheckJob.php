<?php

namespace App\Jobs;

use App\Models\Endpoint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class EndpointCheckJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $endpoint;

    public $project;

    public function __construct(Endpoint $endpoint)
    {
        $this->endpoint = $endpoint;
        $this->project = $this->endpoint->project;
    }

    public function handle(): void
    {
        $request_start = microtime(true);

        try {
            $response = Http::withoutVerifying()
                ->timeout($this->endpoint->timeout)
                ->get($this->endpoint->url);
            $response_ok = $response->ok();
            $response_status = $response->status();
            $response_error_message = null;

        } catch (\Exception $e) {
            //dump($e->getMessage());
            $response_ok = false;
            $response_status = null;
            $response_error_message = $e->getMessage();
        }

        $response_time = microtime(true) - $request_start;

        // $this->check->update([
        //     'processed_at'           => now(),
        //     'response_ok'            => $response_ok,
        //     'response_status'        => $response_status,
        //     'response_time'          => $response_time,
        //     'response_error_message' => $response_error_message,
        // ]);

        // if(!$this->check->isSuccessful()){
        //     if($this->link->last_check_successful){
        //         Mail::send(new CheckNotSuccessfulMail($this->check));
        //     }
        // }

        // $this->link->update([
        //     'last_check_successful' => $this->check->isSuccessful(),
        // ]);
    }
}
