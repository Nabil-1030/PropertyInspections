<?php
require __DIR__ . '/vendor/autoload.php';
use Mollie\Api\MollieApiClient;

// Mollie API key
$mollie = new MollieApiClient();
$mollie->setApiKey("test_JBUgPsf8HkAtrcqKryNDJqR2ex2dcs");

// --- 1. Haal formuliergegevens op ---
$tenant_name      = htmlspecialchars($_POST['tenant_name'] ?? '');
$tenant_firstname = htmlspecialchars($_POST['tenant_firstname'] ?? '');
$tenant_email     = htmlspecialchars($_POST['tenant_email'] ?? '');
$tenant_phone     = htmlspecialchars($_POST['tenant_phone'] ?? '');
$owner_name       = htmlspecialchars($_POST['owner_name'] ?? '');
$owner_firstname  = htmlspecialchars($_POST['owner_firstname'] ?? '');
$owner_email      = htmlspecialchars($_POST['owner_email'] ?? '');
$owner_phone      = htmlspecialchars($_POST['owner_phone'] ?? '');
$type_pand        = $_POST['type_pand'] ?? '';
$type_plaats      = $_POST['type_plaatsbeschrijving'] ?? '';
$gemeubeld        = $_POST['gemeubeld'] ?? '';
$datum            = $_POST['datum'] ?? '';
$time             = $_POST['time'] ?? '';
$payer            = $_POST['payer'] ?? '';
$payment_method   = $_POST['payment_method'] ?? 'cash'; // standaard cash
$message          = htmlspecialchars($_POST['message'] ?? '');

// --- 2. Bepaal prijs op basis van type pand en intrede/uittrede ---
$prijzen = [
    "garage" => ["intrede" => 157.30, "uittrede" => 144.97],
    "studio" => ["intrede" => 181.50, "uittrede" => 166.98],
    "appartement1" => ["intrede" => 193.60, "uittrede" => 178.11],
    "appartement2" => ["intrede" => 217.80, "uittrede" => 200.38],
    "appartement3" => ["intrede" => 242.00, "uittrede" => 222.64],
    "woning" => ["intrede" => 296.45, "uittrede" => 272.73],
];

$bedrag_volledig = $prijzen[$type_pand][$type_plaats] ?? 0;
$bedrag_voorschot = $bedrag_volledig * 0.3;
$bedrag_str = number_format($bedrag_voorschot, 2, '.', '');

// --- 3. Verstuur mail naar contact@pibelgium.com ---
$to = "contact@pibelgium.com";
$subject = "Nieuwe boeking: $type_plaats - $type_pand";

$email_message = "
<html>
<head>
  <title>Nieuwe boeking</title>
</head>
<body>
  <h2>Nieuwe boeking ontvangen</h2>
  <h3>Huurder</h3>
  <p>$tenant_name $tenant_firstname / $tenant_email / $tenant_phone</p>
  <h3>Eigenaar</h3>
  <p>$owner_name $owner_firstname / $owner_email / $owner_phone</p>
  <h3>Pandgegevens</h3>
  <p>Type pand: $type_pand</p>
  <p>Type plaatsbeschrijving: $type_plaats</p>
  <p>Gemeubeld: $gemeubeld</p>
  <p>Datum & tijd: $datum $time</p>
  <h3>Betaling</h3>
  <p>Wie betaalt: $payer</p>
  <p>Betaalmethode: $payment_method</p>
";

// Voor cash klanten, volledig bedrag
if($payment_method === 'cash') {
    $email_message .= "
    <p>Bedrag volledig: €" . number_format($bedrag_volledig, 2, ',', '.') . " (volledig te betalen ter plaatse)</p>
    <p><strong>Opmerking:</strong> Klanten regelen zelf de verdeling van betaling tussen eigenaar en huurder.</p>";
}


$email_message .= "
<h3>Extra informatie</h3>
<p>$message</p>
</body>
</html>
";

$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type:text/html;charset=UTF-8\r\n";
$headers .= "From: contact@pibelgium.com\r\n";

mail($to, $subject, $email_message, $headers);

// --- 4. Cash of Mollie? ---
if($payment_method === 'cash') {
    // Redirect naar bevestigde pagina
    header("Location: /BOEKING/cash-confirmed.php");
    exit;
} else {
    // Mollie betaling aanmaken
   // Mollie betaling aanmaken
$payment = $mollie->payments->create([
    "amount" => [
        "currency" => "EUR",
        "value" => $bedrag_str
    ],
    "description" => "Boeking voorschot $type_plaats voor $type_pand",
    "redirectUrl" => "thank-you.php?id={$payment->id}", // <-- Mollie ID meesturen
    "metadata" => [
        "tenant_name" => $tenant_name,
        "tenant_email" => $tenant_email,
        "owner_name" => $owner_name,
        "owner_email" => $owner_email,
        "type_pand" => $type_pand,
        "type_plaats" => $type_plaats,
        "payer" => $payer,
        "bedrag_voorschot" => $bedrag_voorschot,
        "bedrag_volledig" => $bedrag_volledig
    ]
]);

// Redirect naar Mollie checkout
header("Location: " . $payment->getCheckoutUrl());
exit;

}
