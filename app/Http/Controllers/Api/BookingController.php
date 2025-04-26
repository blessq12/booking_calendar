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
            'sauna_id' => 'required|exists:saunas,id',
            'client_name' => 'required|string|max:255',
            'client_phone' => 'required|string|max:20',
            'start' => 'required|date',
            'end' => 'required|date|after:start'
        ]);

        try {
            DB::beginTransaction();

            // Создаем или находим клиента
            $client = Client::firstOrCreate(
                ['phone' => $request->client_phone],
                ['name' => $request->client_name]
            );

            // Получаем дату и время
            $startDate = Carbon::parse($request->start);
            $endDate = Carbon::parse($request->end);

            // Проверяем, что время бронирования не в прошлом
            if ($startDate < now()) {
                throw new \Exception('Нельзя создать бронирование на прошедшее время');
            }

            // Проверяем, что время бронирования в рабочие часы (8:00 - 22:00)
            if (
                $startDate->format('H') < 8 || $startDate->format('H') >= 22 ||
                $endDate->format('H') < 8 || $endDate->format('H') > 22
            ) {
                throw new \Exception('Время бронирования должно быть между 8:00 и 22:00');
            }

            // Проверяем, что продолжительность бронирования не менее 1 часа и не более 12 часов
            $duration = $startDate->diffInHours($endDate, false);
            if ($duration < 1) {
                throw new \Exception('Минимальное время бронирования - 1 час');
            }
            if ($duration > 12) {
                throw new \Exception('Максимальное время бронирования - 12 часов');
            }

            $date = $startDate->format('Y-m-d');

            // Проверяем, что дата не более чем через 3 месяца
            if ($startDate > now()->addMonths(3)) {
                throw new \Exception('Нельзя создать бронирование более чем на 3 месяца вперед');
            }

            // Находим или создаем расписание на этот день
            $schedule = Schedule::firstOrCreate(
                [
                    'sauna_id' => $request->sauna_id,
                    'date' => $date,
                ],
                [
                    'slots' => []
                ]
            );

            // Получаем текущие слоты или создаем новые
            $slots = $schedule->slots ?? [];

            // Проверяем доступность всех слотов в выбранном диапазоне
            $currentTime = $startDate->copy();
            $bookedSlots = [];
            $existingBookings = [];

            // Собираем существующие бронирования
            foreach ($slots as $slot) {
                if ($slot['status'] === 'booked') {
                    $existingBookings[] = [
                        'start' => Carbon::parse($date . ' ' . $slot['time']),
                        'end' => Carbon::parse($date . ' ' . ($slot['booking_end'] ?? $slot['time']))
                    ];
                }
            }

            // Проверяем пересечения с существующими бронированиями
            foreach ($existingBookings as $booking) {
                if (
                    ($startDate >= $booking['start'] && $startDate < $booking['end']) ||
                    ($endDate > $booking['start'] && $endDate <= $booking['end']) ||
                    ($startDate <= $booking['start'] && $endDate >= $booking['end'])
                ) {
                    throw new \Exception('Выбранное время пересекается с существующим бронированием');
                }
            }

            // Создаем слоты для каждого часа бронирования
            while ($currentTime < $endDate) {
                $timeStr = $currentTime->format('H:i:s');

                // Проверяем, существует ли уже слот на это время
                $slotExists = false;
                foreach ($slots as &$slot) {
                    if ($slot['time'] === $timeStr) {
                        if ($slot['status'] === 'booked') {
                            throw new \Exception('Выбранное время уже забронировано');
                        }
                        $slot['status'] = 'booked';
                        $slot['client_id'] = $client->id;
                        $slot['booking_start'] = $startDate->format('H:i:s');
                        $slot['booking_end'] = $endDate->format('H:i:s');
                        $slotExists = true;
                        break;
                    }
                }

                if (!$slotExists) {
                    $slots[] = [
                        'time' => $timeStr,
                        'status' => 'booked',
                        'client_id' => $client->id,
                        'booking_start' => $startDate->format('H:i:s'),
                        'booking_end' => $endDate->format('H:i:s')
                    ];
                }

                $bookedSlots[] = $timeStr;
                $currentTime->addHour();
            }

            // Сортируем слоты по времени
            usort($slots, function ($a, $b) {
                return strcmp($a['time'], $b['time']);
            });

            // Обновляем расписание
            $schedule->slots = $slots;
            $schedule->save();

            DB::commit();

            return response()->json([
                'message' => 'Бронирование успешно создано',
                'schedule' => $schedule
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
