<?php

namespace Tracker\Main;

use Tracker\Helper\UserInfo;
use Tracker\Helper\SessionCookiesConfig;

use Tracker\Main\DBConnection;


class TrackerConfig
{

    public $userInfo = "";
    public $dbConnection = "";
    public $trackingKey  = "";
    public $engagementKey = "";
    public $sessionKey = "";
    public $domain = ""; // mention your domain name in .example.com 
    public function __construct(DBConnection $dbConnection = null, SessionCookiesConfig $sessionCookiesConfig = new SessionCookiesConfig(), UserInfo $userInfo = new UserInfo)
    {
        $this->userInfo = $userInfo;
        $this->dbConnection = $dbConnection;
        $this->trackingKey = $sessionCookiesConfig->trackingKey;
        $this->engagementKey = $sessionCookiesConfig->engagementKey;
        $this->sessionKey = $sessionCookiesConfig->sessionKey;
        $this->domain = $sessionCookiesConfig->domain;
    }
    public function setRetentionCookie()
    {
        setcookie($this->trackingKey, $this->getDate(), strtotime("+1 week"), "/", $this->domain, false);
    }
    public function setEngagementSession($insertInDatabase = true)
    {
        $_SESSION[$this->engagementKey] = $this->getDate();

        if ($this->sessionKey && $insertInDatabase) {
            $info[0] = session_id();
            $this->dbConnection->insertEngagementLog($info);
        }
    }
    public function startTracker()
    {
        $this->resetTracker();
    }
    public function checkRetention()
    {
        $retation_date = new \DateTime($_COOKIE[$this->trackingKey]);
        $current_date = new \DateTime($this->getDate());

        $diff = date_diff($retation_date, $current_date)->format("%r%a");
        return $diff;
    }
    public function resetTracker()
    {
        $this->setRetentionCookie();
        $this->setEngagementSession();
    }
    public function getTimeDiffrenceInSecond()
    {
        $engagement_date = new \DateTime($_SESSION[$this->engagementKey]);
        $current_date = new \DateTime($this->getDate());
        $time_diff_minutes = date_diff($engagement_date, $current_date)->i;
        $time_diff_second = $time_diff_minutes * 60 + date_diff($engagement_date, $current_date)->s;

        return $time_diff_second;
    }
    public function track()
    {
        $this->generatLog();

        // checking tracking cookie  set or not
        if ($this->isCookieSet($this->trackingKey)) {
            // update engagment if session is set
            $isSessionUpdated = false;
            if ($this->isSessionSet($this->engagementKey)) {
                $this->updateEngagement();
                $this->setEngagementSession(false);
                $isSessionUpdated = true;
            }
            // Update Retention if Tracking Cookies date is not todays.
            if ($this->checkRetention() != 0) {
                if ($this->checkRetention() > 0) {
                    $this->updateRetentionLog();
                }
                $this->resetTracker();
                $isSessionUpdated = true;
            }
            if ($isSessionUpdated = false) {
                $this->setEngagementSession();
            }
        } else {
            $this->startTracker();
        }
    }

    public function generatLog()
    {
        $info = array();
        $info[0] = $this->isCookieSet($this->sessionKey) ? $_COOKIE[$this->sessionKey] : "000";
        $info[] = $this->userInfo->getCurrentURL();
        $info[] = $this->userInfo->getRefererURL();
        $info[] =  $this->userInfo->getIP();
        $info[] = $this->userInfo->getRegionName() . "/" . $this->userInfo->getCity() . "/" . $this->userInfo->getCountryName();
        $info[] = $this->userInfo->getBrowser();
        $info[] = $this->userInfo->getDevice();

        $this->dbConnection->insertVisitorLog($info);
    }
    public function updateRetentionLog()
    {
        $info = array();
        $info[0] = $this->isCookieSet($this->sessionKey) ? $_COOKIE[$this->sessionKey] : session_id();
        $this->dbConnection->insertRetentionLog($info);
    }
    public function updateEngagement()
    {
        $time_diff_second = $this->getTimeDiffrenceInSecond();
        $seconds_in_hours = 3600;
        // checking engagment time in second.
        if ($time_diff_second < $seconds_in_hours && $time_diff_second > 0) {
            $info[0] = $time_diff_second;
            $info[1] = (string)$this->isCookieSet($this->sessionKey) ? $_COOKIE[$this->sessionKey] : "000";
            $this->dbConnection->updateEngagementLog($info);
        }
    }
    public function isCookieSet($key = null): bool
    {
        return !empty($_COOKIE[$key]);
    }
    public function isSessionSet($key = null): bool
    {
        return !empty($_SESSION[$key]);
    }
    public function getDate(): string
    {
        $result = date("Y-m-d H:i:s");
        return $result;
    }
}
