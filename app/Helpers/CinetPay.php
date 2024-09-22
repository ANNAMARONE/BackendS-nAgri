<?php
namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class CinetPay
{
    protected $client;
    protected $siteId;
    protected $apiKey;

    public function __construct($siteId, $apiKey)
    {
        // Initialisation du client Guzzle
        $this->client = new Client([
            'base_uri' => 'https://api-checkout.cinetpay.com/v2/payment/check', // Base URI pour CinetPay
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
        $this->siteId = $siteId;
        $this->apiKey = $apiKey;
    }

    public function generatePaymentLink($data)
    {
        try {
            // Ajoutez des données supplémentaires nécessaires pour la requête, comme site_id et api_key
            $response = $this->client->post('payment', [
                'json' => array_merge($data, [
                    'site_id' => $this->siteId,
                    'api_key' => $this->apiKey
                ]),
            ]);

            $body = json_decode($response->getBody(), true);

            if (isset($body['dat']['payment_url'])) {
                return $body['dat']['payment_url'];
            } else {
                $error = isset($body['error']) ? $body['error'] : 'Erreur inconnue';
                throw new \Exception('Erreur lors de la génération du lien de paiement: ' . $error);
            }
        } catch (RequestException $e) {
            \Log::error('Erreur CinetPay: ' . $e->getMessage());
            throw new \Exception('Erreur lors de la génération du lien de paiement: ' . $e->getMessage());
        }
    }
}
