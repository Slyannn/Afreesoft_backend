<?php
declare(strict_types=1);

namespace App\Service;

use DateTimeImmutable;

class JwtService
{
    //genere le Json Web Token (validation 3h)
    public function generate(array $header, array $payload, string $secret, int $validity = 10800): string
    {
        if($validity > 0) {
            $now = new DateTimeImmutable();
            $exp = $now->getTimestamp() + $validity;

            $payload['iat'] = $now->getTimestamp();
            $payload['exp'] = $exp;
        }

        //encode en base64
        $base64Header = base64_encode(json_encode($header));
        $base64Payload= base64_encode(json_encode($payload));

        //On 'nettoie' les valeurs encodés (retrait des +, / et =
        $base64Header = str_replace(['+','/','='], ['-', '_', ''], $base64Header);
        $base64Payload= str_replace(['+','/','='], ['-', '_', ''], $base64Payload);

        //generer la signature
        $secret = base64_encode($secret);
        $signature = hash_hmac(
            'sha256',
            $base64Header . '.' . $base64Payload,
            $secret, true
        );

        $base64signature = base64_encode($signature);
        $base64signature= str_replace(['+','/','='], ['-', '_', ''], $base64signature);

        return $base64Header. '.' .$base64Payload. '.' .$base64signature;
    }


    //check if token Valide
    public function isValid(string $token): bool
    {
        return preg_match(
                '/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/',
                $token
            ) === 1;
    }

    //recupere le payload
    public function getPayload(string $token): array
    {
        // On démonte le token
        $array = explode('.', $token);

        // On décode le Payload
        $payload = json_decode(base64_decode($array[1]), true);

        return $payload;
    }

    //recupere le Header
    public function getHeader(string $token): array
    {
        // On démonte le token
        $array = explode('.', $token);

        // On décode le Header
        $header = json_decode(base64_decode($array[0]), true);

        return $header;
    }

    //verifie si la date a expiré
    public function isExpired(string $token): bool
    {
        $payload = $this->getPayload($token);

        $now = new DateTimeImmutable();

        return $payload['exp'] < $now->getTimestamp();
    }


    //verifie la signature du token
    public function checkToken(string $token, string $secret): bool
    {
        // On récupère le header et le payload
        $header = $this->getHeader($token);
        $payload = $this->getPayload($token);

        // On régénère un token
        $verifToken = $this->generate($header, $payload, $secret, 0);

        return $token === $verifToken;
    }



}