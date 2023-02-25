<?php
@session_start();
date_default_timezone_set("Asia/Calcutta");

include './Userinfo.php';
include './connection.php';

class IdConfig
{
    public $trackingKey  = "visitid";
    public $engagementKey = "engid";
    public $sessionKey = "PHPSESSID";
    public $domain = ""; // mention your domain name in .example.com 
    public function __construct($trackingKey = "visitid", $engagementKey = "engid", $sessionKey = "PHPSESSID", $domain = "")
    {
        $this->trackingKey = $trackingKey;
        $this->engagementKey = $engagementKey;
        $this->sessionKey = $sessionKey;
        $this->$domain = $domain;
    }
}
class Tracker
{

    public $userInfo = "";
    public $conn = "";
    public $trackingKey  = "";
    public $engagementKey = "";
    public $sessionKey = "";
    public $domain = ""; // mention your domain name in .example.com 
    public function __construct(UserInfo $userInfo = null, ConnectionLog $conn = null, IdConfig $idConfig)
    {
        $this->userInfo = $userInfo;
        $this->conn = $conn;
        $this->trackingKey = $idConfig->trackingKey;
        $this->engagementKey = $idConfig->engagementKey;
        $this->sessionKey = $idConfig->sessionKey;
        $this->domain = $idConfig->domain;
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
            $this->conn->insertEngagementLog($info);
        }
    }
    public function startTracker()
    {
        $this->resetTracker();
    }
    public function checkRetention()
    {
        $retation_date = new DateTime($_COOKIE[$this->trackingKey]);
        $current_date = new DateTime($this->getDate());

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
        $engagement_date = new DateTime($_SESSION[$this->engagementKey]);
        $current_date = new DateTime($this->getDate());
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
            if ($this->isSessionSet($this->engagementKey)) {
                $this->updateEngagement();
                $this->setEngagementSession(false);
            }
            // Update Retention if Tracking Cookies date is not todays.
            if ($this->checkRetention() != 0) {
                if ($this->checkRetention() > 0) {
                    $this->updateRetentionLog();
                }
                $this->resetTracker();
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

        $this->conn->insertVisitorLog($info);
    }
    public function updateRetentionLog()
    {
        $info = array();
        $info[0] = $this->isCookieSet($this->sessionKey) ? $_COOKIE[$this->sessionKey] : "000";
        $this->conn->insertRetentionLog($info);
    }
    public function updateEngagement()
    {
        $time_diff_second = $this->getTimeDiffrenceInSecond();
        $seconds_in_hours = 3600;
        // checking engagment time in second.
        if ($time_diff_second < $seconds_in_hours && $time_diff_second > 0) {
            $info[0] = $time_diff_second;
            $info[1] = (string)$this->isCookieSet($this->sessionKey) ? $_COOKIE[$this->sessionKey] : "000";
            $this->conn->updateEngagementLog($info);
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


$database = new Database("localhost", "root", "", "student_database");
$obj = new Tracker(new UserInfo(), new ConnectionLog($database), new IdConfig());
$obj->track();
