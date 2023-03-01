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

    public function __construct(DBConnection $dbConnection, SessionCookiesConfig $sessionCookiesConfig)
    {
        $this->tracker = new TrackerConfig($dbConnection, $sessionCookiesConfig, new UserInfo());
    }
    public function start()
    {
        $this->tracker->track();
    }
}
$servername = getenv('servername') ?? "localhost";
$username = getenv('username') ?? "";
$password = getenv('password') ?? "";
$database = getenv('database') ?? "";

$userHashId = $_SERVER['userHashId'] ?? "123";

$sessionCookiesConfig = new SessionCookiesConfig("visitiId", "engagametId", "PHPSESSID", "localhost", $userHashId);
$databaseConfig = new DatabaseConfig($servername, $username, $password, $database);

// if only environment variable set for sername than only create Tracker Object.
if ($servername != "" && $username != "" && $database != "") {
    $tracker = new Tracker(new DBConnection($databaseConfig), $sessionCookiesConfig);
}
