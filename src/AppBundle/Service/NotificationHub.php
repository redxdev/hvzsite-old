<?php

namespace AppBundle\Service;

use AppBundle\External\Notification;

// Windows Azure Notification Hub
class NotificationHub {

    const API_VERSION = "?api-version=2013-10";

    private $endpoint;
    private $hubPath;
    private $sasKeyName;
    private $sasKeyValue;

    private $enabled;
    private $iosEnabled;
    private $androidEnabled;

    public function __construct($enabled, $connectionString, $hubPath, $iosEnabled, $androidEnabled) {
        $this->hubPath = $hubPath;

        $this->enabled = $enabled;

        if($enabled) {
            $this->parseConnectionString($connectionString);
        }

        $this->iosEnabled = $iosEnabled;
        $this->androidEnabled = $androidEnabled;
    }

    private function parseConnectionString($connectionString) {
        $parts = explode(";", $connectionString);
        if (sizeof($parts) != 3) {
            throw new \Exception("Error parsing connection string: " . $connectionString);
        }

        foreach ($parts as $part) {
            if (strpos($part, "Endpoint") === 0) {
                $this->endpoint = "https" . substr($part, 11);
            } else if (strpos($part, "SharedAccessKeyName") === 0) {
                $this->sasKeyName = substr($part, 20);
            } else if (strpos($part, "SharedAccessKey") === 0) {
                $this->sasKeyValue = substr($part, 16);
            }
        }
    }

    private function generateSasToken($uri) {
        $targetUri = strtolower(rawurlencode(strtolower($uri)));

        $expires = time();
        $expiresInMins = 60;
        $expires = $expires + $expiresInMins * 60;
        $toSign = $targetUri . "\n" . $expires;

        $signature = rawurlencode(base64_encode(hash_hmac('sha256', $toSign, $this->sasKeyValue, TRUE)));

        $token = "SharedAccessSignature sr=" . $targetUri . "&sig="
            . $signature . "&se=" . $expires . "&skn=" . $this->sasKeyName;

        return $token;
    }

    public function broadcastMessage($message) {
        // iOS
        if($this->iosEnabled) {
            $enc = json_encode(["aps" =>
                [
                    "alert" => [
                        "title" => "HvZ Announcement",
                        "body" => $message
                    ],
                    "sound" => "default"
                ]]);
            $notification = new Notification("apple", $enc);
            $this->broadcastNotification($notification);
        }

        // Android
        if($this->androidEnabled) {
            $enc = json_encode(["data" => ["msg" => $message]]);
            $notification = new Notification("gcm", $enc);
            $this->broadcastNotification($notification);
        }
    }

    public function broadcastNotification($notification) {
        $this->sendNotification($notification, "");
    }

    public function sendNotification($notification, $tagsOrTagExpression) {
        if(!$this->enabled)
            return;

        if (is_array($tagsOrTagExpression)) {
            $tagExpression = implode(" || ", $tagsOrTagExpression);
        } else {
            $tagExpression = $tagsOrTagExpression;
        }

        # build uri
        $uri = $this->endpoint . $this->hubPath . "/messages" . NotificationHub::API_VERSION;

        $ch = curl_init($uri);

        if (in_array($notification->format, ["template", "apple", "gcm"])) {
            $contentType = "application/json";
        } else {
            $contentType = "application/xml";
        }

        $token = $this->generateSasToken($uri);

        $headers = [
            'Authorization: '.$token,
            'Content-Type: '.$contentType,
            'ServiceBusNotification-Format: '.$notification->format
        ];

        if ("" !== $tagExpression) {
            $headers[] = 'ServiceBusNotification-Tags: '.$tagExpression;
        }

        # add headers for other platforms
        if (is_array($notification->headers)) {
            $headers = array_merge($headers, $notification->headers);
        }

        curl_setopt_array($ch, array(
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $notification->payload
        ));

        // Send the request
        $response = curl_exec($ch);

        // Check for errors
        if($response === FALSE){
            throw new \Exception(curl_error($ch));
        }

        $info = curl_getinfo($ch);

        if ($info['http_code'] <> 201) {
            throw new \Exception('Error sending notification: '. $info['http_code'] . ' msg: ' . $response);
        }

        //print_r($info);

        //echo $response;
    }
}