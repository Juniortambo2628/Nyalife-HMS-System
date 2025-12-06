<?php
/**
 * Nyalife HMS - JSON Response Utility
 *
 * Helper functions for returning standardized JSON responses
 */
/**
 * Send a JSON response with detailed parameters and exit
 *
 * @param bool $success Whether the request was successful
 * @param string $message Response message
 * @param array $data Optional data to include in the response
 */
function sendDetailedJsonResponse($success, $message, $data = null): void
{
    // Set the content type to application/json
    header('Content-Type: application/json');

    // Create the response array
    $response = [
        'success' => (bool)$success,
        'message' => $message
    ];

    // Add data if provided
    if ($data !== null) {
        $response['data'] = $data;
    }

    // Encode and output the response
    echo json_encode($response);
    exit;
}
