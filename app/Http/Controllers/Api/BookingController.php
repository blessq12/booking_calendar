<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'client_name' => 'required|string|max:255',
            'client_phone' => 'required|string|max:20',
            'comment' => 'required|string|max:255',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
            'sauna_id' => 'required|exists:saunas,id',
            'prepayment' => 'required|numeric',
            'total_amount' => 'required|numeric',
            'payment_type' => 'required|string|in:cash,card,transfer',
        ]);

        try {
            DB::beginTransaction();

            $client = Client::firstOrCreate(
                ['phone' => $request->client_phone],
                ['name' => $request->client_name]
            );

            $startDate = Carbon::parse($request->start_datetime);
            $endDate = Carbon::parse($request->end_datetime);

            if ($startDate < now()) {
                throw new \Exception('Нельзя создать бронирование на прошедшее время');
            }

            $date = $startDate->format('Y-m-d');

            $booking = \App\Models\Booking::create([
                'sauna_id' => $request->sauna_id,
                'client_id' => $client->id,
                'start_datetime' => $startDate,
                'end_datetime' => $endDate,
                'comment' => $request->comment,
                'price' => $request->total_amount,
                'prepayment' => $request->prepayment,
                'type' => $request->payment_type,
            ]);

            $existingBookings = \App\Models\Booking::where('sauna_id', $request->sauna_id)
                ->where('start_datetime', '>=', $startDate)
                ->where('end_datetime', '<=', $endDate)
                ->get();

            foreach ($existingBookings as $booking) {
                if (
                    ($startDate >= $booking['start'] && $startDate < $booking['end']) ||
                    ($endDate > $booking['start'] && $endDate <= $booking['end']) ||
                    ($startDate <= $booking['start'] && $endDate >= $booking['end'])
                ) {
                    throw new \Exception('Выбранное время пересекается с существующим бронированием');
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Бронирование успешно создано',
                'booking' => $booking
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function index(Request $request)
    {
        $sauna_id = $request->query('sauna_id');
        if (!$sauna_id) {
            return response()->json(['message' => 'Сауна не указана'], 422);
        }

        $startDate = Carbon::parse($request->query('start_date', now()->startOfDay()));
        $endDate = Carbon::parse($request->query('end_date', now()->addDays(30)));

        $bookings = \App\Models\Booking::with(['client', 'sauna'])
            ->where('sauna_id', $sauna_id)
            ->where('start_datetime', '>=', $startDate)
            ->where('end_datetime', '<=', $endDate)
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'title' => 'Бронь: ' . $booking->client->name,
                    'start' => $booking->start_datetime,
                    'end' => $booking->end_datetime,
                    'extendedProps' => [
                        'client_name' => $booking->client->name,
                        'client_phone' => $booking->client->phone,
                        'comment' => $booking->comment,
                        'type' => $booking->type,
                        'price' => $booking->price,
                        'prepayment' => $booking->prepayment,
                        'sauna_id' => $booking->sauna_id
                    ]
                ];
            });

        return response()->json($bookings);
    }

    public function getClientBookings($client_id)
    {
        $client = \App\Models\Client::findOrFail($client_id);

        if (!$client) {
            return response()->json(['message' => 'Клиент не найден'], 422);
        }

        $bookings = [];
        foreach ($client->bookings as $booking) {
            $bookings[] = [
                'id' => $booking->id,
                'sauna' => $booking->sauna->name,
                'date' => $booking->start_datetime,
                'start_time' => $booking->start_datetime,
                'end_time' => $booking->end_datetime,
                'comment' => $booking->comment ?? null,
                'price' => $booking->price,
                'prepayment' => $booking->prepayment,
                'type' => $booking->type
            ];
        }

        return response()->json($bookings);
    }

    public function destroy($bookingId)
    {
        try {
            DB::beginTransaction();

            $booking = \App\Models\Booking::findOrFail($bookingId);
            $booking->delete();

            DB::commit();
            return response()->json(['message' => 'Бронирование успешно удалено']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function update(Request $request, $bookingId)
    {

        $booking = \App\Models\Booking::findOrFail($bookingId);
        try {
            DB::beginTransaction();
            $booking->update([
                'comment' => $request->comment,
                'end_datetime' => $request->end_datetime,
                'payment_type' => $request->payment_type,
                'prepayment' => $request->prepayment,
                'sauna_id' => $request->sauna_id,
                'start_datetime' => $request->start_datetime,
                'total_amount' => $request->total_amount,
                'price' => $request->total_amount,
                'type' => $request->payment_type,
            ]);
            $booking->client->update([
                'name' => $request->client_name,
                'phone' => $request->client_phone,
            ]);
            $booking->save();
            DB::commit();
            return response()->json(['message' => 'Бронирование успешно обновлено']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
