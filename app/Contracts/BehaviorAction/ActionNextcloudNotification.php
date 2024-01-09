<?php

namespace App\Contracts\BehaviorAction;

use App\Models\Checkup;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use App\Contracts\AccountType\AccountTypeNexcloud;

class ActionNextcloudNotification extends AbstractAction
{

    public static function needAccountType(): ?string
    {
        return AccountTypeNexcloud::class;
    }

    public function run(Checkup $checkup): void
    {
        if( !$this->action->account ){
            return;
        }

        $accountTypeInstance = $this->action->account->type->getInstance($this->action->account);

        $domain   = $accountTypeInstance->getCleanDomain($accountTypeInstance->getParam('domain'));
        $username = $accountTypeInstance->getParam('username');
        $password = $accountTypeInstance->getParam('password');

        if ( empty($domain) || empty($username) || empty($password) ) {
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

        $this->afterRun($checkup);

        dd($response->body());
    }
}
