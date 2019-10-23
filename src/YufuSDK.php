<?php

use Yufu\SDK\InitException as InitException;

/**
 * Authentication SDK for Yufu.
 */
final class YufuSDK
{

    const CIDP_TOKEN_CONSUME_URL = 'https://portal.yufuid.com/api/v1/external/sso';
    const DURATION_IN_MS = 300000; // 5 mins

    private $utils;
    private $tenant;
    private $issuer;
    private $canAccessPortal;
    private $defaultLoggingParams;

    /**
     * YufuSDK constructor.
     * @param $tenant
     * @param null $issuer
     * @param null $privateKeyPath
     * @param null $publicKeyPath
     * @param null $keyServiceUrl
     * @param bool $canAccessPortal
     */
    public function __construct($tenant, $issuer = null, $privateKeyPath = null, $publicKeyPath = null, $canAccessPortal = false)
    {
        require_once 'exceptions/InitException.php';
        if (is_null($tenant)) {
            throw new InitException('Tenant can not be empty');
        }
        $this->tenant = $tenant;
        $this->defaultLoggingParams = 'tnt=' . $tenant . '&issuer=' . $issuer;
        $this->issuer = $issuer;
        require_once 'YufuSDKUtils.php';
        if (!is_null($privateKeyPath)) {
            $privateKey = file_get_contents($privateKeyPath);
        }
        $this->utils = new YufuSDKUtils($privateKey, $publicKeyPath, $issuer);
        $this->canAccessPortal = $canAccessPortal;
    }

    public function verify($token)
    {
        return $this->utils->verify($token);
    }

    private function generate($payload)
    {
        return $this->utils->generate($payload);
    }

    public function generateIDPUrl($payload)
    {
        $payload['aud'] = 'cidp';
        $payload['tnt'] = $this->tenant;
        $timestamp = time();
        $payload['iat'] = $timestamp;
        $payload['exp'] = $timestamp + self::DURATION_IN_MS;
        $payload['iss'] = $this->issuer;
        $params = '?idp_token=' . $this->generate($payload);
        if ($this->canAccessPortal) {
            $params += '&request_type=access_token';
        }
        return self::CIDP_TOKEN_CONSUME_URL . $params . '&' . $this->defaultLoggingParams;
    }
}
