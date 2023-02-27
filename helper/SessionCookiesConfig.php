<?php

namespace Tracker\Helper;

class SessionCookiesConfig
{
    public $trackingKey  = "visitid";
    public $engagementKey = "engid";
    public $sessionKey = "PHPSESSID"; // Default Session Id
    public $domain = ""; //  domain name in .example.com 
    public function __construct($trackingKey = "visitid", $engagementKey = "engid", $sessionKey = "PHPSESSID", $domain = "")
    {
        $this->trackingKey = $trackingKey;
        $this->engagementKey = $engagementKey;
        $this->sessionKey = $sessionKey;
        $this->$domain = $domain;
    }
}
