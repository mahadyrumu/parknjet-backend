<?php

namespace App\Services\Reservation;

class JwtService
{

    public function base64urlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public function createJWT($payload)
    {
        // Create the header
        $header = json_encode([
            'alg' => 'HS256',
            'typ' => 'JWT'
        ]);

        // Encode header and payload
        $encoded_header = $this->base64urlEncode($header);
        $encoded_payload = $this->base64urlEncode(json_encode($payload));

        // Create the signature
        $signature = hash_hmac('sha256', "$encoded_header.$encoded_payload", true);
        $encoded_signature = $this->base64urlEncode($signature);

        // Combine to create the JWT
        return "$encoded_header.$encoded_payload.$encoded_signature";
    }

    public function generateJWT($lotType, $total, $couponCode, $walletDiscountDays, $originalDurationInDay)
    {
        $reservationData = [
            "lotType" => $lotType,
            "total" => $total,
            "couponCode" => $couponCode,
            "walletDays" => $walletDiscountDays,
            "numberOfDays" => $originalDurationInDay,
        ];

        $payload = [
            'reservationData' => $reservationData,
            'iat' => now()->timestamp, // Issued at
        ];

        $jwt = $this->createJWT($payload);
        return $jwt;
    }

    public function base64UrlDecode($data)
    {
        // Decode from base64url (replace - with +, _ with /, and remove any trailing =)
        $data = str_replace(['-', '_'], ['+', '/'], $data);
        return base64_decode(str_pad($data, strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    public function decodeJWT($jwt)
    {
        list($headerEncoded, $payloadEncoded, $signatureEncoded) = explode('.', $jwt); // Split the JWT into its three parts
        $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);
        return $payload;
    }
}
