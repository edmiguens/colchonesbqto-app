$accessToken = 'eyJhbGciOiJkaXIiLCJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwieC5vcmciOiJIMCJ9..CdOXg-_MgcIDyvLZklZ7GA.QtmfSFsnOazbCrOUEL_3Qz0jU0lZu3Y8dnxFRn7bE58D4_GdWmiCkSdtQ9MTJoKUJOdDoDzDX1alHCwwToMtLy2tirrKZPYxw1TZAX9ikvp33kpInnYC2R7nOpfAQLaf6P6nLK2zmJk3hxIFBnMK4EXYCzFxy4rPUeZYaM5ZvsvyG9sjdPYC-sWZc4NHRxktFfxhe9f0wA15u0AiWm3TZOYFPyeBs_7F-vuzfkBKhEdO46_Vs-zFLXaTpwkwp17mvIe5H7bwiCVsw1vfegiF0ibItkRm3PhcgMf7ZSx7dIzYLnvqqZjeQhiUpQ4JG2x5ptlXu6qqrUXUIUPoqDBAlHrW0Ud6nB6V0QTN0OUL14ia7ZgkTURbouuMCZKn4yqH2xgKbcXU7hVODXD3FtTdRWoln8b-0KcPiZ69V8we6Tsp8yFC_Idw-QRjvbfDzZFY0U_DEisFc5ntyJRQ5L4XsJjCXRdVbnRiiUI3xcWP0kg.t_HZq8l8r186KGa2xulryw';
$realmId = '9341454854766198';

$url = "https://quickbooks.api.intuit.com/v3/company/$realmId/query?query=select * from Customer";

$headers = [
    "Authorization: Bearer $accessToken",
    "Accept: application/json",
    "Content-Type: application/text"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
print_r($data);
