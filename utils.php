<?php

require_once __DIR__ . "/crest/crest.php";

function formatComments(array $data): string
{
    if ($data['eventType'] === 'callStarted') {

        return sprintf(
            "=== Call Information ===\n" .
                "Call ID: %s\n" .
                "Call Type: %s\n" .
                "Event Type: %s\n\n" .

                "=== Client Details ===\n" .
                "Client Phone: %s\n" .
                "Line Number: %s\n\n" .

                "=== Agent Details ===\n" .
                "Brightcall User ID: %d\n" .
                "Brightcall Agent ID: %d\n" .
                "Agent Name: %s\n" .
                "Agent Email: %s\n\n" .

                "=== Call Timing ===\n" .
                "Call Start Time: %s\n",

            // Call Information
            $data['callId'],
            $data['type'],
            $data['eventType'],

            // Client Details
            $data['clientPhone'],
            $data['lineNumber'],

            // Agent Details
            $data['userId'],
            $data['agentId'],
            $data['agentName'],
            $data['agentEmail'],

            // Call Timing
            tsToHuman($data['timestampMs'])
        );
    } elseif ($data['eventType'] === 'callRinging') {

        return sprintf(
            "=== Call Information ===\n" .
                "Call ID: %s\n" .
                "Call Type: %s\n" .
                "Event Type: %s\n\n" .

                "=== Client Details ===\n" .
                "Client Phone: %s\n" .
                "Line Number: %s\n\n" .

                "=== Agent Details ===\n" .
                "Brightcall User ID: %d\n" .
                "Brightcall Agent ID: %d\n" .
                "Agent Email: %s\n\n" .

                "=== Call Timing ===\n" .
                "Call Start Time: %s\n",

            // Call Information
            $data['callId'],
            $data['type'],
            $data['eventType'],

            // Client Details
            $data['clientPhone'],
            $data['lineNumber'],

            // Agent Details
            $data['userId'],
            $data['agentId'],
            $data['agentEmail'],

            // Call Timing
            tsToHuman($data['timestampMs'])
        );
    } elseif ($data['eventType'] === 'callAnswered') {

        return "

                ";
    } elseif ($data['eventType'] === 'callEnded') {

        return "

                ";
    } elseif ($data['eventType'] === 'smsEvent') {

        return "

                ";
    } elseif ($data['eventType'] === 'webphoneSummary') {

        return "

                ";
    } elseif ($data['eventType'] === 'aiTranscriptionSummary') {

        return "

                ";
    }

    return "";
}


function getResponsiblePersonId(string $agentEmail): ?int
{
    $responsiblePersonId = null;

    $response = CRest::call('user.get', [
        'filter' => [
            'EMAIL' => $agentEmail
        ]
    ]);

    if (isset($response['result'][0]['ID'])) {
        $responsiblePersonId = $response['result'][0]['ID'];
    }

    return $responsiblePersonId;
}

function tsToIso($tsMs, $tz = 'Asia/Dubai')
{
    return (new DateTime("@" . ($tsMs / 1000)))->setTimezone(new DateTimeZone($tz))->format('Y-m-d\TH:i:sP');
}

function tsToHuman($tsMs, $tz = 'Asia/Dubai')
{
    $date = (new DateTime("@" . ($tsMs / 1000)))->setTimezone(new DateTimeZone($tz));
    return $date->format('F j, Y \a\t h:i A (T)');
}
