<?php

header('Content-Type: application/json; charset=utf-8');

try {
    $requestType = $_GET['request'] ?? null;

    if (!$requestType) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Hiányzó request paraméter. Példa: ?request=getCategories'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $allowedRequests = [
        'getProducts',
        'getCategories',
        'getCategoriesPlus',
        'getPricelist',
        'GetQtyDiscount',
        'getParams',
        'getParamValues',
        'getImages',
        'getStock',
        'getManufacture',
        'getStockManufacture',
        'sendOrder',
        'paymentStatusUpdate',
        'getOrderStatusChange'
    ];

    if (!in_array($requestType, $allowedRequests, true)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Nem engedélyezett request típus.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $authCode = getenv('OVIP_AUTH_CODE');
    $userId = getenv('OVIP_USER_ID');
    $webshopId = getenv('OVIP_WEBSHOP_ID');
    $ip = getenv('OVIP_IP');
    $soapLink = getenv('OVIP_SOAP_LINK');

    $signature = hash(
        'sha256',
        trim($userId . $webshopId . $authCode . $requestType . $ip)
    );

    $requestData = [
        'request' => $requestType,
        'user_id' => $userId,
        'signature' => $signature,
        'webshop_id' => $webshopId
    ];

    if (isset($_GET['extra_data'])) {
        $requestData['extra_data'] = $_GET['extra_data'];
    }

    if (isset($_GET['limit_from'])) {
        $requestData['limit_from'] = (int)$_GET['limit_from'];
    }

    if (isset($_GET['limit_to'])) {
        $requestData['limit_to'] = (int)$_GET['limit_to'];
    }

    $client = new SoapClient(null, [
        'location' => $soapLink,
        'uri' => $soapLink,
        'encoding' => 'UTF-8',
        'trace' => 1,
        'exceptions' => true
    ]);

    $result = $client->getRequest($requestData);

    echo json_encode([
        'success' => true,
        'request' => $requestType,
        'data' => $result
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}