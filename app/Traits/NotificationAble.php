<?php

namespace App\Traits;

use App\Jobs\PushNotificationJob;
use App\Models\DeviceToken;
use Edujugon\PushNotification\PushNotification;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Trait Notification
 * @package App\Traits
 */
trait NotificationAble
{
    /**
     * Send push notification
     */
    public function sendPushNotification($title, $body, $data = null)
    {
        try {
            $deviceTokens = $this->deviceTokens()->pluck('device_token')->toArray();
            if (!empty($deviceTokens)) {
                PushNotificationJob::dispatch([
                    'title' => $title,
                    'body' => $body,
                    'tokens' => $deviceTokens,
                    'data' => $data
                ]);

                // Log::info('sendPushNotification Title: '. $title);
                // Log::info('sendPushNotification Data: '. json_encode($data));
                // Log::info('sendPushNotification deviceTokens: '. json_encode($deviceTokens));
                // $push = new PushNotification('fcm');
                // $icon = asset('images/favicon.png');
                // if (isset($data['icon'])) {
                //     $icon = $data['icon'];
                //     unset($data['icon']);
                // }
                // $push->setMessage([
                //     'notification' => [
                //         'title' => $title,
                //         'body' => $body,
                //         'sound' => 'default',
                //         'icon' => $icon
                //     ],
                //     'data' => $data,
                //     'content_available' => true,
                //     'priority' => 'high',
                // ])
                //     ->setApiKey(env('FCM_SERVER_KEY'))
                //     ->setDevicesToken($deviceTokens)
                //     ->send()
                //     ->getFeedback();

                // $resultsArray = optional(optional($push->service)->feedback)->results;
                // if (!empty($resultsArray)) {
                //     foreach ($resultsArray as $index => $result) {
                //         if (isset($result->error) && !empty($result->error) && $result->error == 'NotRegistered') {
                //             $token = $deviceTokens[$index] ?? null;
                //             if (!empty($token)) {
                //                 if (isset($this->id)) {
                //                     $this->deviceTokens()->where('device_token', $token)->delete();
                //                 } else {
                //                     DeviceToken::where('device_token', $token)->delete();
                //                 }
                //             }
                //         }
                //     }
                // }
                // if (isset($_GET['log'])) {
                //     echo "<pre>";
                //     print_r($push);
                //     die;
                // }
            }
        } catch (Exception $e) {
            Log::info('Error' . $e->getMessage());
            return $e->getMessage();
        }
    }

    /**
     * Send multiple/bulk push notifications
     */
    public function sendBulkPushNotifications($title, $body, $deviceTokens, $data = null)
    {
        try {
            if (!empty($deviceTokens)) {
                PushNotificationJob::dispatch([
                    'title' => $title,
                    'body' => $body,
                    'tokens' => $deviceTokens,
                    'data' => $data,
                ]);

                // $push = new PushNotification('fcm');

                // $push->setMessage([
                //     'notification' => [
                //         'title' => $title,
                //         'body' => $body,
                //         'sound' => 'default',
                //     ],
                //     'data' => $data,
                // ])
                //     ->setApiKey(env('FCM_SERVER_KEY'))
                //     ->setDevicesToken($deviceTokens)
                //     ->send()
                //     ->getFeedback();

                // $resultsArray = optional(optional($push->service)->feedback)->results;
                // if (!empty($resultsArray)) {
                //     foreach ($resultsArray as $index => $result) {
                //         if (isset($result->error) && !empty($result->error) && $result->error == 'NotRegistered') {
                //             $token = $deviceTokens[$index] ?? null;
                //             if (!empty($token)) {
                //                 if (isset($this->id)) {
                //                     $this->deviceTokens()->where('device_token', $token)->delete();
                //                 } else {
                //                     DeviceToken::where('device_token', $token)->delete();
                //                 }
                //             }
                //         }
                //     }
                // }
                // if (isset($_GET['log'])) {
                //     echo "<pre>";
                //     print_r($push);
                //     die;
                // }
            }
        } catch (Exception $e) {
            Log::info('Error' . $e->getMessage());
            return $e->getMessage();
        }
    }
}
