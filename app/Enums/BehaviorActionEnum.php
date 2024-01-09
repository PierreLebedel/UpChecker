<?php

namespace App\Enums;

use App\Contracts\BehaviorAction\ActionEmail;
use App\Contracts\BehaviorAction\ActionNextcloudNotification;
use App\Contracts\BehaviorAction\ActionSms;
use App\Contracts\BehaviorActionInterface;
use App\Models\Action;

enum BehaviorActionEnum: string
{
    case Email = 'Email';
    //case Sms = 'SMS';
    case NextcloudNotification = 'NextcloudNotification';

    public function getDescription(): string
    {
        return match ($this) {
            self::Email                 => __('Send email'),
            //self::Sms                   => __('Send SMS'),
            self::NextcloudNotification => __('Send Nextcloud notification'),
        };
    }

    public function getClassName(): string
    {
        return match ($this) {
            self::Email => ActionEmail::class,
            //self::Sms      => ActionSms::class,
            self::NextcloudNotification => ActionNextcloudNotification::class,
        };
    }

    public function getInstance(Action $action): BehaviorActionInterface
    {
        $className = $this->getClassName();
        return new $className($action);
    }
}
