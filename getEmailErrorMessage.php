<?php
use Symfony\Component\Yaml\Yaml;

require __DIR__ . '/emailClient.php';
require __DIR__ . '/DB.php';

date_default_timezone_set('Asia/Taipei');

$client = getClient();
$service = new Google_Service_Gmail($client);
$config = Yaml::parse(file_get_contents(__DIR__ . "/config/getEmailAttachment.yaml"));

$user = $config['user'];
$before = date('Y/m/d');
//測試用
$before = date('Y/m/d', strtotime("+1 day", strtotime($before)));
$after = date('Y/m/d', strtotime("-1 day", strtotime($before)));
$err_source = $config['error'];

// before and after 是以當日整點計算
$messages = $service->users_messages->listUsersMessages(
    $user,
    array(
        "q" => "from:{$err_source} after:{$after} before:{$before}",
        "maxResults" => 100)
);

// if there is no message then return
$isMessage = count($messages);
if ($isMessage == 0) {
    echo json_encode(['status' => 1]);
    return false;
}

$emailAccount = $config['email_account'];
foreach ($messages as $messages) {
    $message = $service->users_messages->get($emailAccount, $messages->getId());
    $snippet = $message->snippet;
    $parts = $message->payload->parts;
    $details = $parts[2];

    $insertDataList = $details->parts;
    $value = keyValue($insertDataList[0]->headers);
    $value['error_msg'] = $snippet;

    $data = insertData($value);
    $db->insert('email_issues', $data);

    echo json_encode(['status' => 0]);
}

function keyValue($obj)
{
    $return_arr = [];
    foreach ($obj as $value) {
        $return_arr = $return_arr + array($value->name => $value->value);
    }
    return $return_arr;
}

function insertData($arr)
{
    $arr = [
        'from' => $arr['From'],
        'to' => $arr['To'],
        'subject' => $arr['Subject'],
        'date' => $arr['Date'],
        'error_message' => $arr['error_msg'],
        'created_at' => date('Y-m-d H:i:s'),
        'message_id' => $arr['Message-ID'],
    ];

    return $arr;
}
