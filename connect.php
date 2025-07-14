$client_id = 'TU_CLIENT_ID';
$redirect_uri = urlencode('https://tusitio.com/callback');
$scope = urlencode('com.intuit.quickbooks.accounting');
$response_type = 'code';
$state = 'seguridad123';

$url = "https://appcenter.intuit.com/connect/oauth2?" .
       "client_id=$client_id&" .
       "redirect_uri=$redirect_uri&" .
       "response_type=$response_type&" .
       "scope=$scope&" .
       "state=$state";

header("Location: $url");
exit;