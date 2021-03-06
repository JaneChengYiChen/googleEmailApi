<?php
use Symfony\Component\Yaml\Yaml;

require __DIR__ . '/emailClient.php';

$client = getClient();
$service = new Google_Service_Gmail($client);
$config = Yaml::parse(file_get_contents(__DIR__ . "/config/getEmailAttachment.yaml"));

$user = $config['user'];
$messages = $service->users_messages->listUsersMessages(
    $user,
    array(
        "labelIds" => $config['label'],
        "maxResults" => 50)
);
$emailAccount = $config['email_account'];

define('UPLOAD_DIR', $config['upload_dir']);

foreach ($messages as $messages) {
    $message = $service->users_messages->get($emailAccount, $messages->getId());

    $attachmentId = $message->payload->parts[1]->body->attachmentId;
    if (!is_null($attachmentId)) {
        //get attachments
        $fileName = $message->payload->parts[1]->filename;
        $attachmentObj = $service->users_messages_attachments->get(
            $emailAccount,
            $messages->getId(),
            $attachmentId
        );
        $data = $attachmentObj->getData();
        $data = strtr($data, array('-' => '+', '_' => '/'));
        $file = UPLOAD_DIR . $fileName;
        file_put_contents($file, base64_decode($data));

        echo 'successful';
    }
}
