<?php
require __DIR__ . '/config/bootstrap.php';

$app = \App\Application::getInstance();
$app->boot();
$app->getContainer()->getDefinition(\App\Taxes\TaxCalculator::class)->setPublic(true);
$app->start();

/**
 * @var App\Taxes\TaxCalculator $taxCalculator
 */
$taxCalculator = $app->getContainer()->get(\App\Taxes\TaxCalculator::class);
$filename = $argv[1];
if (!\file_exists($filename)) {
    return;
}
$file =  new \SplFileObject($filename);
foreach ($file as $line_num => $line) {
    $data = json_decode($line, true);
    $transaction  = new \App\Taxes\Entity\Transaction();
    $transaction
        ->setBin((string)$data['bin'])
        ->setAmount((float)$data['amount'])
        ->setCurrency((string)$data['currency']);

    $tax = $taxCalculator->calculate($transaction);
    print \number_format((int)$tax + \ceil(($tax - (int)$tax)*100)/100, 2);
    print PHP_EOL;
}
