<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SendPushNotificationPersonalizeJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $tokens;
    protected $title;
    protected $body;

    public function __construct(array $tokens, $title, $body)
    {
        $this->tokens = $tokens;
        $this->title = $title;
        $this->body = $body;
    }

    public function handle()
    {
        $accessToken = $this->getAccessToken();

        foreach ($this->tokens as $token) {
            $this->sendToFCM($accessToken, $token, $this->title, $this->body);
        }
    }

    private function getAccessToken()
    {
        $path = public_path('assets/zarin-tarabar-firebase-adminsdk-9x6c3-7dbc939cac.json');
        $serviceAccount = json_decode(file_get_contents($path), true);

        $clientEmail = $serviceAccount['client_email'];
        $privateKey = $serviceAccount['private_key'];

        $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
        $now = time();
        $payload = json_encode([
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/cloud-platform https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ]);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $privateKey, 'sha256');
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));

        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);

        return $data['access_token'] ?? null;
    }

    private function sendToFCM($accessToken, $token, $title, $body)
    {
        $url = 'https://fcm.googleapis.com/v1/projects/zarin-tarabar/messages:send';

        $message = [
            "message" => [
                "token" => $token,
                "notification" => [
                    "title" => $title,
                    "body" => $body,
                ],
            ],
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
        curl_exec($ch);
        curl_close($ch);
    }
}
