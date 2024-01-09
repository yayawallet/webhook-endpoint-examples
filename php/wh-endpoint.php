<?php

// the same secret you configure on the YAYA dashboard
// keep this secure, and out of version control
// you can use an environment variable, or a config file
$secret_key = "test_key";
$tolerance  = 300; // 5 minutes

$payload = @file_get_contents( 'php://input' );
$data    = [];

try {
	$data = json_decode( $payload, true );
} catch ( \UnexpectedValueException $e ) {
	// Invalid payload
	http_response_code( 400 );
	exit();
}

$headers           = getallheaders();
$receivedSignature = isset( $headers ['YAYA-SIGNATURE'] ) ? $headers ['YAYA-SIGNATURE'] : '';

if ( empty( $receivedSignature ) ) {
	http_response_code( 400 );
	exit();
}

$dataToSign        = implode( '', $data );
$signedPayload     = mb_convert_encoding( $dataToSign, 'UTF-8', 'ISO-8859-1' );
$expectedSignature = hash_hmac( 'sha256', $signedPayload, $secret_key );

if ( $expectedSignature !== $receivedSignature ) {
	http_response_code( 400 );
	exit();
}

$timestamp           = $data ['timestamp'];
$differenceInSeconds = ( ( time() * 1000 ) - intval( $timestamp ) ) / 1000;

if ( $differenceInSeconds > $tolerance ) {
	// too old, ignore as it might be a replay
	http_response_code( 400 );
	exit();
}

/**
 * Do your thing here with the data!
 * If process is too long do it in the background and quickly return a successful status code
 */

// $data ['id'] is the transaction id
// $data ['amount'] is the amount in base currency
// $data ['currency'] is the base currency
// $data ['created_at_time'] is the time the transaction was created
// $data ['timestamp'] is the time the webhook was sent
// $data ['cause'] is the cause of the transaction
// $data ['full_name'] is the full name of the sender
// $data ['account_name'] is the unique account name of the sender
// $data ['invoice_url'] is the invoice url of the transaction

http_response_code( 200 );
