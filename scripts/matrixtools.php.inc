<?

function GetModels($host) {
    if ($host == "") {
        $host = "localhost";
    }
    $ch = curl_init("http://" . $host . "/api/overlays/models");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec($ch);
    curl_close($ch);
    return json_decode($data, true);
}

function GetFonts($host) {
    if ($host == "") {
        $host = "localhost";
    }
    $ch = curl_init("http://" . $host . "/api/overlays/fonts");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec($ch);
    curl_close($ch);
    return json_decode($data, true);
}

function GetModel($host, $model) {
    if ($host == "") {
        $host = "localhost";
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://" . $host . "/api/overlays/model/" . curl_escape($ch, $model));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec($ch);
    curl_close($ch);
    return json_decode($data, true);
}

function ClearModel($host, $model) {
    if ($host == "") {
        $host = "localhost";
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://" . $host . "/api/overlays/model/" . curl_escape($ch, $model) . "/clear");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec($ch);
    curl_close($ch);
}

function GetModelData($host, $model) {
    if ($host == "") {
        $host = "localhost";
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://" . $host . "/api/overlays/model/" . curl_escape($ch, $model) . "/data");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec($ch);
    curl_close($ch);
    return json_decode($data, true);
}

function SetModelState($host, $model, $state) {
    logEntry("HELP!!!! " . $state);
    $data = "{\"State\": ". $state . "}";
    if ($host == "") {
        $host = "localhost";
    }
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "http://" . $host . "/api/overlays/model/" . curl_escape($ch, $model) . "/state");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data)));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
    $data = curl_exec($ch);
    curl_close($ch);
}

function FillModel($host, $model, $R, $G, $B) {
    $rgb = array($R, $G, $B);
    $data = array('RGB'=>$rgb);
    $data = json_encode($data);

    if ($host == "") {
        $host = "localhost";
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://" . $host . "/api/overlays/model/" . curl_escape($ch, $model) . "/fill");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data)));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
    $data = curl_exec($ch);
    curl_close($ch);
}
function SetModelPixel($host, $model, $X, $Y, $R, $G, $B) {
    $rgb = array($R, $G, $B);
    $data = array('X'=>$X, 'Y'=>$Y, 'RGB'=>$rgb);
    $data = json_encode($data);

    if ($host == "") {
        $host = "localhost";
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://" . $host . "/api/overlays/model/" . curl_escape($ch, $model) . "/pixel");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data)));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
    $data = curl_exec($ch);
    curl_close($ch);
}
function MapColorName($color) {
    if (strcasecmp($color, "red") == 0) {
        return "#FF0000";
    }
    if (strcasecmp($color, "green") == 0) {
        return "#00FF00";
    }
    if (strcasecmp($color, "blue") == 0) {
        return "#0000FF";
    }
    if (strcasecmp($color, "black") == 0) {
        return "#000000";
    }
    if (strcasecmp($color, "white") == 0) {
        return "#FFFFFF";
    }
    if (strcasecmp($color, "yellow") == 0) {
        return "#FFFF00";
    }
    if (strcasecmp($color, "gray") == 0) {
        return "#808080";
    }
    if (strcasecmp($color, "grey") == 0) {
        return "#808080";
    }
    if (strcasecmp($color, "cyan") == 0) {
        return "#00FFFF";
    }
    if (strcasecmp($color, "cyan") == 0) {
        return "#00FFFF";
    }
    if (strcasecmp($color, "magenta") == 0) {
        return "#FF00FF";
    }
    if (strcasecmp($color, "purple") == 0) {
        return "#800080";
    }
    if (strcasecmp($color, "orange") == 0) {
        return "#FFA500";
    }
    if (strcasecmp($color, "indigo") == 0) {
        return "#4B0082";
    }

    return $color;
}

function DisplayTextOnModel($host, $model, $msg, $Position, $Font, $FontSize, $color, $PPS, $AntiAlias, $Duration="10", $autoenable="false") {
    $color = MapColorName($color);
    
    
    $data["command"] = "Overlay Model Effect";
    
    $args = array();
    $args[] = $model;
    $args[] = "" . $autoenable;
    $args[] = "Text";
    $args[] = $color;
    $args[] = $Font;
    $args[] = "" . $FontSize;
    $args[] = "" . $AntiAlias;
    $args[] = $Position;
    $args[] = "" . $PPS;
    $args[] = "" . $Duration;
    $args[] = $msg;
    $data["args"] = $args;
    echo json_encode($data);
    $data = json_encode($data);
    
logEntry($host);
logEntry($data);

    if ($host == "") {
        $host = "localhost";
    }
    $ch = curl_init("http://" . $host . "/api/command");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data)));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS,$data);

    $data = curl_exec($ch);
    curl_close($ch);
}
?>
