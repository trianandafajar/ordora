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

        $params = [
            'external_id' => 'ordora_'.$order->order_token,
            'amount' => (int) $order->total_price,
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
