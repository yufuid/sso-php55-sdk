<?php

use Yufu\SDK\CannotRetrieveKeyException as CannotRetrieveKeyException;
use Yufu\SDK\InvalidFormatException as InvalidFormatException;
use Yufu\SDK\JWT as JWT;

final class YufuSDKUtils
{
    private $kid;
    private $publicKeyPath;
    private $privateKey;
    private $publicKey;
    private $issuer;

    public function __construct($privateKey, $publicKeyPath, $issuer)
    {
        $this->privateKey = $privateKey;
        $this->publicKeyPath = $publicKeyPath;
        $this->issuer = $issuer;
        require_once 'JWT.php';
        require_once 'exceptions/ExpiredException.php';
        require_once 'exceptions/TokenTooEarlyException.php';
        require_once 'exceptions/SignatureInvalidException.php';
        require_once 'exceptions/InvalidFormatException.php';
        require_once 'exceptions/CannotRetrieveKeyException.php';
    }

    /**
     * @return object The JWT's payload as a PHP object
     */
    public function verify($token)
    {
        $segments = explode('.', $token);
        if (count($segments) != 3) {
            throw new InvalidFormatException('Wrong length of segments: ' . count($segments));
        }
        list($headb64, $_, $_) = $segments;
        if (
            null === ($header = JWT::jsonDecode(JWT::urlsafeB64Decode($headb64))) ||
            empty($header->kid)
        ) {
            throw new InvalidFormatException('Invalid header encoding or no kid in header');
        }
        return JWT::decode($token, $this->getPublicKey($header->kid), array('RS256'));
    }

    /**
     * @param $kid
     * @return mixed|string
     */
    private function getPublicKey($kid)
    {

        if ($kid !== $this->kid) {
            // Refetch key
            if (null == $this->publicKeyPath) {
                throw new CannotRetrieveKeyException('No publicKeyPath found');
            }
            $key = file_get_contents($this->publicKeyPath);
            if ($kid == self::getKeyId($key)) {
                return $key;
            }
            if (null != $key) {
                $this->publicKey = self::normalizePublicKey($key);
                $this->kid = $kid;
            } else {
                throw new CannotRetrieveKeyException('No key return from server');
            }
        }
        return $this->publicKey;
    }

    private static function normalizePublicKey($publicKey)
    {
        $publicKey = str_replace('-----BEGIN PUBLIC KEY-----', '', $publicKey);
        $publicKey = trim(str_replace('-----END PUBLIC KEY-----', '', $publicKey));
        $publicKey = str_replace(PHP_EOL, '', $publicKey);
        $publicKey = str_replace(' ', '', $publicKey);
        $publicKey = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($publicKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
        return $publicKey;
    }


    private static function getKeyId($publicKey)
    {
        $publicKey = preg_replace('~-----(.*)-----(\r\n?|\n|)([\\s\\S]*)(\r\n?|\n|)-----(.*)-----~', '$3', $publicKey);
        $publicKey = trim(str_replace(' ', '', $publicKey));
        $publicKey = sha1($publicKey, false);
        return $publicKey;
    }

    /**
     * @param $payload
     * @return string based on payload
     */
    public function generate($payload)
    {
        return JWT::encode($payload, $this->privateKey, 'RS256', $this->issuer);
    }
}
