<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'sauna_id' => 'required|exists:saunas,id',
            'start_date' => 'date',
            'end_date' => 'date|after_or_equal:start_date'
        ]);

        $query = Booking::with(['client'])
            ->where('sauna_id', $request->sauna_id);

        if ($request->has('start_date')) {
            $query->where('start_datetime', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('end_datetime', '<=', $request->end_date);
        }

        $bookings = $query->get();

        return response()->json($bookings);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sauna_id' => 'required|exists:saunas,id',
            'client_name' => 'required|string|max:255',
            'client_phone' => 'required|string|max:20',
            'start_datetime' => 'required|date_format:Y-m-d H:i:s',
            'end_datetime' => 'required|date_format:Y-m-d H:i:s|after:start_datetime',
            'comment' => 'nullable|string'
        ]);

        // Проверяем, нет ли пересечений с существующими бронями
        $conflictingBookings = Booking::where('sauna_id', $request->sauna_id)
            ->where(function($query) use ($request) {
                $query->whereBetween('start_datetime', [$request->start_datetime, $request->end_datetime])
                    ->orWhereBetween('end_datetime', [$request->start_datetime, $request->end_datetime])
                    ->orWhere(function($q) use ($request) {
                        $q->where('start_datetime', '<=', $request->start_datetime)
                            ->where('end_datetime', '>=', $request->end_datetime);
                    });
            })->exists();

        if ($conflictingBookings) {
            return response()->json(['message' => 'Выбранное время уже занято'], 422);
        }

        try {
            DB::beginTransaction();

            // Создаем или находим клиента
            $client = Client::firstOrCreate(
                ['phone' => $request->client_phone],
                ['name' => $request->client_name]
            );

            // Создаем бронирование
            $booking = Booking::create([
                'sauna_id' => $request->sauna_id,
                'client_id' => $client->id,
                'start_datetime' => $request->start_datetime,
                'end_datetime' => $request->end_datetime,
                'comment' => $request->comment
            ]);

            DB::commit();
            return response()->json($booking->load('client'));

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Ошибка при создании бронирования: ' . $e->getMessage());
            return response()->json(['message' => 'Ошибка при создании бронирования'], 500);
        }
    }

    public function destroy(Booking $booking)
    {
        try {
            $booking->delete();
            return response()->json(['message' => 'Бронирование успешно удалено']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Ошибка при удалении бронирования'], 500);
        }
    }

    public function getClientBookings(Client $client)
    {
        $bookings = $client->bookings()
            ->with('sauna')
            ->orderBy('start_datetime', 'desc')
            ->get();

        return response()->json($bookings);
    }
}
