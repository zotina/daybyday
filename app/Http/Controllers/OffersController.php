<?php
namespace App\Http\Controllers;
use App\Models\Lead;
use App\Models\Offer;
use Ramsey\Uuid\Uuid;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Setting;
use App\Enums\OfferStatus;
use App\Models\InvoiceLine;
use App\Enums\InvoiceStatus;
use App\Services\InvoiceNumber\InvoiceNumberService;
use Illuminate\Http\Request;

class OffersController extends Controller
{
    public function getOfferInvoiceLinesJson(Offer $offer)
    {
        return $offer->invoiceLines()->with(['product' => function ($q) {
            $q->select('id', 'external_id', 'name');
        }])->get(['title', 'comment', 'price', 'quantity', 'type', 'product_id']);
    }

    public function update(Request $request, Offer $offer)
    {
        $offer->invoiceLines()->forceDelete();
        foreach ($request->all() as $line) {
            if (!$line["title"] || !$line["type"] || !$line["price"] || !$line["quantity"]) {
                return response("missing fields", 422);
            }
            
            $invoiceLine = InvoiceLine::make([
                'title' => $line["title"],
                'type' => $line["type"],
                'quantity' => $line["quantity"] ?: 1,
                'comment' => $line["comment"],
                'price' => $line["price"] * 100,
                'product_id' => $line["product"] ? Product::whereExternalId($line["product"])->first()->id : null
            ]);
            
            $offer->invoiceLines()->save($invoiceLine);
        }
    }

    public function create(Request $request, Lead $lead)
    {
        $offer = Offer::create([
            'status' => OfferStatus::inProgress()->getStatus(),
            'client_id' => $lead->client_id,
            'external_id' => Uuid::uuid4()->toString(),
            'source_id' => $lead->id,
            'source_type' => Lead::class,
            'status' => OfferStatus::inProgress()->getStatus()
        ]);
        
        foreach ($request->all() as $line) {
            if (!$line["title"] || !$line["type"] || !$line["price"] || !$line["quantity"]) {
                return response("missing fields", 422);
            }
            
            $invoiceLine = InvoiceLine::make([
                'title' => $line["title"],
                'type' => $line["type"],
                'quantity' => $line["quantity"] ?: 1,
                'comment' => $line["comment"],
                'price' => $line["price"] * 100,
                'product_id' => $line["product"] ? Product::whereExternalId($line["product"])->first()->id : null
            ]);
            
            $offer->invoiceLines()->save($invoiceLine);
        }
        
        return response("OK");
    }

    public function won(Request $request)
    {
        $offer = Offer::whereExternalId($request->get('offer_external_id'))->with('invoiceLines')->firstOrFail();
        $offer->setAsWon();
            
        $invoice = Invoice::create($offer->toArray());
        $invoice->offer_id = $offer->id;
        $invoice->invoice_number = app(InvoiceNumberService::class)->setNextInvoiceNumber();
        $invoice->status = InvoiceStatus::draft()->getStatus();
        $invoice->save();
        
        $lines = $offer->invoiceLines;
        $newLines = collect();
        
        
        $setting = Setting::first();
        $remise = $setting->remise ?? 0;
        
        foreach ($lines as $invoiceLine) {
            $invoiceLine->offer_id = null;
            
            
            $newLine = InvoiceLine::make($invoiceLine->toArray());
            
            
            if ($remise > 0) {
                
                $prixOriginal = $newLine->price;
                $prixAvecRemise = $prixOriginal * (1 - ($remise / 100));
                $newLine->price = $prixAvecRemise;
                
                
                $infoRemise = " (Remise de {$remise}% appliquée)";
                $newLine->comment = ($newLine->comment ? $newLine->comment . $infoRemise : "Remise de {$remise}% appliquée");
            }
            
            $newLines->push($newLine);
        }
        
        $invoice->invoiceLines()->saveMany($newLines);
        
        return redirect()->back();
    }

    public function lost(Request $request)
    {
        $offer = Offer::whereExternalId($request->get('offer_external_id'))->firstOrFail();
        $offer->setAsLost();
        
        return redirect()->back();
    }
}