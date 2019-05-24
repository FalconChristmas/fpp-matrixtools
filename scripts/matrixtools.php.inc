<?

function GetModels($host) {
    $ch = curl_init("http://" . $host . "/api/overlays/models");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec($ch);
    curl_close($ch);
    return json_decode($data, true);
}

function GetFonts($host) {
    $ch = curl_init("http://" . $host . "/api/overlays/fonts");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec($ch);
    curl_close($ch);
    return json_decode($data, true);
}

function GetModel($host, $model) {
    $ch = curl_init("http://" . $host . "/api/overlays/model/" . $model);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec($ch);
    curl_close($ch);
    return json_decode($data, true);
}

function ClearModel($host, $model) {
    $ch = curl_init("http://" . $host . "/api/overlays/model/" . $model . "/clear");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec($ch);
    curl_close($ch);
}

function GetModelData($host, $model) {
    $ch = curl_init("http://" . $host . "/api/overlays/model/" . $model. "/data");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $data = curl_exec($ch);
    curl_close($ch);
    return json_decode($data, true);
}

function SetModelState($host, $model, $state) {
    $data = "{\"State\": ". $state . "}";
    $ch = curl_init("http://" . $host . "/api/overlays/model/" . $model. "/state");
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

    $ch = curl_init("http://" . $host . "/api/overlays/model/" . $model. "/fill");
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

    $ch = curl_init("http://" . $host . "/api/overlays/model/" . $model. "/pixel");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data)));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
    $data = curl_exec($ch);
    curl_close($ch);
}
function DisplayTextOnModel($host, $model, $msg, $Position, $Font, $FontSize, $color, $PPS, $AntiAlias) {
    $data = array('Message'=>$msg, 'Position'=>$Position, 'Font'=>$Font, 'FontSize'=>$FontSize, 'Color'=>$color, 'PixelsPerSecond'=>$PPS, 'AntiAlias'=>$AntiAlias);
    $data = json_encode($data);
    $ch = curl_init("http://" . $host . "/api/overlays/model/" . $model. "/text");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data)));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
    $data = curl_exec($ch);
    curl_close($ch);
}
?>
