<?php

namespace Tracker\Helper;

class SessionCookiesConfig
{
    public $trackingKey  = "";
    public $engagementKey = "";
    public $sessionKey = ""; // Default Session Id
    public $domain = ""; //  domain name in .example.com 
    public $userHashId = "";

    public function __construct($trackingKey = "visitid", $engagementKey = "engagementid", $sessionKey = "PHPSESSID", $domain = "", $userHashId = "")
    {
        $this->trackingKey = $trackingKey;
        $this->engagementKey = $engagementKey;
        $this->sessionKey = $sessionKey;
        $this->$domain = $domain;
        $this->$userHashId = $userHashId;
    }
}
