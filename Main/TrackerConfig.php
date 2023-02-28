<?php

namespace Tracker\Main;

if (session_status() != PHP_SESSION_ACTIVE)
    @session_start();

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
    public $userHashId = "";
    public function __construct(DBConnection $dbConnection = null, SessionCookiesConfig $sessionCookiesConfig, UserInfo $userInfo = new UserInfo)
    {
        $this->userInfo = $userInfo;
        $this->dbConnection = $dbConnection;
        $this->trackingKey = $sessionCookiesConfig->trackingKey;
        $this->engagementKey = $sessionCookiesConfig->engagementKey;
        $this->sessionKey = $sessionCookiesConfig->sessionKey;
        $this->domain = $sessionCookiesConfig->domain;
        $this->userHashId = $sessionCookiesConfig->userHashId;
    }
    public function setRetentionCookie()
    {
        setcookie($this->trackingKey, $this->getDate(), strtotime("+1 week"), "/", $this->domain, false);
    }
    public function setEngagementSession($insertInDatabase = true)
    {
        $_SESSION[$this->engagementKey] = $this->getDate();

        if ($this->sessionKey && $insertInDatabase) {
            $info[0] = $this->getLogId();
            $this->dbConnection->insertEngagementLog($info);
        }
    }
    public function startTracker()
    {
        $this->resetTracker();
    }
    public function checkRetention()
    {
        $retation_date = new \DateTime(date_format(new \DateTime($_COOKIE[$this->trackingKey]), "Y/m/d"));
        $current_date = new \DateTime($this->getDate("Y/m/d"));
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
            if ($isSessionUpdated == false) {
                $this->setEngagementSession();
            }
        } else {
            $this->startTracker();
        }
    }

    public function generatLog()
    {
        $info = array();
        $info[0] = $this->getLogId();
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
        $info[0] = $this->getLogId();
        $this->dbConnection->insertRetentionLog($info);
    }
    public function updateEngagement()
    {
        $time_diff_second = $this->getTimeDiffrenceInSecond();
        $seconds_in_hours = 3600;
        // checking engagment time in second.
        if ($time_diff_second < $seconds_in_hours && $time_diff_second > 0) {
            $info[0] = $time_diff_second;
            $info[1] = (string)$this->getLogId();
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

    /**
     * getDate get current date with default date format
     *
     * @param  mixed $format format for specific format
     * @return string
     */
    public function getDate($format = "Y-m-d H:i:s"): string
    {
        $result = date($format);
        return $result;
    }

    /**
     * getLogId returns id which insert into a database Configure this function to store key in database
     * currently it is storeing session if userhashid is not set else it is storing sessiion id.
     *
     * @return void
     */
    private function getLogId()
    {
        if (empty($this->userHashId)) {
            $id = $this->isCookieSet($this->sessionKey) ? $_COOKIE[$this->sessionKey] : session_id();
        } else {
            $id = hash("md5", $this->userHashId);
        }
        return $id;
    }
}
