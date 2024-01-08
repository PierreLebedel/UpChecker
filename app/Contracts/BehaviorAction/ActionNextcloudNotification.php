<?php

namespace App\Contracts\BehaviorAction;

use App\Models\Checkup;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ActionNextcloudNotification extends AbstractAction
{
    public function run(Checkup $checkup): void
    {
        $username = '';
        $password = '';
        $domain   = Str::of('https://www.test.com/nextcloud/')
            ->replaceStart('https://', '')
            ->replaceStart('http://', '')
            ->replaceEnd('/', '');

        if (empty($username) || empty($password) || $domain == 'www.test.com/nextcloud') {
            return;
        }

        $response = Http::withoutVerifying()
            ->withHeader('OCS-APIREQUEST', 'true')
            ->withBasicAuth($username, $password)
            ->withQueryParameters([
                'shortMessage' => config('app.name').': '.$checkup->endpoint->project->name.' check up',
                'longMessage'  => 'You receive this notification because the last checkup of endpoint '.$checkup->endpoint->url.' satisfied all the rules configured in '.config('app.name'),
            ])
            ->post('https://'.$domain.'/ocs/v2.php/apps/notifications/api/v2/admin_notifications/'.$username);

        dd($response->body());
    }
}
