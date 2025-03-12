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
        'aiTranscriptionSummary' => 'handleAiTranscriptionSummary'
    ];

    private LoggerController $logger;
    private BitrixController $bitrix;

    public function __construct()
    {
        $this->logger = new LoggerController();
        $this->bitrix = new BitrixController();
    }

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

    private function parseRequestData(): ?array
    {
        $rawData = file_get_contents('php://input');
        $data = json_decode($rawData, true);
        return $data;
    }

    private function sendResponse(int $statusCode, array $data): void
    {
        header("Content-Type: application/json");
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

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
            'ASSIGNED_BY_ID' => getResponsiblePersonId($data['agentEmail']) ?? CONFIG['DEFAULT_RESPONSIBLE_PERSON_ID'],
        ];

        $leadId = $this->bitrix->addLead($leadData);

        if (!$leadId) {
            $this->sendResponse(500, [
                'error' => 'Failed to create lead in Bitrix'
            ]);
        }

        $this->sendResponse(200, [
            'message' => 'Call started data processed successfully and lead created with ID: ' . $leadId,
        ]);
    }

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
            'ASSIGNED_BY_ID' => getResponsiblePersonId($data['agentEmail']) ?? CONFIG['DEFAULT_RESPONSIBLE_PERSON_ID'],
        ];

        $leadId = $this->bitrix->addLead($leadData);

        if (!$leadId) {
            $this->sendResponse(500, [
                'error' => 'Failed to create lead in Bitrix'
            ]);
        }

        $this->sendResponse(200, [
            'message' => 'Call ringing data processed successfully'
        ]);
    }

    public function handleCallAnswered(array $data): void
    {
        $this->logger->logWebhook('call_answered', $data);
        $this->sendResponse(200, [
            'message' => 'Call answered data processed successfully'
        ]);
    }

    public function handleCallEnded(array $data): void
    {
        $this->logger->logWebhook('call_ended', $data);
        $this->sendResponse(200, [
            'message' => 'Call ended data processed successfully'
        ]);
    }

    public function handleSmsEvent(array $data): void
    {
        $this->logger->logWebhook('sms_event', $data);
        $this->sendResponse(200, [
            'message' => 'SMS event data processed successfully'
        ]);
    }

    public function handleWebphoneSummary(array $data): void
    {
        $this->logger->logWebhook('webphone_summary', $data);
        $this->sendResponse(200, [
            'message' => 'Webphone summary data processed successfully'
        ]);
    }

    public function handleAiTranscriptionSummary(array $data): void
    {
        $this->logger->logWebhook('ai_transcription', $data);
        $this->sendResponse(200, [
            'message' => 'AI transcription summary data processed successfully'
        ]);
    }
}
