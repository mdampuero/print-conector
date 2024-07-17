<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: POST");

require __DIR__ . '/vendor/autoload.php';

use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\CapabilityProfile;

try {
    $json = file_get_contents('php://input');
    $body = json_decode($json, true);
    if(!$body){
        exit();
    }
    $connector = new WindowsPrintConnector("POS-80");
   // $connector = new FilePrintConnector("output.txt");
    $printer = new Printer($connector);

    $data = [
        'name' => 'GUAPA JOYAS',
        'address' => 'Manuel Altolaguirre 20, Local 01, 29003',
        'phone' => 'WhatsApp: +34 623480330',
        'fantasy' => 'Fabiola Gisella Arias',
        'nif' => 'NIF: Z1674602T',
        'signed' => 'Gracias por su visita'
    ];

    $paylod = [
        "ticket" => [
            "nomenclator" => "A",
            "number" => "1",
        ],
        "user" => "Gisella",
        "items" => [
            [
                "name" => "Coso 1",
                "price" => 2.5,
                "amount" => 1
            ],
            [
                "name" => "Coso 2",
                "price" => 5,
                "amount" => 2
            ],
            [
                "name" => "Coso 3",
                "price" => 2.5985,
                "amount" => 2
            ]
        ],
        "total" => 17.70
    ];

    $paylod = $body;

    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->setTextSize(2, 2);
    $printer->text($data["name"] . "\n\n");
    $printer->setTextSize(1, 1);
    $printer->text($data["address"] . "\n");
    $printer->text($data["fantasy"] . "\n");
    $printer->text($data["nif"] . "\n");
    $printer->text($data["phone"] . "\n\n");
    $printer->text("==============================\n\n");
    $printer->setJustification(Printer::JUSTIFY_LEFT);
    $printer->text("Ticket: " . str_pad($paylod["ticket"]["nomenclator"], 4, "0", STR_PAD_LEFT) . "-" . str_pad($paylod["ticket"]["number"], 8, "0", STR_PAD_LEFT) . "\n");
    $printer->text("Fecha y hora: " . date("d/m/Y H:i") . "\n");
    $printer->text("Atendido por: " . $paylod["user"] . "\n\n");
    $printer->text("Nombre                       Precio  Un     Subt\n");
    $printer->text("------------------------------------------------\n");
    foreach ($paylod["items"] as $item) {
        $nameTruncate = substr($item["name"], 0, 26);
        $nameComplete = str_pad($nameTruncate, 26, " ");
        $priceComplete = str_pad("$" . number_format($item["price"], 2), 9, " ", STR_PAD_LEFT);
        $amountComplete = str_pad($item["amount"], 4, " ", STR_PAD_LEFT);
        $subtotalComplete = str_pad("$" . number_format(($item["price"] * $item["amount"]), 2), 9, " ", STR_PAD_LEFT);
        $printer->text($nameComplete . $priceComplete . $amountComplete . $subtotalComplete . "\n");
    }
    $printer->text("------------------------------------------------\n");
    $printer->setJustification(Printer::JUSTIFY_RIGHT);
    $printer->setEmphasis(true);
    $totalComplete= "TOTAL  $" . number_format($paylod["total"], 2);
    $total = str_pad($totalComplete, 48, " ",STR_PAD_LEFT);
    $printer->text($total);
    $printer->setEmphasis(false);
    $printer->text("\n\n");
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->text("==============================\n\n");
    $printer->text($data["signed"]);
    $printer->text("\n\n\n");
    $printer->cut();

    $printer->close();

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
