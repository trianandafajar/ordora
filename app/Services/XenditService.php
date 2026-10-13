<?php

namespace App\Services;

use App\Models\Order;
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;

class XenditService
{
    public function __construct()
    {
        Configuration::setXenditKey(config('services.xendit.api_key'));
    }

    public function createInvoice(Order $order)
    {
        $apiInstance = new InvoiceApi;
        $rate = (float) env('CURRENCY_RATE', 17913.00);
        $amountInIdr = (int) round($order->total_price * $rate);

        $params = [
            'external_id' => 'ordora_'.$order->order_token,
            'amount' => $amountInIdr,
            'description' => 'Order #'.$order->id,
            'customer' => [
                'given_names' => $order->customer_name,
            ],
            'success_redirect_url' => route('table.tracking.show', $order->order_token),
            'failure_redirect_url' => route('table.tracking.show', $order->order_token),
        ];

        return $apiInstance->createInvoice($params);
    }
}
