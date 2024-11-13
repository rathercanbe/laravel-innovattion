<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\WorkTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class WorkTimeController extends Controller
{
    /**
     * Rejestruje czas pracy dla pracownika.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // Walidacja danych wejściowych
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $startTime = new Carbon($request->start_time);
        $endTime = new Carbon($request->end_time);
        $hoursWorked = $startTime->diffInHours($endTime);

        // Sprawdzenie, czy czas pracy przekracza 12 godzin
        if ($hoursWorked > 12) {
            return response()->json(['error' => 'Maksymalnie 12 godzin w jednym przedziale'], 422);
        }

        // Sprawdzenie, czy istnieje już czas pracy dla tego samego dnia
        $existingWorkTime = WorkTime::where('employee_id', $request->employee_id)
            ->whereDate('start_day', $startTime->toDateString())
            ->exists();

        if ($existingWorkTime) {
            return response()->json(['error' => 'Pracownik już ma zarejestrowany czas pracy dla tego dnia'], 422);
        }

        // Tworzenie wpisu czasu pracy
        $workTime = WorkTime::create([
            'id' => Str::uuid(),
            'employee_id' => $request->employee_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'start_day' => $startTime->toDateString(),
        ]);

        return response()->json(['message' => 'Czas pracy został dodany!'], 201);
    }

    /**
     * Generuje podsumowanie czasu pracy dla danego dnia lub miesiąca.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function summary(Request $request)
    {
        // Walidacja parametrów wejściowych
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date_format:Y-m-d|date_format:Y-m',
        ]);

        $employeeId = $request->employee_id;
        $date = $request->date;

        // Ustalenie, czy chodzi o podsumowanie dnia czy miesiąca
        $isMonthSummary = strlen($date) === 7;
        $query = WorkTime::where('employee_id', $employeeId);

        if ($isMonthSummary) {
            // Podsumowanie dla miesiąca
            $query->whereBetween('start_day', [
                Carbon::parse($date)->startOfMonth(),
                Carbon::parse($date)->endOfMonth()
            ]);
        } else {
            // Podsumowanie dla dnia
            $query->whereDate('start_day', $date);
        }

        $workTimes = $query->get();

        // Inicjalizacja zmiennych do sumowania czasu pracy
        $totalHours = 0;
        foreach ($workTimes as $workTime) {
            $startTime = new Carbon($workTime->start_time);
            $endTime = new Carbon($workTime->end_time);
            $hours = $startTime->diffInMinutes($endTime) / 60;

            // Zaokrąglenie czasu pracy do najbliższych 30 minut
            $roundedHours = round($hours * 2) / 2;
            $totalHours += $roundedHours;
        }

        // Obliczenie wynagrodzenia
        $regularHours = min(env('NORMA_GODZIN'), $totalHours);
        $overtimeHours = max(0, $totalHours - $regularHours);
        $totalPayment = $regularHours * env('STAWKA') + $overtimeHours * env('STAWKA_NADGODZINOWA');

        // Zwrot danych w zależności od typu podsumowania
        if ($isMonthSummary) {
            return response()->json([
                'regular_hours' => $regularHours,
                'overtime_hours' => $overtimeHours,
                'total_payment' => $totalPayment,
            ]);
        } else {
            return response()->json([
                'daily_hours' => $totalHours,
                'total_payment' => $totalPayment,
            ]);
        }
    }
}
