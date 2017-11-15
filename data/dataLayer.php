<?php

    function getDatabaseConnection()
    {
        $servername = "localhost";
        $username = "root";
        $password = "root";
        $dbname = "Cover";

	   $conn = new mysqli($servername, $username, $password, $dbname);
        
        if ($conn->connect_error)
        {
            return null;
        }
        else
        {
            return $conn;
        }
    }

    function jsonAttemptLogin($email, $password)
    {
        $connection = getDatabaseConnection();
        $key = pack('H*', "bcb04b7e103a0cd8b54763051cef08bc55abe029fdebae5e1d417e2ffb2a00a3");
        if ($connection != null)
        {
            $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
            
            $sql = "SELECT *
                FROM Musician
                WHERE Email = '$email'";
            
            $result = $connection->query($sql);
            
            if ($result->num_rows > 0)
            {
                $cypher_password;
                while ($row = $result->fetch_assoc())
                {
                    $response = array("MusicianId"=>$row["MusicianId"],
                            "MESSAGE"=>"SUCCESS");
                    $cypher_password = $row["Password"];
                }
                
                $ciphertext_dec = base64_decode($cypher_password);
                $iv_dec = substr($ciphertext_dec, 0, $iv_size);
                $ciphertext_dec = substr($ciphertext_dec, $iv_size);
                $plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key,
                    $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);
                
                $plaintext_dec = clean($plaintext_dec);
                
                $connection->close();
                
                if ($password == $plaintext_dec)
                {
                    return $response;
                }
                else
                {
                    return array("MESSAGE" => "406");
                }
            }
            else
            {
                $connection->close();
                return array("MESSAGE" => "406");
            }
        }
        else
        {
            return array("MESSAGE" => "500");
        }
    }

    function jsonAttemptRegistration($firstName, $lastName, $email, $musicianName, $password, $country, $city)
    {
        $connection = getDatabaseConnection();
        $key = pack('H*', "bcb04b7e103a0cd8b54763051cef08bc55abe029fdebae5e1d417e2ffb2a00a3");
        if ($connection != null)
        {
            $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
            $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
            $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $password, MCRYPT_MODE_CBC, $iv);
            $ciphertext = $iv . $ciphertext;
            $password = base64_encode($ciphertext);
            
            $sql = "SELECT *
                FROM Musician
                WHERE Email = '$email';";
  
            $result = $connection->query($sql);

            if ($result->num_rows == 0)
            {            
                $sql = "INSERT INTO Musician (MusicianId, FirstName, LastName, Email, MusicianName, Password, Country, City, RegDate)
                    VALUES (NULL, '$firstName', '$lastName', '$email', '$musicianName', '$password', '$country', '$city', DEFAULT);";
                $connection->query($sql);

                $sql = "SELECT *
                    FROM Musician
                    WHERE MusicianName = '$musicianName';"; 
                
                $result = $connection->query($sql);
                $response;
                while ($row = $result->fetch_assoc())
                {
                    $response = array("MusicianId"=>$row["MusicianId"], "MESSAGE" => "SUCCESS");
                }
                
                $connection->close();
                return $response;
            }
            else
            {
                $connection->close();
                return array("MESSAGE" => "409");
            }
        }
        else
        {
            return array("MESSAGE" => "500");
        }
    }

    function jsonAttemptSubmitNewPerformance($musicianId, $place, $location, $datetime)
    {
        $connection = getDatabaseConnection();
        
        if ($connection != null)
        {
            $sql = "SELECT
                        *
                    FROM
                        RecentActivity
                    WHERE
                        MusicianId = $musicianId;";
            
            $result = $connection->query($sql);
            
            if ($result->num_rows > 0)
            {
                $sql = "INSERT INTO
                            Performance (PerformanceId, MusicianId, Place, Location, DateAndTime)
                        VALUES
                            (NULL, $musicianId, '$place', '$location', '$datetime');";
                
                $connection->query($sql);
                $lastId = $connection->insert_id;
                $sql = "UPDATE
                            RecentActivity
                        SET
                            Type = 'Performance', ActivityId = $lastId, FileName = NULL
                        WHERE
                            MusicianId = $musicianId;";
            }
            else
            {
                $sql = "INSERT INTO
                            Performance (PerformanceId, MusicianId, Place, Location, DateAndTime)
                        VALUES
                            (NULL, $musicianId, '$place', '$location', '$datetime');";
                
                $connection->query($sql);
                $lastId = $connection->insert_id;
                $sql = "INSERT INTO
                            RecentActivity (RecentActivityId, MusicianId, Type, ActivityId, FileName)
                        VALUES (NULL, $musicianId, 'Performance', $lastId, NULL);";
            }
            
            $result = $connection->query($sql);
            
            if ($result)
            {
                $response = array("MESSAGE" => "SUCCESS");
                $connection->close();
                return $response;
            }
            else
            {
                $connection->close();
                return array("MESSAGE" => "406");
            }
        }
        else
        {
            return array("MESSAGE" => "500");
        }
    }

    function jsonAttemptGetInfoById($MusicianId)
    {
        $connection = getDatabaseConnection();
        
        if ($connection != null)
        {
            $sql = "SELECT
                        *
                    FROM
                        Musician
                    WHERE
                        MusicianId = $MusicianId;";
            
            $result = $connection->query($sql);
            
            $response;
            if ($result->num_rows > 0)
            {
                while ($row = $result->fetch_assoc())
                {
                    $response = array("musicianName" => $row["MusicianName"],
                                     "MESSAGE" => "SUCCESS");
                }
            }
            else
            {
                $response = array("MESSAGE" => "406");
            }
            
            $connection->close();
            return $response;
        }
        else
        {
            return array("MESSAGE" => "500");
        }
    }

    function jsonAttemptGetPerformances($musicianId)
    {
        $connection = getDatabaseConnection();
        
        if ($connection != null)
        {
            $sql = "SELECT
                        *
                    FROM
                        Performance
                    WHERE
                        MusicianId = '$musicianId';";
            
            $result = $connection->query($sql);
            
            if ($result)
            {
                $response = array();
                if ($result->num_rows > 0)
                {
                    while ($row = $result->fetch_assoc())
                    {
                        $response[] = array("place" => $row["Place"],
                                           "location" => $row["Location"],
                                           "dateTime" => $row["DateAndTime"]);
                    }
                }
                
                $connection->close();
                return array("response" => $response,
                            "MESSAGE" => "SUCCESS");
            }
            else
            {
                $connection->close();
                return array("MESSAGE" => "406");
            }
        }
        else
        {
            return array("MESSAGE" => "500");
        }
    }

    function jsonAttemptUploadImage($musicianId, $fileName)
    {
        $connection = getDatabaseConnection();
        
        if ($connection != null)
        {
            $sql = "SELECT
                        *
                    FROM
                        RecentActivity
                    WHERE
                        MusicianId = $musicianId;";
            
            $result = $connection->query($sql);
            
            if ($result->num_rows > 0)
            {
                $sql = "UPDATE
                            RecentActivity
                        SET
                            Type = 'Image', FileName = '$fileName'
                        WHERE
                            MusicianId = $musicianId;";
            }
            else
            {
                $sql = "INSERT INTO
                            RecentActivity (RecentActivityId, MusicianId, Type, ActivityId, FileName)
                        VALUES (NULL, $musicianId, 'Image', NULL, '$fileName');";
            }

            $result = $connection->query($sql);
            
            if ($result)
            {
                $connection->close();
                return array("MESSAGE" => "SUCCESS");
            }
            else
            {
                $connection->close();
                return array("MESSAGE" => "406");
            }
        }
        else
        {
            return array("MESSAGE" => "500");
        }
    }

    function jsonAttemptUploadTrack($musicianId, $fileName)
    {
        $connection = getDatabaseConnection();
        
        if ($connection != null)
        {
            $sql = "SELECT
                        *
                    FROM
                        RecentActivity
                    WHERE
                        MusicianId = $musicianId;";
            
            $result = $connection->query($sql);
            
            if ($result->num_rows > 0)
            {
                $sql = "UPDATE
                            RecentActivity
                        SET
                            Type = 'Track', FileName = '$fileName'
                        WHERE
                            MusicianId = $musicianId;";
            }
            else
            {
                $sql = "INSERT INTO
                            RecentActivity (RecentActivityId, MusicianId, Type, ActivityId, FileName)
                        VALUES (NULL, $musicianId, 'Track', NULL, '$fileName');";
            }

            $result = $connection->query($sql);
            
            if ($result)
            {
                $connection->close();
                return array("MESSAGE" => "SUCCESS");
            }
            else
            {
                $connection->close();
                return array("MESSAGE" => "406");
            }
        }
        else
        {
            return array("MESSAGE" => "500");
        }
    }

    function jsonAttemptSearch($MusicianId, $searchContent)
    {
        $connection = getDatabaseConnection();
        
        if ($connection != null)
        {
            $sql = "SELECT *
                    FROM Musician
                    WHERE
                        (MusicianName like '%$searchContent%' OR Email like '%$searchContent%') AND
                        Musician.MusicianId not in (SELECT MusicianReceivedId from Connection WHERE Connection.MusicianSentId = $MusicianId) AND
                        Musician.MusicianId not in (SELECT MusicianSentId from Connection WHERE Connection.MusicianReceivedId = $MusicianId) AND
                        Musician.MusicianId != $MusicianId;";
        
            $result = $connection->query($sql);
            
            $response = array();
            
            if ($result)
            {
                if ($result->num_rows > 0)
                {
                    while ($row = $result->fetch_assoc())
                    {
                        $response[] = array("musicianName"=>$row["MusicianName"],
                                          "country"=>$row["Country"],
                                          "city"=>$row["City"],
                                           "email" => $row["Email"]);
                    }
                }
            }
            
            $connection->close();
            $res = array("response" => $response,
                        "MESSAGE" => "SUCCESS");
            return $res;
        }
        else
        {
            return array("MESSAGE" => "500");
        }
    }

    function jsonAttemptConnectRequest($MusicianId, $MusicianToAdd)
    {
        $connection = getDatabaseConnection();
            
        if ($connection != null)
        {
            $sql = "SELECT MusicianId
                    FROM Musician
                    WHERE Email = '$MusicianToAdd';";
            
            $result = $connection->query($sql);
            
            if ($result->num_rows > 0)
            {  
                while($row = $result->fetch_assoc())
                {
                    $MusicianIdToAdd = $row["MusicianId"];
                }
                
                $sql = "INSERT INTO
                            `Connection` (ConnectionId, MusicianSentId, MusicianReceivedId, ConnectionStatus, SentDate, AcceptedDate)
                        VALUES (NULL, $MusicianId, $MusicianIdToAdd, 'SENT', DEFAULT, NULL);";

                $result = $connection->query($sql);

                if ($result)
                {
                    $connection->close();
                    return array("MESSAGE" => "SUCCESS");
                }
                else
                {
                    $connection->close();
                    return array("MESSAGE" => "505");
                }
            }
            else
            {
                $connection->close();
                return array("MESSAGE" => "406");
            }
        }
        else
        {
            return array("MESSAGE" => "500");
        }
    }

    function jsonAttemptGetSentRequests($MusicianId)
    {
        $connection = getDatabaseConnection();
        
        if ($connection != null)
        {
            $sql = "SELECT 
                        *
                    FROM 
                        `Connection`
                    JOIN 
                        Musician ON MusicianReceivedId = Musician.MusicianId
                    WHERE 
                        MusicianSentId = '$MusicianId' AND
                        ConnectionStatus = 'SENT';";
            
            $result = $connection->query($sql);
            
            $response = array();
        
            if ($result->num_rows > 0)
            {
                while ($row = $result->fetch_assoc())
                {
                    $response[] = array("musicianName"=>$row["MusicianName"],
                                       "country" => $row["Country"],
                                       "city" => $row["City"],
                                       "email" => $row["Email"]);
                }
            }
            
            $connection->close();
            $res = array("response" => $response,
                        "MESSAGE" => "SUCCESS");
            return $res;
        }
        else
        {
            return array("MESSAGE" => "500");
        }
    }

    function jsonAttemptGetReceivedRequests($MusicianId)
    {
        $connection = getDatabaseConnection();
        
        if ($connection != null)
        {
            $sql = "SELECT 
                        *
                    FROM 
                        `Connection`
                    JOIN 
                        Musician ON MusicianSentId = Musician.MusicianId
                    WHERE 
                        MusicianReceivedId = '$MusicianId' AND
                        ConnectionStatus = 'SENT';";
            
            $result = $connection->query($sql);
            
            $response = array();
            
            if ($result->num_rows > 0)
            {
                while ($row = $result->fetch_assoc())
                {
                    $response[] = array("musicianName"=>$row["MusicianName"],
                                       "country" => $row["Country"],
                                       "city" => $row["City"],
                                       "email" => $row["Email"]);
                }
            }
            
            $connection->close();
            $res = array("response" => $response,
                        "MESSAGE" => "SUCCESS");
            return $res;
        }
        else
        {
            return array("MESSAGE" => "500");
        }
    }

    function jsonAttemptGetConnections($MusicianId)
    {
        $connection = getDatabaseConnection();
        
        if ($connection != null)
        {
            $sql = "SELECT 
                        *
                    FROM
                        Connection
                    JOIN 
                        Musician ON (Musician.MusicianId = Connection.MusicianSentId OR Musician.MusicianId = Connection.MusicianReceivedId) AND Musician.MusicianId <> $MusicianId
                    WHERE 
                        (MusicianSentId = $MusicianId OR MusicianReceivedId = $MusicianId) AND 
                        ConnectionStatus = 'Accepted';";
            
            $result = $connection->query($sql);
            $response = array();
            if ($result->num_rows > 0)
            {
                while ($row = $result->fetch_assoc())
                {
                    $connectionId;
                    if ($row["MusicianSentId"] == $MusicianId)
                    {
                        $connectionId = $row["MusicianReceivedId"];
                    }
                    else
                    {
                        $connectionId = $row["MusicianSentId"];
                    }
                    
                    $response[] = array("musicianName"=>$row["MusicianName"],
                                       "country" => $row["Country"],
                                       "city" => $row["City"],
                                       "email" => $row["Email"],
                                       "musicianId" => $connectionId);
                }
            }
            
            $connection->close();
            $res = array("response" => $response,
                        "MESSAGE" => "SUCCESS");
            return $res;
        }
        else
        {
            return array("MESSAGE" => "500");
        }
    }

    function jsonAttemptAcceptRequest($MusicianId, $MusicianToAccept)
    {
        $connection = getDatabaseConnection();
        
        if ($connection != null)
        {
            $sql = "SELECT 
                        MusicianId
                    FROM
                        Musician
                    WHERE 
                        Email = '$MusicianToAccept';";
            
            $result = $connection->query($sql);
            
            if ($result->num_rows > 0)
            {  
                while($row = $result->fetch_assoc())
                {
                    $MusicianIdToAccept = $row["MusicianId"];
                }
                
                $sql = "UPDATE 
                            Connection
                        SET 
                            ConnectionStatus='Accepted'
                        WHERE 
                            MusicianSentId='$MusicianIdToAccept' AND
                            MusicianReceivedId='$MusicianId';";
            
                $result = $connection->query($sql);
                if ($result)
                {
                    $connection->close();
                    $res = array("MESSAGE" => "SUCCESS");
                    return $res;
                }
                else
                {
                    $connection->close();
                    return array("MESSAGE" => "406");
                }
            }
            else
            {
                $connection->close();
                return array("MESSAGE" => "406");
            }
        }
        else
        {
            return array("MESSAGE" => "500");
        }
    }

    function jsonAttemptRejectRequest($MusicianId, $MusicianToReject)
    {
        $connection = databaseConnection();
        
        if ($connection != null)
        {
            $sql = "SELECT 
                        MusicianId
                    FROM
                        Musician
                    WHERE
                        Email = '$MusicianToReject';";
            
            $result = $connection->query($sql);
            
            if ($result->num_rows > 0)
            {  
                while($row = $result->fetch_assoc())
                {
                    $MusicianIdToReject = $row["MusicianId"];
                }
                
                $sql = "UPDATE 
                            Connection
                        SET 
                            ConnectionStatus='Rejected'
                        WHERE 
                            MusicianSentId='$MusicianIdToReject' AND
                            MusicianReceivedId='$MusicianId';";
            
                $result = $connection->query($sql);

                if ($result)
                {
                    $connection->close();
                    $res = array("MESSAGE" => "SUCCESS");
                    return $res;
                }
                else
                {
                    $connection->close();
                    return array("MESSAGE" => "406");
                }
            }
            else
            {
                $connection->close();
                return array("MESSAGE" => "406");
            }
        }
        else
        {
            return array("MESSAGE" => "500");
        }
    }

    function jsonAttemptGetRecentActivity($MusicianId)
    {
        $connection = getDatabaseConnection();
        
        if ($connection != null)
        {
            $sql = "SELECT 
                        *
                    FROM
                        RecentActivity
                    WHERE 
                        MusicianId = $MusicianId;";
            
            $result = $connection->query($sql);
            
            if ($result->num_rows > 0)
            {
                $type;
                $activityId;
                $fileName;
                while ($row = $result->fetch_assoc())
                {
                    $type = $row["Type"];
                    $activityId = $row["ActivityId"];
                    $fileName = $row["FileName"];
                }
            }

            if ($type == "Performance")
            {
                $sql = "SELECT
                            *
                        FROM
                            Performance
                        WHERE
                            PerformanceId = $activityId;";
                
                $result = $connection->query($sql);
                
                while ($row = $result->fetch_assoc())
                {
                    $response = array("place" => $row["Place"],
                                       "location" => $row["Location"],
                                       "dateTime" => $row["DateAndTime"],
                                       "type" => $type);
                }
            }
            else if ($type == "Image")
            {
                $directory = "C:/Users/Huvok/Documents/GitHub/Cover/uploads/images/";
                $images = glob($directory . $MusicianId . "/" . $fileName);

                foreach($images as $image)
                {
                  $response = array("image" => base64_encode(file_get_contents($image)),
                                    "type" => $type);
                }
            }
            else if ($type == "Track")
            {
                $directory = "C:/Users/Huvok/Documents/GitHub/Cover/uploads/audio/";
                $audio = glob($directory . $MusicianId . "/" . $fileName);

                foreach($audio as $track)
                {
                  $response = array("track" => base64_encode(file_get_contents($track)),
                                    "type" => $type);
                }
            }
            
            $connection->close();
            $res = array("response" => $response,
                        "MESSAGE" => "SUCCESS");
            return $res;
        }
        else
        {
            return array("MESSAGE" => "500");
        }
    }

    function jsonAttemptGetIdByEmail($Email)
    {
        $connection = getDatabaseConnection();
        
        if ($connection != null)
        {
            $sql = "SELECT
                        *
                    FROM
                        Musician
                    WHERE
                        Email = '$Email';";
            
            $result = $connection->query($sql);
            
            if ($result)
            {
                if ($result->num_rows > 0)
                {
                    $response;
                    while ($row = $result->fetch_assoc())
                    {
                        $response = array("musicianId" => $row["MusicianId"],
                                         "MESSAGE" => "SUCCESS");
                    }
                    
                    $connection->close();
                    return $response;
                }
                else
                {
                    $connection->close();
                    return array("MESSAGE" => "406");
                }
            }
        }
        else
        {
            return array("MESSAGE" => "500");
        }
    }

    function clean($string) 
    {
        return preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '', $string);
    }
?>