<?php

require_once __DIR__ . "/crest/crest.php";

// Formats the comments for the call
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
    } elseif ($data['eventType'] === 'callEnded' && $data['type'] === 'INCOMING') {

        return sprintf(
            "=== Call Information ===\n" .
                "Call ID: %s\n" .
                "Call Type: %s\n" .
                "Event Type: %s\n\n" .

                "=== Client Details ===\n" .
                "Client Phone: %s\n" .
                "Line Number: %s\n\n" .

                "=== Agent Details ===\n" .
                "Brightcall User ID: %d\n\n" .

                "=== Call Timing ===\n" .
                "Call Start Time: %s\n" .
                "Call End Time: %s\n",

            // Call Information
            $data['callId'],
            $data['type'],
            $data['eventType'],

            // Client Details
            $data['clientPhone'],
            $data['lineNumber'],

            // Agent Details
            $data['userId'],

            // Call Timing
            tsToHuman($data['startTimestampMs']),
            tsToHuman($data['endTimestampMs'])
        );
    } elseif ($data['eventType'] === 'callEnded' && $data['type'] === 'OUTGOING') {
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
                "Call Start Time: %s\n" .
                "Call Answer Time: %s\n" .
                "Call End Time: %s\n",

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
            tsToHuman($data['startTimestampMs']),
            tsToHuman($data['answerTimestampMs']),
            tsToHuman($data['endTimestampMs']),

        );
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

// Gets the responsible person ID from the agent email
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

// Converts timestamp in milliseconds to ISO 8601 format
function tsToIso($tsMs, $tz = 'Asia/Dubai')
{
    return (new DateTime("@" . ($tsMs / 1000)))->setTimezone(new DateTimeZone($tz))->format('Y-m-d\TH:i:sP');
}

// Converts timestamp in milliseconds to human readable format
function tsToHuman($tsMs, $tz = 'Asia/Dubai')
{
    $date = (new DateTime("@" . ($tsMs / 1000)))->setTimezone(new DateTimeZone($tz));
    return $date->format('F j, Y \a\t h:i A (T)');
}

// Converts time in HH:MM:SS format to seconds
function timeToSec($time)
{
    $time = explode(':', $time);
    return $time[0] * 3600 + $time[1] * 60 + $time[2];
}

// Converts seconds to time in HH:MM:SS format
function getCallDuration($startTimestampMs, $endTimestampMs)
{
    return ($endTimestampMs - $startTimestampMs) / 1000;
}

// Registers a call in Bitrix
function registerCall($fields)
{
    $res = CRest::call('telephony.externalcall.register', $fields);
    return $res['result'];
}

// Finishes a call in Bitrix
function finishCall($fields)
{
    $res = CRest::call('telephony.externalcall.finish', $fields);
    return $res['result'];
}

// Attaches a record to a call
function attachRecord($fields)
{
    $res = CRest::call('telephony.externalcall.attachRecord', $fields);
    return $res['result'];
}
