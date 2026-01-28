<?php
require __DIR__ . '/vendor/autoload.php';
use Mollie\Api\MollieApiClient;

$mollie = new MollieApiClient();
$mollie->setApiKey("test_JBUgPsf8HkAtrcqKryNDJqR2ex2dcs");

// Haal payment ID op uit de GET-parameter
$paymentId = $_GET['id'] ?? null;
$paymentStatus = 'onbekend';
$amount = '';
$description = '';

if ($paymentId) {
    try {
        $payment = $mollie->payments->get($paymentId);
        $paymentStatus = $payment->isPaid() ? 'Geslaagd' : ($payment->isOpen() ? 'In afwachting' : 'Mislukt');
        $amount = $payment->amount->value . ' ' . $payment->amount->currency;
        $description = $payment->description;
    } catch (Exception $e) {
        $paymentStatus = 'Fout bij ophalen betaling';
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Bedankt voor uw betaling</title>
  <link rel="stylesheet" href="/CSS/boeking.css">
</head>
<body>
<header>
  <div class="header-container">
    <a href="/HOME/home_nl.html"><img src="/IMAGES/Test_Logo.png" alt="Property Inspections logo" class="logo"></a>
    <nav>
      <ul>
        <li><a href="/HOME/home_nl.html">Home</a></li> |
        <li><a href="/INFO/info_nl.html">Info</a></li> |
        <li><a href="/FAQ/faqNL.html">FAQ</a></li>
      </ul>
    </nav>
  </div>
</header>
<hr>

<main>
  <section>
    <h1>Bedankt voor uw betaling!</h1>
    <p>Uw betaling is: <strong><?= htmlspecialchars($paymentStatus) ?></strong></p>
    <?php if($amount): ?>
      <p>Bedrag: <?= htmlspecialchars($amount) ?></p>
      <p>Omschrijving: <?= htmlspecialchars($description) ?></p>
    <?php endif; ?>
    <p>We hebben uw boeking ontvangen. U ontvangt binnen 24-48h een bevestigingsmail.</p>
    <a href="/HOME/home_nl.html" class="submit-btn">Terug naar Home</a>
  </section>
</main>

<footer>
  <div class="footer-bottom">
    <p>&copy; 2025 Property Inspections — Alle rechten voorbehouden.</p>
    <p class="footer-small">Voor vragen: <a href="/HOME/home_nl.html#contact">Contacteer ons</a>.</p>
  </div>
</footer>
</body>
</html>
