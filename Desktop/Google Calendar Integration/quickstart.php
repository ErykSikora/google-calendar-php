<?php
/**
 * Copyright 2018 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
// [START calendar_quickstart]
require __DIR__ . '/vendor/autoload.php';

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient()
{
    $client = new Google_Client();
    $client->setApplicationName('Google Calendar API PHP Quickstart');
    $client->setScopes(Google_Service_Calendar::CALENDAR_READONLY);
    $client->setAuthConfig('credentials.json');
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    // Load previously authorized token from a file, if it exists.
    // The file token.json stores the user's access and refresh tokens, and is
    // created automatically when the authorization flow completes for the first
    // time.
    $tokenPath = 'token.json';
    if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);
    }

    // If there is no previous token or it's expired.
    if ($client->isAccessTokenExpired()) {
        // Refresh the token if possible, else fetch a new one.
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            $client->setAccessToken($accessToken);

            // Check to see if there was an error.
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
        }
        // Save the token to a file.
        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }
    return $client;
}


// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Calendar($client);

// Print the next 10 events on the user's calendar.
$calendarId = 'xxx@group.calendar.google.com';
$optParams = array(
  'maxResults' => 10,
  'orderBy' => 'startTime',
  'singleEvents' => true,
  'timeMin' => date('c'),
);
$results = $service->events->listEvents($calendarId, $optParams);
$events = $results->getItems();

// if (empty($events)) {
//     print "No upcoming events found.\n";
// } else {
//     foreach ($events as $event) {
//         echo $event['summary']."<br>";
//         // if (empty($start)) {
//         //     $start = $event->start->date;
//         // }
//         // printf("%s (%s)\n", $event->getSummary(), $start);
//     }
// }


$json = file_get_contents('http://config.json.file');
$data = json_decode($json, true);

$onDuty = $events[1]['summary']; // [1] bo za dyżurnego bierzemy osobę z piątku
$email = $data['employees'][$onDuty];

if (!empty($email)) {

    require "phpmailer/class.phpmailer.php";
    require "phpmailer/class.smtp.php";

    $subject = 'Cześć, dyżurny!';
    $msg = '<p>test</p>';

    $mail = new PHPMailer;

    $smtp = false; // Daj true i uzupełnij dane żeby wysłać przy pocmocy SMTP

    if ($smtp == true) {
        $mail->isSMTP();
        // Dane do logowania są w pierwszych liniach
        $mail->Host = '';
        $mail->SMTPAuth = true;
        $mail->Username = '';
        $mail->Password = '';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
    }

    $mail->CharSet = "UTF-8";
    $mail->Subject = $subject;

    // FROM
    $mail->From = 'e.sikora95@gmail.com';
    $mail->FromName = 'ESIKORA';

    // TO
    $mail->AddAddress( $email , 'XXYY' );

    $mail->isHTML(true);
    $mail->Body = $msg;
    $mail->AltBody = $msg;

    if ($mail->send()) {
        # sent success
    } else {
        # not sent
    }

}