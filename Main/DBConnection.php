<?php

namespace Tracker\Main;

use Tracker\Helper\DatabaseConfig;

class DBConnection
{
    private $connection;

    public function __construct(DatabaseConfig $database)
    {
        # Create connection
        $this->connection = $database->connect();
    }
    public function __destruct()
    {
        # "Connection Close";
        $this->connection->close();
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function insertVisitorLog($data)
    {
        $query  = "INSERT INTO `visitor_logs`(`log_id`, `page_url`, `referrer_url`, `user_ip_address`, `user_geo_location`, `user_agent`, `device`) VALUES (?,?,?,?,?,?,?)";
        $statement = $this->connection->prepare($query);
        $statement->bind_param("sssssss", $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6]);
        $statement->execute();
    }
    public function insertRetentionLog($data)
    {
        $query  = "INSERT INTO `retantion_logs`(`log_id`) VALUES (?)";
        $statement = $this->connection->prepare($query);
        $statement->bind_param("s", $data[0]);
        $statement->execute();
    }
    // check if session is alerady there then insert into todays engagement
    public function insertEngagementLog($data)
    {
        $query  = "SELECT * FROM `engagement_logs` WHERE `log_id`=? limit 1";
        $statement = $this->connection->prepare($query);
        $statement->bind_param("s", $data[0]);
        $statement->execute();

        $result = $statement->get_result(); // get the mysqli result
        if ($result->num_rows == 0) {
            $query  = "INSERT INTO `engagement_logs`(`log_id`) VALUES (?)";
            $statement = $this->connection->prepare($query);
            $statement->bind_param("s", $data[0]);
            $statement->execute();
        }
    }
    // update engagement log if 
    public function updateEngagementLog($data)
    {
        $query  = "UPDATE `engagement_logs` SET `engagement_time`=`engagement_time`+?, `last_visited_at` = now()  WHERE `log_id`=?";
        $statement = $this->connection->prepare($query);
        $statement->bind_param("is", $data[0], $data[1]);
        $statement->execute();

        $affectedrow = $statement->affected_rows;

        if ($affectedrow == 0) {
            $info[0] = $data[1];
            $this->insertEngagementLog($info);
        }
    }
}
