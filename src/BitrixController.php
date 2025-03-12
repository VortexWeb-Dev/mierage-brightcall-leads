<?php

require_once __DIR__ . '/../crest/crest.php';

class BitrixController
{
    public function addLead(array $leadData): void
    {
        if (empty($leadData['TITLE']) || empty($leadData['NAME']) || empty($leadData['PHONE'])) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode(['error' => 'Missing required lead fields: TITLE, NAME, PHONE']);
            exit;
        }

        $result = CRest::call('crm.lead.add', [
            'fields' => $leadData,
            'params' => ["REGISTER_SONET_EVENT" => "Y"]
        ]);

        if (isset($result['result'])) {
            header('Content-Type: application/json');
            http_response_code(201);
            echo json_encode(['message' => 'Lead added successfully', 'lead_id' => $result['result']]);
        } else {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['error' => 'Failed to add lead', 'details' => $result['error_description'] ?? 'Unknown error']);
        }
        exit;
    }
}
