<?php

namespace App\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class InternalHttpClient
{
    private Client $httpClient;
    private ?string $bearerToken;
    private int $timeout;
    private int $retries;
    private ?string $apiKeyId = null;
    private ?string $userId = null;
    private ?string $publicKeyId = null;
    private ?string $privateKeyId = null;
    private ?string $environment = null;
    private ?string $requestUuid = null;
    private ?string $ipAddress = null;
    private ?array $permissions = null;

    public function __construct(?string $bearerToken = null, int $timeout = 30, int $retries = 1)
    {
        $this->httpClient = new Client([
            'timeout' => $timeout,
            'connect_timeout' => 5,
            'verify' => false, // À ajuster selon votre environnement
        ]);

        $this->bearerToken = $bearerToken;
        $this->timeout = $timeout;
        $this->retries = $retries;

        $this->apiKeyId = null;
        $this->userId = null;
        $this->publicKeyId = null;
        $this->privateKeyId = null;
        $this->environment = null;
        $this->requestUuid = null;
        $this->ipAddress = null;
        $this->permissions = null;
    }


    /**
     * Requête GET
     */
    public function get(Request $request, string $serviceUrl, string $endpoint, array $permissions = []): array
    {
        return $this->call($request, $serviceUrl, $endpoint, [], 'GET', $permissions);
    }

    /**
     * Requête POST
     */
    public function post($request, string $serviceUrl, string $endpoint, array $data = [], array $permissions = []): array
    {
        return $this->call($request, $serviceUrl, $endpoint, $data, 'POST', $permissions);
    }

    /**
     * Requête PUT
     */
    public function put(Request $request, string $serviceUrl, string $endpoint, array $data = [], array $permissions = []): array
    {
        return $this->call($request, $serviceUrl, $endpoint, $data, 'PUT', $permissions);
    }

    /**
     * Requête PATCH
     */
    public function patch(Request $request, string $serviceUrl, string $endpoint, array $data = [], array $permissions = []): array
    {
        return $this->call($request, $serviceUrl, $endpoint, $data, 'PATCH', $permissions);
    }

    /**
     * Requête DELETE
     */
    public function delete(Request $request, string $serviceUrl, string $endpoint, array $permissions = []): array
    {
        return $this->call($request, $serviceUrl, $endpoint, [], 'DELETE', $permissions);
    }
 
 

    /**
     * Méthode mise à jour pour passer les headers à logApiUsage
     */
    private function call(Request $request, string $serviceUrl, string $endpoint, array $data = [], string $method = 'GET', array $permissions = [])
    { 
        $url = rtrim($serviceUrl, '/') . '/' . ltrim($endpoint, '/');

        $headers = $this->buildHeaders($permissions);
        $options = [
            'headers' => $headers,
            'timeout' => $this->timeout,
        ];

        // Ajouter les données selon la méthode
        if (in_array(strtoupper($method), ['POST', 'PUT', 'PATCH']) && !empty($data)) {
            $options['json'] = $data;
        } elseif (strtoupper($method) === 'GET' && !empty($data)) {
            $options['query'] = $data;
        }

        $attempt = 0;
        $lastException = null;
        $response = null;

       

        while ($attempt < $this->retries) {
            try {
                $response = $this->httpClient->request($method, $url, $options);

                $statusCode = $response->getStatusCode();
                $responseBody = $response->getBody()->getContents();
             
                $result = [
                    'success' => true,
                    'status_code' => $statusCode,
                    'data' => json_decode($responseBody, true) ?? $responseBody,
                    'headers' => $response->getHeaders()
                ];


                return $result;

            } catch (RequestException $e) {
                $lastException = $e;
                $attempt++;

                $statusCode = $e->getResponse() ? $e->getResponse()->getStatusCode() : 0;
                $responseBody = $e->getResponse() ? $e->getResponse()->getBody()->getContents() : '';

                // Ne pas réessayer pour certains codes d'erreur
                if (in_array($statusCode, [400, 401, 403, 404, 422])) {
                    break;
                }

                // Attendre avant de réessayer
                if ($attempt < $this->retries) {
                    sleep(pow(2, $attempt)); // Backoff exponentiel
                }

            } catch (GuzzleException $e) {
                $lastException = $e;
                $attempt++;
 

                if ($attempt < $this->retries) {
                    sleep(pow(2, $attempt));
                }
            }
        }
 
        // Retourner l'erreur après tous les essais
        $result = $this->handleError($lastException);

        return $result;
    }

    /**
     * Construire les headers de la requête
     */
    private function buildHeaders(array $permissions = []): array
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Request-ID' => $this->generateRequestId(),
            'X-Service-Name' => config('app.name', 'unknown'),
        ];

        // Ajouter le Bearer token si disponible
        if ($this->bearerToken) {
            $headers['Authorization'] = 'Bearer ' . $this->bearerToken;
        }

        // Ajouter les permissions personnalisées
        if (!empty($permissions)) {
            $headers['X-Permissions'] = json_encode($permissions);
        }

        return $headers;
    }

    /**
     * Générer un ID unique pour la requête
     */
    private function generateRequestId(): string
    {
        return uniqid('req_', true);
    }

    /**
     * Gérer les erreurs
     */
    private function handleError(?Exception $exception): array
    {
        if ($exception instanceof RequestException && $exception->getResponse()) {
            $statusCode = $exception->getResponse()->getStatusCode();
            $responseBody = $exception->getResponse()->getBody()->getContents();

            return [
                'success' => false,
                'status_code' => $statusCode,
                'error' => $exception->getMessage(),
                'data' => json_decode($responseBody, true) ?? $responseBody,
            ];
        }

        return [
            'success' => false,
            'status_code' => 500,
            'error' => $exception ? $exception->getMessage() : 'Unknown error occurred',
            'data' => null,
        ];
    }

}