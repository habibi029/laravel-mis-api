<?php

namespace App\Http\Controllers\API;

use App\Models\Client;
use App\Models\Exercise;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ExerciseTransaction;
use App\Http\Controllers\Controller;

class AdminDashboardController extends Controller
{

    public function index()
    {
        $currentYear = now()->year;
        $currentWeek = now()->weekOfYear;
    
        $totalSales = Client::with('exerciseTransactions.exercise')->get();
        $salesByExercise = $totalSales->flatMap(function ($client) {
            return $client->exerciseTransactions->map(function ($transaction) {
                return $transaction->exercise;
            });
        })->groupBy('id')->map(function ($exercises) {
            return [
                'id' => $exercises->first()->id,
                'name' => $exercises->first()->name,
                'sales' => $exercises->sum(function ($exercise) {
                    return $exercise->price;
                }),
            ];
        });

        $totalGender = [
            'male' => $totalSales->where('gender', 'male')->count(),
            'female' => $totalSales->where('gender', 'female')->count(),
        ];
    
        // Calculate total sales count
        $totalSalesCount = $salesByExercise->sum('sales');
    
        // Calculate session and monthly customer counts
        $sessionCount = $totalSales->map(function ($client) {
            return $client->exerciseTransactions->pluck('exercise.tag')->contains('session') ? 1 : 0;
        })->sum();
    
        $monthlyCount = $totalSales->map(function ($client) {
            return $client->exerciseTransactions->pluck('exercise.tag')->contains('monthly') ? 1 : 0;
        })->sum();
    
        // ** Monthly sales calculation **
        $months = [
            "Jan", "Feb", "Mar", "Apr", "May", "Jun",
            "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
        ];
    
        $transactions = ExerciseTransaction::with('exercise')
            ->whereYear('created_at', $currentYear)
            ->get();
    
        $monthlySales = array_fill(0, 12, []);
    
        foreach ($transactions as $transaction) {
            $monthIndex = $transaction->created_at->month - 1;
            $exerciseName = $transaction->exercise->name;
    
            if (!isset($monthlySales[$monthIndex][$exerciseName])) {
                $monthlySales[$monthIndex][$exerciseName] = 0;
            }
    
            $monthlySales[$monthIndex][$exerciseName] += $transaction->exercise->price;
        }
    
        $formattedMonthlySales = [];
        foreach ($months as $index => $month) {
            $formattedMonthlySales[] = [
                'month' => $month,
                'sales' => $monthlySales[$index] ?? []
            ];
        }
    
        // ** Daily sales calculation **
        $daysOfWeek = [
            "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"
        ];
    
        $transactionsThisWeek = ExerciseTransaction::with('exercise')
            ->whereYear('created_at', $currentYear)
            ->where(DB::raw('WEEKOFYEAR(created_at)'), $currentWeek)
            ->get();
    
        $dailySales = array_fill(0, 7, []);
    
        foreach ($transactionsThisWeek as $transaction) {
            $dayIndex = $transaction->created_at->dayOfWeekIso - 1;
            $exerciseName = $transaction->exercise->name;
    
            if (!isset($dailySales[$dayIndex][$exerciseName])) {
                $dailySales[$dayIndex][$exerciseName] = 0;
            }
    
            $dailySales[$dayIndex][$exerciseName] += $transaction->exercise->price;
        }
    
        $formattedDailySales = [];
        foreach ($daysOfWeek as $index => $day) {
            $formattedDailySales[] = [
                'day' => $day,
                'sales' => $dailySales[$index] ?? []
            ];
        }
    
        $response = [
            'Sales_exercise' => $salesByExercise->values(),
            'total_gender' => [
                [
                    'male' => $totalGender['male'],
                    'female' => $totalGender['female'],
                ]
            ],
            'total_sales' => $totalSalesCount,
            'monthly_customer' => $monthlyCount,
            'session_customer' => $sessionCount,
            'monthly_sales' => $formattedMonthlySales,
            'daily_sales' => $formattedDailySales
        ];
    
        return response()->json($response);
    }
}
