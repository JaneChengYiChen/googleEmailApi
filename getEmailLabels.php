<?php
use Symfony\Component\Yaml\Yaml;

require __DIR__ . '/emailClient.php';

$client = getClient();
$service = new Google_Service_Gmail($client);
$config = Yaml::parse(file_get_contents(__DIR__ . "/config/getEmailAttachment.yaml"));

$user = $config['user'];
$labels = $service->users_labels->listUsersLabels($user);
$labels_arr = [];

foreach ($labels as $key => $value) {
    $labels_arr[$key]['id'][] = $value->id;
    $labels_arr[$key]['name'][] = $value->name;
}
return $labels_arr;
