<?php
require_once __DIR__ . "/../crest/crest.php";
require_once __DIR__ . "/../utils.php";

define('CONFIG', require_once __DIR__ . '/../config.php');

class WebhookController
{
    private const ALLOWED_ROUTES = [
        'callStarted' => 'handleCallStarted',
        'callRinging' => 'handleCallRinging',
        'callAnswered' => 'handleCallAnswered',
        'callEnded' => 'handleCallEnded',
        'smsEvent' => 'handleSmsEvent',
        'webphoneSummary' => 'handleWebphoneSummary',
        'aiTranscriptionSummary' => 'handleAiTranscriptionSummary',
        'speedToLead' => 'handleSpeedToLead'
    ];

    private LoggerController $logger;
    private BitrixController $bitrix;

    public function __construct()
    {
        $this->logger = new LoggerController();
        $this->bitrix = new BitrixController();
    }

    // Handles incoming webhooks
    public function handleRequest(string $route): void
    {
        try {
            $this->logger->logRequest($route);

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->sendResponse(405, [
                    'error' => 'Method Not Allowed. Only POST is accepted.'
                ]);
            }

            if (!array_key_exists($route, self::ALLOWED_ROUTES)) {
                $this->sendResponse(404, [
                    'error' => 'Resource not found'
                ]);
            }

            $handlerMethod = self::ALLOWED_ROUTES[$route];

            $data = $this->parseRequestData();
            if ($data === null) {
                $this->sendResponse(400, [
                    'error' => 'Invalid JSON data'
                ]);
            }

            $this->$handlerMethod($data);
        } catch (Throwable $e) {
            $this->logger->logError('Error processing request', $e);
            $this->sendResponse(500, [
                'error' => 'Internal server error'
            ]);
        }
    }

    // Parses incoming JSON data
    private function parseRequestData(): ?array
    {
        $rawData = file_get_contents('php://input');
        $data = json_decode($rawData, true);
        return $data;
    }

    // Sends response back to the webhook
    private function sendResponse(int $statusCode, array $data): void
    {
        header("Content-Type: application/json");
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    // Handles callStarted webhook event
    public function handleCallStarted(array $data): void
    {
        $this->logger->logWebhook('call_started', $data);

        $leadData = [
            'TITLE' => 'Brightcall Lead - ' . $data['eventType'] . ' - ' . $data['type'],
            'NAME' => 'Unknown Caller from Brightcall (' . $data['clientPhone'] . ')',
            'PHONE' => [
                [
                    'VALUE' => $data['clientPhone'],
                    'VALUE_TYPE' => 'WORK'
                ]
            ],
            'COMMENTS' => formatComments($data),
            'SOURCE_ID' => CONFIG['BRIGHTCALL_SOURCE_ID'],
            'UF_CRM_1726164235378' => CONFIG['CALL_COLLECTION_SOURCE_ID'],
            'UF_CRM_1726453884158' => tsToIso($data['timestampMs']),
            'ASSIGNED_BY_ID' => $data['agentEmail'] ? getResponsiblePersonId($data['agentEmail']) : CONFIG['DEFAULT_RESPONSIBLE_PERSON_ID'],
            'UF_CRM_1743492578846' => $data['callId'],
        ];

        $existingLead = $this->bitrix->getLeadByCallId($data['callId']);

        if ($existingLead) {
            $leadId = $existingLead['ID'];
            $this->bitrix->updateLead(array_merge($leadData, ['IS_RETURN_CUSTOMER' => 'Y']), $leadId);
            $this->sendResponse(200, [
                'message' => 'Call started data processed successfully and lead updated with ID: ' . $leadId,
            ]);
            return;
        } else {
            $leadId = $this->bitrix->addLead($leadData);
        }

        if (!$leadId) {
            $this->sendResponse(500, [
                'error' => 'Failed to create lead in Bitrix'
            ]);
        }

        $this->sendResponse(200, [
            'message' => 'Call started data processed successfully and lead created with ID: ' . $leadId,
        ]);
    }

    // Handles callRinging webhook event
    public function handleCallRinging(array $data): void
    {
        $this->logger->logWebhook('call_ringing', $data);

        $leadData = [
            'TITLE' => 'Brightcall Lead - ' . $data['eventType'] . ' - ' . $data['type'],
            'NAME' => 'Unknown Caller from Brightcall (' . $data['clientPhone'] . ')',
            'PHONE' => [
                [
                    'VALUE' => $data['clientPhone'],
                    'VALUE_TYPE' => 'WORK'
                ]
            ],
            'COMMENTS' => formatComments($data),
            'SOURCE_ID' => CONFIG['BRIGHTCALL_SOURCE_ID'],
            'UF_CRM_1726164235378' => CONFIG['CALL_COLLECTION_SOURCE_ID'],
            'UF_CRM_1726453884158' => tsToIso($data['timestampMs']),
            'ASSIGNED_BY_ID' => $data['agentEmail'] ? getResponsiblePersonId($data['agentEmail']) : CONFIG['DEFAULT_RESPONSIBLE_PERSON_ID'],
            'UF_CRM_1743492578846' => $data['callId'],
        ];

        $existingLead = $this->bitrix->getLeadByCallId($data['callId']);

        if ($existingLead) {
            $leadId = $existingLead['ID'];
            $this->bitrix->updateLead(array_merge($leadData, ['IS_RETURN_CUSTOMER' => 'Y']), $leadId);
            $this->sendResponse(200, [
                'message' => 'Call ringing data processed successfully and lead updated with ID: ' . $leadId,
            ]);
            return;
        } else {
            $leadId = $this->bitrix->addLead($leadData);
        }

        if (!$leadId) {
            $this->sendResponse(500, [
                'error' => 'Failed to create lead in Bitrix'
            ]);
        }

        $this->sendResponse(200, [
            'message' => 'Call ringing data processed successfully and lead created with ID: ' . $leadId,
        ]);
    }

    // Handles callAnswered webhook event
    public function handleCallAnswered(array $data): void
    {
        $this->logger->logWebhook('call_answered', $data);

        $leadData = [
            'TITLE' => 'Brightcall Lead - ' . $data['eventType'] . ' - ' . $data['type'],
            'NAME' => 'Unknown Caller from Brightcall (' . $data['clientPhone'] . ')',
            'PHONE' => [
                [
                    'VALUE' => $data['clientPhone'],
                    'VALUE_TYPE' => 'WORK'
                ]
            ],
            'COMMENTS' => formatComments($data),
            'SOURCE_ID' => CONFIG['BRIGHTCALL_SOURCE_ID'],
            'UF_CRM_1726164235378' => CONFIG['CALL_COLLECTION_SOURCE_ID'],
            'UF_CRM_1726453884158' => tsToIso($data['timestampMs']),
            'ASSIGNED_BY_ID' => $data['agentEmail'] ? getResponsiblePersonId($data['agentEmail']) : CONFIG['DEFAULT_RESPONSIBLE_PERSON_ID'],
            'UF_CRM_1743492578846' => $data['callId'],
        ];

        $existingLead = $this->bitrix->getLeadByCallId($data['callId']);

        if ($existingLead) {
            $leadId = $existingLead['ID'];
            $this->bitrix->updateLead(array_merge($leadData, ['IS_RETURN_CUSTOMER' => 'Y']), $leadId);
            $this->sendResponse(200, [
                'message' => 'Call answered data processed successfully and lead updated with ID: ' . $leadId,
            ]);
            return;
        } else {
            $leadId = $this->bitrix->addLead($leadData);
        }

        if (!$leadId) {
            $this->sendResponse(500, [
                'error' => 'Failed to create lead in Bitrix'
            ]);
        }

        $this->sendResponse(200, [
            'message' => 'Call answered data processed successfully and lead created with ID: ' . $leadId,
        ]);
    }

    // Handles callEnded webhook event
    public function handleCallEnded(array $data): void
    {
        $this->logger->logWebhook('call_ended', $data);

        $leadData = [
            'TITLE' => 'Brightcall Lead - ' . $data['eventType'] . ' - ' . $data['type'],
            'NAME' => 'Unknown Caller from Brightcall (' . $data['clientPhone'] . ')',
            'PHONE' => [
                [
                    'VALUE' => $data['clientPhone'],
                    'VALUE_TYPE' => 'WORK'
                ]
            ],
            'COMMENTS' => formatComments($data),
            'SOURCE_ID' => CONFIG['BRIGHTCALL_SOURCE_ID'],
            'UF_CRM_1726164235378' => CONFIG['CALL_COLLECTION_SOURCE_ID'],
            'UF_CRM_1726453884158' => tsToIso($data['startTimestampMs']),
            'ASSIGNED_BY_ID' => $data['agentEmail'] ? getResponsiblePersonId($data['agentEmail']) : CONFIG['DEFAULT_RESPONSIBLE_PERSON_ID'],
            'UF_CRM_1743492578846' => $data['callId'],
        ];

        $existingLead = $this->bitrix->getLeadByCallId($data['callId']);

        if ($existingLead) {
            $leadId = $existingLead['ID'];
            $this->bitrix->updateLead(array_merge($leadData, ['IS_RETURN_CUSTOMER' => 'Y']), $leadId);
            $this->sendResponse(200, [
                'message' => 'Call ended data processed successfully and lead updated with ID: ' . $leadId,
            ]);
            return;
        } else {
            $leadId = $this->bitrix->addLead($leadData);
        }

        if (!$leadId) {
            $this->sendResponse(500, [
                'error' => 'Failed to create lead in Bitrix'
            ]);
        }

        if ($data['recordName']) {
            $callRecordContent = @file_get_contents($data['recordName']);

            $registerCall = $this->registerCall($leadData, $data, $leadId);
            $callId = $registerCall['CALL_ID'] ?? null;

            if ($callId) {
                $this->finishCallAndAttachRecord($callId, $leadData, $data, $callRecordContent);
            }
        }

        $this->sendResponse(200, [
            'message' => 'Call ended data processed successfully and lead created with ID: ' . $leadId,
        ]);
    }

    // Handles smsEvent webhook event
    public function handleSmsEvent(array $data): void
    {
        $this->logger->logWebhook('sms_event', $data);
        $this->sendResponse(200, [
            'message' => 'SMS event data processed successfully'
        ]);
    }

    // Handles webphoneSummary webhook event
    public function handleWebphoneSummary(array $data): void
    {
        $this->logger->logWebhook('webphone_summary', $data);

        $leadData = [
            'TITLE' => 'Brightcall Lead - ' . $data['eventType'] . ' - ' . $data['type'],
            'NAME' => 'Unknown Caller from Brightcall (' . $data['clientPhone'] . ')',
            'PHONE' => [
                [
                    'VALUE' => $data['clientPhone'],
                    'VALUE_TYPE' => 'WORK'
                ]
            ],
            'COMMENTS' => formatComments($data),
            'SOURCE_ID' => CONFIG['BRIGHTCALL_SOURCE_ID'],
            'UF_CRM_1726164235378' => CONFIG['CALL_COLLECTION_SOURCE_ID'],
            'UF_CRM_1726453884158' => tsToIso($data['startTimestampMs']),
            'ASSIGNED_BY_ID' => $data['agentEmail'] ? getResponsiblePersonId($data['agentEmail']) : CONFIG['DEFAULT_RESPONSIBLE_PERSON_ID'],
            'UF_CRM_1743492578846' => $data['callId'],
        ];

        $existingLead = $this->bitrix->getLeadByCallId($data['callId']);

        if ($existingLead) {
            $leadId = $existingLead['ID'];
            $this->bitrix->updateLead(array_merge($leadData, ['IS_RETURN_CUSTOMER' => 'Y']), $leadId);
            $this->sendResponse(200, [
                'message' => 'Webphone summary data processed successfully and lead updated with ID: ' . $leadId,
            ]);
            return;
        } else {
            $leadId = $this->bitrix->addLead($leadData);
        }

        if (!$leadId) {
            $this->sendResponse(500, [
                'error' => 'Failed to create lead in Bitrix'
            ]);
        }

        if ($data['type'] === 'OUTGOING' && $data['recordName']) {
            $callRecordContent = @file_get_contents($data['recordName']);

            $registerCall = $this->registerCall($leadData, $data, $leadId);
            $callId = $registerCall['CALL_ID'] ?? null;

            if ($callId) {
                $this->finishCallAndAttachRecord($callId, $leadData, $data, $callRecordContent);
            }
        }

        $this->sendResponse(200, [
            'message' => 'Webphone summary data processed successfully and lead created with ID: ' . $leadId,
        ]);
    }

    // Handles aiTranscriptionSummary webhook event
    public function handleAiTranscriptionSummary(array $data): void
    {
        $this->logger->logWebhook('ai_transcription', $data);
        $this->sendResponse(200, [
            'message' => 'AI transcription summary data processed successfully'
        ]);
    }

    // Handles speedToLead webhook event
    public function handleSpeedToLead(array $data): void
    {
        $this->logger->logWebhook('speed_to_lead', $data);

        if (!$data['type'] || $data['type'] !== 'lead_created') {
            return;
        }

        $leadData = [
            'TITLE' => 'Brightcall Lead - ' . $data['widget_name'] . ' - ' . $data['type'],
            'NAME' => $data['lead']['custom_params']['lc_param_name'],
            'PHONE' => [
                [
                    'VALUE' => $data['lead']['lead_phone'],
                    'VALUE_TYPE' => 'WORK'
                ]
            ],
            'EMAIL' => [
                [
                    'VALUE' => $data['lead']['custom_params']['lc_param_email'],
                    'VALUE_TYPE' => 'WORK'
                ]
            ],
            'COMMENTS' => formatComments($data),
            'SOURCE_ID' => $data['lead']['custom_params']['api_source'] === 'Facebook' ? CONFIG['FACEBOOK_SOURCE_ID'] : CONFIG['BRIGHTCALL_SOURCE_ID'],
            'UF_CRM_1726164235378' => $data['lead']['custom_params']['api_source'] === 'Facebook' ? CONFIG['FACEBOOK_COLLECTION_SOURCE_ID'] : CONFIG['CALL_COLLECTION_SOURCE_ID'],
            'UF_CRM_1726453884158' => $data['lead']['time_created_iso_string'],
            'ASSIGNED_BY_ID' => CONFIG['DEFAULT_RESPONSIBLE_PERSON_ID'],
            'UF_CRM_1743492578846' => $data['callId'],
            'SOURCE_DESCRIPTION' => $data['widget_name'],
        ];

        $existingLead = $this->bitrix->getLeadByCallId($data['callId']);

        if ($existingLead) {
            $leadId = $existingLead['ID'];
            $this->bitrix->updateLead(array_merge($leadData, ['IS_RETURN_CUSTOMER' => 'Y']), $leadId);
            $this->sendResponse(200, [
                'message' => 'Speed to lead data processed successfully and lead updated with ID: ' . $leadId,
            ]);
            return;
        } else {
            $leadId = $this->bitrix->addLead($leadData);
        }

        if (!$leadId) {
            $this->sendResponse(500, [
                'error' => 'Failed to create lead in Bitrix'
            ]);
        }

        $this->sendResponse(200, [
            'message' => 'Speed to lead data processed successfully and lead created with ID: ' . $leadId,
        ]);
    }

    // Registers a call in Bitrix
    private function registerCall($leadData, $data, $leadId)
    {
        $registerCall = registerCall([
            'USER_PHONE_INNER' => $data['lineNumber'],
            'USER_ID' => $leadData['ASSIGNED_BY_ID'],
            'PHONE_NUMBER' => $data['clientPhone'],
            'CALL_START_DATE' => tsToIso($data['startTimestampMs']),
            'CRM_CREATE' => false,
            'CRM_SOURCE' => $leadData['SOURCE_ID'],
            'CRM_ENTITY_TYPE' => 'LEAD',
            'CRM_ENTITY_ID' => $leadId,
            'SHOW' => false,
            'TYPE' => $data['type'] === 'INCOMING' ? 2 : 1,
            'LINE_NUMBER' => 'Brightcall ' . $data['receiver_number'],
        ]);

        return $registerCall;
    }

    // Finishes a call and attaches the call record
    private function finishCallAndAttachRecord($callId, $leadData, $data, $callRecordContent)
    {
        $finishCall = finishCall([
            'CALL_ID' => $callId,
            'USER_ID' => $leadData['ASSIGNED_BY_ID'],
            'DURATION' => getCallDuration($data['startTimestampMs'], $data['endTimestampMs']),
            'STATUS_CODE' => 200,
        ]);

        $attachRecord = attachRecord([
            'CALL_ID' => $callId,
            'FILENAME' => $data['callId'] . '|' . uniqid($data['endTimestampMs']) . '.mp3',
            'FILE_CONTENT' => base64_encode($callRecordContent),
        ]);
    }
}
