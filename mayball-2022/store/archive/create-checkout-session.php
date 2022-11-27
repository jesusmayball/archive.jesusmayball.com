<?php

require './vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

\Stripe\Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
  echo 'Invalid request';
  exit;
}

$domain_url = $_ENV['DOMAIN'];
$items_json = $_POST["basket-data"];
$items = json_decode($items_json, true);

if (sizeof($items) == 0) {
  echo 'No items in basket';
  exit;
}
$line_items = array();
foreach ($items as $item) {
  if (isset($item["id"]) && isset($item["quantity"])) {
    array_push(
      $line_items,
      [
        'price' => $item["id"],
        'adjustable_quantity' => [
          'enabled' => true
        ],
        'quantity' => $item["quantity"],
      ]
    );
  }
}

echo (json_encode($line_items));

// Create new Checkout Session for the order
// Other optional params include:
// [billing_address_collection] - to display billing address details on the page
// [customer] - if you have an existing Stripe Customer ID
// [customer_email] - lets you prefill the email input in the form
// [automatic_tax] - to automatically calculate sales tax, VAT and GST in the checkout page
// For full details see https://stripe.com/docs/api/checkout/sessions/create
// ?session_id={CHECKOUT_SESSION_ID} means the redirect will have the session ID set as a query param
$checkout_session = \Stripe\Checkout\Session::create([
  'success_url' => $domain_url . '/success.html?session_id={CHECKOUT_SESSION_ID}',
  'cancel_url' => $domain_url . '/canceled.html',
  'automatic_tax' => ['enabled' => true],
  'line_items' => $line_items,
  'mode' => 'payment',
  'allow_promotion_codes' => true,
]);

header("HTTP/1.1 303 See Other");
header("Location: " . $checkout_session->url);
