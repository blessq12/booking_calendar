<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        try {
            $query = $request->input('q');

            if (empty($query)) {
                return response()->json([]);
            }
            $bookings = Booking::query()
                ->with('client')
                ->where(function ($q) use ($query) {
                    $q->where('comment', 'like', '%' . $query . '%');
                })
                ->orderBy('start_datetime', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($booking) {
                    return [
                        'id' => $booking->id,
                        'client_name' => $booking->client ? $booking->client->name : 'Нет данных',
                        'client_phone' => $booking->client ? $booking->client->phone : 'Нет данных',
                        'start' => $booking->start_datetime,
                        'total_amount' => $booking->price,
                        'comment' => $booking->comment,
                        'type' => 'booking'
                    ];
                });

            // Поиск по клиентам
            $clients = Client::query()
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', '%' . $query . '%')
                        ->orWhere('phone', 'like', '%' . $query . '%');
                })
                ->with(['bookings' => function ($q) {
                    $q->orderBy('start_datetime', 'desc')->limit(1);
                }])
                ->limit(10)
                ->get()
                ->map(function ($client) {
                    $lastBooking = $client->bookings->first();
                    return [
                        'id' => $client->id,
                        'client_name' => $client->name,
                        'client_phone' => $client->phone,
                        'start' => $lastBooking ? $lastBooking->start_datetime : null,
                        'total_amount' => $lastBooking ? $lastBooking->price : 0,
                        'type' => 'client'
                    ];
                })
                ->filter(function ($client) {
                    return $client !== null;
                });

            $results = $bookings->concat($clients)
                ->sortByDesc('start_datetime')
                ->values()
                ->all();

            return response()->json($results);
        } catch (\Exception $e) {
            Log::error('Search error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Произошла ошибка при поиске'
            ], 500);
        }
    }
}
