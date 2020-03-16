<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Ixudra\Curl\Facades\Curl;

class Paystack extends Model
{
    public static function getAuthorizationUrl($paymentData)
    {
        $response = Curl::to(env("PAYSTACK_PAYMENT_URL") . "/transaction/initialize")
            ->withHeaders([
                'Authorization: Bearer ' . env('PAYSTACK_SECRET_KEY'),
                'Content-Type: application/json', 'Accept: application/json',
            ])
            ->withData($paymentData)
            ->post();

        $decodedRes = json_decode($response);
        return $decodedRes;
    }

    public static function verifyTransaction($reference)
    {
        $response = Curl::to(env("PAYSTACK_PAYMENT_URL") . "/transaction/verify/$reference")
            ->withHeaders([
                'Authorization: Bearer ' . env('PAYSTACK_SECRET_KEY'),
                'Content-Type: application/json', 'Accept: application/json',
            ])
            ->get();

        $paymentDetails = json_decode($response);

        return $paymentDetails;
    }

    public static function createTransferRecipient($recipientData)
    {
        $response = Curl::to(env("PAYSTACK_PAYMENT_URL") . "/transferrecipient")
            ->withHeaders(['Authorization: Bearer ' . env('PAYSTACK_SECRET_KEY'), 'Content-Type: application/json', 'Accept: application/json'])
            ->withData($recipientData)
            ->post();

        $decodedRes = json_decode($response);
        return $decodedRes;
    }

    public static function allBanks()
    {
        $response = Curl::to(env("PAYSTACK_PAYMENT_URL") . "/bank")
            ->withHeaders(['Content-Type: application/json', 'Accept: application/json'])
            ->get();

        $bankDetails = json_decode($response);

        return $bankDetails;
    }
}
