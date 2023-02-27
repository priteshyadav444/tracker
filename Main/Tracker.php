<?php

namespace Tracker\Main;

require_once __DIR__ . '/../vendor/autoload.php';

use Tracker\Helper\DatabaseConfig;
use Tracker\Helper\SessionCookiesConfig;
use Tracker\Helper\UserInfo;

use Tracker\Main\DBConnection;

date_default_timezone_set("Asia/Calcutta");

class Tracker
{
    private $tracker = "";

    public function __construct(DbConnection $dbConnection, SessionCookiesConfig $sessionCookiesConfig = new SessionCookiesConfig(), UserInfo $userInfo = new UserInfo())
    {
        $this->tracker = new TrackerConfig($dbConnection, $sessionCookiesConfig, $userInfo);
    }
    public function start()
    {
        $this->tracker->track();
    }
}
$servername = "localhost";
$username = "root";
$password = "";
$database = "student_database";

$databaseConfig = new DatabaseConfig($servername, $username, $password, $database);
$tracker = new Tracker(new DBConnection($databaseConfig));
$tracker->start();
