<?php
    header('Content-type: application/json');
    header('Accept: application/json');
    require_once __DIR__ . '/dataLayer.php';

    $action = $_POST["action"];

    switch($action)
    {
        case "LOGIN" : 
            subLoginFunction();
            break;
        case "REGISTRATION" : 
            subRegistrationFunction();
            break;
        case "GET_SESSION" : 
            subGetSessionFunction();
            break;
        case "GET_COOKIES" : 
            subGetCookiesFunction();
            break;
        case "DELETE_SESSION" :
            subDeleteSessionFunction();
            break;
        case "GET_MUSICIAN_INFO" :
            subGetMusicianInfoFunction();
            break;
        case "GET_INFO_BY_ID" :
            subGetInfoByIdFunction();
            break;
        case "SUBMIT_NEW_PERFORMANCE" :
            subSubmitNewPerformanceFunction();
            break;
        case "GET_OWN_PERFORMANCES" :
            subGetOwnPerformances();
            break;
        case "GET_PERFORMANCES_BY_ID" :
            subGetPerformances();
            break;
        case "UPLOAD_IMAGE" :
            subUploadImageFunction();
            break;
        case "UPLOAD_TRACK" :
            subUploadTrackFunction();
            break;
        case "SEARCH" :
            subSearchFunction();
            break;
        case "CONNECT_REQUEST" :
            subConnectRequestFunction();
            break;
        case "GET_SENT_REQUESTS" :
            subGetSentRequestsFunction();
            break;
        case "GET_RECEIVED_REQUESTS" :
            subGetReceivedRequestsFunction();
            break;
        case "GET_CONNECTIONS" :
            subGetConnectionsFunction();
            break;
        case "ACCEPT_REQUEST" :
            subAcceptRequestFunction();
            break;
        case "REJECT_REQUEST" :
            subRejectRequestFunction();
            break;
        case "GET_RECENT_ACTIVITY" :
            subGetrecentActivityFunction();
            break;
        case "GET_ID_BY_EMAIL" :
            subGetIdByEmailFunction();
            break;
        case "GET_OWN_IMAGES" :
            subGetOwnImages();
            break;
        case "GET_OWN_TRACKS" :
            subGetOwnTracks();
            break;
        case "GET_IMAGES_BY_ID" :
            subGetImages();
            break;
        case "GET_TRACKS_BY_ID" :
            subGetTracks();
            break;
        case "UPLOAD_COVER_PICTURE" :
            subUploadCoverPicture();
            break;
        case "GET_COVER_PICTURE" :
            subGetCoverPicture();
            break;
        case "GET_COVER_PICTURE_BY_ID" :
            subGetCoverPictureById();
            break;
    }

    function subLoginFunction()
    {
        $email = $_POST["email"];
        $password = $_POST["password"];
        $rememberMe = $_POST["rememberMe"];
        
        $loginResponse = jsonAttemptLogin($email, $password, $rememberMe);
        
        if ($loginResponse["MESSAGE"] == "SUCCESS")
        {
            if ($rememberMe == "true")
            {
                setcookie("Email", $email, time() + (86400 * 30));
                setcookie("MusicianId", $loginResponse["MusicianId"], time() + (86400 * 30));
            }
            
            session_start();
            if (!isset($_SESSION['MusicianId']))
            {
                $_SESSION['MusicianId'] = $loginResponse['MusicianId'];
            }
            
            $response = array("MESSAGE"=>"SUCCESS");
            
            echo json_encode($response);
        }
        else
        {
            subGetErrorByCode($loginResponse["MESSAGE"]);
        }
    }

    function subRegistrationFunction()
    {
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        $email = $_POST['email'];
        $musicianName = $_POST['musicianName'];
        $password = $_POST['password'];
        $country = $_POST['country'];
        $city = $_POST['city'];
        
        $registrationResponse = jsonAttemptRegistration($firstName, $lastName, $email, $musicianName, $password, $country, $city);
        
        if ($registrationResponse["MESSAGE"] == "SUCCESS")
        {   
            session_start();
            if (!isset($_SESSION['MusicianId']))
            {
                $_SESSION['MusicianId'] = $registrationResponse['MusicianId'];
            }
            
            $response = array("MESSAGE"=>"SUCCESS");
            
            echo json_encode($response);
        }
        else
        {
            subGetErrorByCode($registrationResponse["MESSAGE"]);
        }
    }

    function subGetSessionFunction()
    {
        session_start();
        if (isset($_SESSION['MusicianId']))
        {
            echo json_encode(array("MESSAGE" => "SUCCESS"));   	    
        }
        else
        {
            subGetErrorByCode("406");
        }
    }

    function subGetCookiesFunction()
    {
        if (isset($_COOKIE['Email']) && isset($_COOKIE['MusicianId']))
        {
            echo json_encode(array('Email' => $_COOKIE['Email']));   	    
        }
        else
        {
            subGetErrorByCode("406");
        }
    }

    function subGetMusicianInfoFunction()
    {
        session_start();
        
        $getMusicianInfoResponse = jsonAttemptGetInfoById($_SESSION["MusicianId"]);
        
        if ($getMusicianInfoResponse["MESSAGE"] == "SUCCESS")
        {
            echo json_encode($getMusicianInfoResponse);
        }
        else
        {
            subGetErrorByCode($getMusicianInfoResponse["MESSAGE"]);
        }
    }

    function subGetInfoByIdFunction()
    {
        $getInfoByIdResponse = jsonAttemptGetInfoById($_POST["musicianId"]);
        
        if ($getInfoByIdResponse["MESSAGE"] == "SUCCESS")
        {
            echo json_encode($getInfoByIdResponse);
        }
        else
        {
            subGetErrorByCode($getInfoByIdResponse["MESSAGE"]);
        }
    }

    function subDeleteSessionFunction()
    {
        session_start();
        if (isset($_SESSION['MusicianId']))
        {
            unset($_SESSION['MusicianId']);
            session_destroy();
            echo json_encode(array('success' => 'Session deleted'));   	    
        }
        else
        {
            subGetErrorByCode("406");
        }
    }

    function subSubmitNewPerformanceFunction()
    {
        session_start();
        $musicianId = $_SESSION["MusicianId"];
        $place = $_POST["place"];
        $location = $_POST["location"];
        $dateTime = $_POST["datetime"];
        
        $submitNewPerformanceResponse = jsonAttemptSubmitNewPerformance($musicianId, $place, $location, $dateTime);
        
        if ($submitNewPerformanceResponse["MESSAGE"] == "SUCCESS")
        {
            echo json_encode(array("MESSAGE" => "SUCCESS"));
        }
        else
        {
            subGetErrorByCode($submitNewPerformanceResponse["MESSAGE"]);
        }
    }

    function subGetOwnPerformances()
    {
        session_start();
        subGetPerformancesById($_SESSION["MusicianId"]);
    }

    function subGetPerformances()
    {
        subGetPerformancesById($_POST["musicianId"]);
    }

    function subGetPerformancesById($MusicianId)
    {
        $getOwnPerformancesResponse = jsonAttemptGetPerformances($MusicianId);
        
        if ($getOwnPerformancesResponse["MESSAGE"] == "SUCCESS")
        {
            echo json_encode($getOwnPerformancesResponse["response"]);
        }
        else
        {
            subGetErrorByCode($getOwnPerformancesResponse["MESSAGE"]);
        }
    }

    function subUploadImageFunction()
    {
        
        session_start();

        if (!file_exists('C:/Users/Huvok/Documents/GitHub/Cover/uploads/images/' . $_SESSION["MusicianId"])) 
        {
            mkdir('C:/Users/Huvok/Documents/GitHub/Cover/uploads/images/' . $_SESSION["MusicianId"], 0777, true);
        }

        if(!move_uploaded_file($_FILES['images']['tmp_name'], 'C:/Users/Huvok/Documents/GitHub/Cover/uploads/images/' . $_SESSION["MusicianId"] . "/" . $_FILES['images']['name'])){
            die('Error uploading file - check destination is writeable.');
        }
        
        $uploadImageResponse = jsonAttemptUploadImage($_SESSION["MusicianId"], $_FILES["images"]["name"]);
        
        if ($uploadImageResponse["MESSAGE"] == "SUCCESS")
        {
            echo json_encode(array("MESSAGE" => "Image uploaded successfully."));
        }
        else
        {
            subGetErrorByCode($uploadImageResponse["MESSAGE"]);
        }
    }

    function subUploadTrackFunction()
    {
        session_start();

        if (!file_exists('C:/Users/Huvok/Documents/GitHub/Cover/uploads/audio/' . $_SESSION["MusicianId"])) 
        {
            mkdir('C:/Users/Huvok/Documents/GitHub/Cover/uploads/audio/' . $_SESSION["MusicianId"], 0777, true);
        }

        if(!move_uploaded_file($_FILES['tracks']['tmp_name'], 'C:/Users/Huvok/Documents/GitHub/Cover/uploads/audio/' . $_SESSION["MusicianId"] . "/" . $_FILES['tracks']['name'])){
            die('Error uploading file - check destination is writeable.');
        }

        $uploadTrackResponse = jsonAttemptUploadTrack($_SESSION["MusicianId"], $_FILES["tracks"]["name"]);

        if ($uploadTrackResponse["MESSAGE"] == "SUCCESS")
        {
            echo json_encode(array("MESSAGE" => "Track uploaded successfully."));
        }
        else
        {
            subGetErrorByCode($uploadImageResponse["MESSAGE"]);
        }
    }

    function subSearchFunction()
    {
        session_start();
        $searchResponse = jsonAttemptSearch($_SESSION["MusicianId"], $_POST["search"]);
        
        if ($searchResponse["MESSAGE"] == "SUCCESS")
        {
            echo json_encode($searchResponse["response"]);
        }
        else
        {
            subGetErrorByCode($searchResponse["MESSAGE"]);
        }
    }

    function subConnectRequestFunction()
    {
        session_start();
        $connectRequestResponse = jsonAttemptConnectRequest($_SESSION["MusicianId"], $_POST["MusicianToConnect"]);
        
        if ($connectRequestResponse["MESSAGE"] == "SUCCESS")
        {
            echo json_encode($connectRequestResponse["MESSAGE"]);
        }
        else
        {
            subGetErrorByCode($connectRequestResponse["MESSAGE"]);
        }
    }

    function subGetSentRequestsFunction()
    {
        session_start();
        $getSentRequestsResponse = jsonAttemptGetSentRequests($_SESSION["MusicianId"]);
        
        if ($getSentRequestsResponse["MESSAGE"] == "SUCCESS")
        {
            echo json_encode($getSentRequestsResponse["response"]);
        }
        else
        {
            subGetErrorByCode($getSentRequestsResponse["MESSAGE"]);
        }
    }

    function subGetReceivedRequestsFunction()
    {
        session_start();
        $getReceivedRequestsResponse = jsonAttemptGetReceivedRequests($_SESSION["MusicianId"]);
        
        if ($getReceivedRequestsResponse["MESSAGE"] == "SUCCESS")
        {
            echo json_encode($getReceivedRequestsResponse["response"]);
        }
        else
        {
            subGetErrorByCode($getReceivedRequestsResponse["MESSAGE"]);
        }
    }

    function subGetConnectionsFunction()
    {
        session_start();
        $getConnectionsResponse = jsonAttemptGetConnections($_SESSION["MusicianId"]);
        
        if ($getConnectionsResponse["MESSAGE"] == "SUCCESS")
        {
            echo json_encode($getConnectionsResponse["response"]);
        }
        else
        {
            subGetErrorByCode($getConnectionsResponse["MESSAGE"]);
        }
    }

    function subAcceptRequestFunction()
    {
        session_start();
        $acceptRequestResponse = jsonAttemptAcceptRequest($_SESSION["MusicianId"], $_POST["musicianToAccept"]);
        
        if ($acceptRequestResponse["MESSAGE"] == "SUCCESS")
        {
            echo json_encode(array("MESSAGE" => "SUCCESS"));
        }
        else
        {
            subGetErrorByCode($acceptRequestResponse["MESSAGE"]);
        }
    }

    function subRejectRequestFunction()
    {
        session_start();
        $rejectRequestResponse = jsonAttemptRejectRequest($_SESSION["MusicianId"], $_POST["musicianToReject"]);
        
        if ($rejectRequestResponse["MESSAGE"] == "SUCCESS")
        {
            echo json_encode(array("MESSAGE" => "SUCCESS"));
        }
        else
        {
            subGetErrorByCode($rejectRequestResponse["MESSAGE"]);
        }
    }

    function subGetRecentActivityFunction()
    {
        $recentActivityResponse = jsonAttemptGetRecentActivity($_POST["musicianId"]);
        
        if ($recentActivityResponse["MESSAGE"] == "SUCCESS")
        {
            $recentActivityResponse["response"]["musicianName"] = $_POST["musicianName"];
            $recentActivityResponse["response"]["country"] = $_POST["country"];
            $recentActivityResponse["response"]["city"] = $_POST["city"];
            $recentActivityResponse["response"]["email"] = $_POST["email"];
            
            echo json_encode($recentActivityResponse["response"]);
        }
        else
        {
            subGetErrorByCode($recentActivityResponse["MESSAGE"]);
        }
    }

    function subGetIdByEmailFunction()
    {
        $getIdByEmailResponse = jsonAttemptGetIdByEmail($_POST["email"]);

        if ($getIdByEmailResponse["MESSAGE"] == "SUCCESS")
        {
            echo json_encode(array("musicianId" => $getIdByEmailResponse["musicianId"]));
        }
        else
        {
            subGetErrorByCode($getIdByEmailResponse["MESSAGE"]);
        }
    }

    function subGetOwnImages()
    {
        session_start();
        subGetImagesById($_SESSION["MusicianId"]);
    }

    function subGetOwnTracks()
    {
        session_start();
        subGetTracksById($_SESSION["MusicianId"]);
    }

    function subGetImages()
    {
        subGetImagesById($_POST["musicianId"]);
    }

    function subGetTracks()
    {
        subGetTracksById($_POST["musicianId"]);
    }

    function subGetImagesById($MusicianId)
    {
        $directory = __DIR__ . "/../uploads/images/";

        $images = glob($directory . $MusicianId . "/*.jpg");

        $response = array();
        foreach($images as $image)
        {
            $response[] = base64_encode(file_get_contents($image));
        }

        echo json_encode($response);
    }

    function subGetTracksById($MusicianId)
    {
        $directory = __DIR__ . "/../uploads/audio/";

        $audio = glob($directory . $MusicianId . "/*.mp3");

        $response = array();
        foreach($audio as $track)
        {
          $response[] = base64_encode(file_get_contents($track));
        }

        echo json_encode($response);
    }

    function subUploadCoverPicture()
    {
        session_start();

        if(!move_uploaded_file($_FILES['cover']['tmp_name'], 'C:/Users/Huvok/Documents/GitHub/Cover/uploads/covers/' . $_SESSION["MusicianId"] . ".jpg")){
            die('Error uploading file - check destination is writeable.');
        }
        
        $uploadImageResponse = jsonAttemptUploadImage($_SESSION["MusicianId"], $_FILES["cover"]["name"]);
        
        if ($uploadImageResponse["MESSAGE"] == "SUCCESS")
        {
            subGetCoverPicture();
        }
        else
        {
            subGetErrorByCode($uploadImageResponse["MESSAGE"]);
        }
    }

    function subGetCoverPicture()
    {
        session_start();
        $directory = __DIR__ . "/../uploads/covers/";

        if (file_exists($directory . $_SESSION["MusicianId"] . ".jpg")) 
        {
            echo json_encode(array("url" => "../uploads/covers/" . $_SESSION["MusicianId"]));
        }
        else
        {
            echo json_encode(array("url" => "../images/Band Dummy Cover"));
        }
    }

    function subGetCoverPictureById()
    {
        $directory = __DIR__ . "/../uploads/covers/";

        if (file_exists($directory . $_POST["musicianId"] . ".jpg")) 
        {
            echo json_encode(array("url" => "../uploads/covers/" . $_POST["musicianId"]));
        }
        else
        {
            echo json_encode(array("url" => "../images/Band Dummy Cover"));
        }
    }

    function subGetErrorByCode($errorCode)
    {
        switch($errorCode)
        {
            case "500" : 
                header("HTTP/1.1 500 Bad connection, portal down");
                die("The server is down, we couln't stablish a connection.");
                break;
            case "409" :
                header("HTTP/1.1 409 The email is already taken.");
                die("The username is already taken.");
                break;
            case "406" : 
                header("HTTP/1.1 406 User not found.");
                die("Wrong credentials provided");
                break; 
            case "505" :
                header("HTTP/1.1 505 There has been an error.");
                die("There has been an error.");
                break;
        }
    }
     
 ?>