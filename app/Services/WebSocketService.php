<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebSocketService
{
    protected $baseUrl;
    protected $token;

    public function __construct()
    {
        $this->baseUrl = config('services.websocket.url');
        $this->token = config('services.websocket.token');
    }

    public function sendNewOrder($order)
    {
        return $this->send('/order', [
            'event' => 'new_order',
            'restaurant_id' => $order->restaurant_id,
            'order_id' => $order->id,
            'order' => $order,
        ]);
    }

    public function sendOrderUpdated($orderId, $restaurantId, $status)
    {
        return $this->send('/order-status', [
            'event' => 'order_updated',
            'restaurant_id' => $restaurantId,
            'order_id' => $orderId,
            'status' => $status,
        ]);
    }
    public function sendOrderUpdatedAll($orderId, $restaurantId, $order)
    {
        return $this->send('/order-status', [
            'event' => 'order_updated',
            'restaurant_id' => $restaurantId,
            'order_id' => $orderId,
            'order' => $order,
        ]);
    }

    private function send($endpoint, $data)
    {
        try {
            Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'Accept' => 'application/json',
            ])->post($this->baseUrl . $endpoint, $data);
        } catch (\Exception $e) {
            Log::error("WebSocket Error: " . $e->getMessage());
        }
    }
}
