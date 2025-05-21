<?php

namespace App\Http\Controllers\API;

use App\Models\Staff;
use App\Models\Client;
use App\Models\Exercise;
use Illuminate\Http\Request;
use App\Models\ExerciseTransaction;
use App\Http\Controllers\Controller;

class ExerciseTransactionController extends Controller
{
    public function store(Request $request) {
        $validated = $request->validate([
            'client_id' => 'required',
            'exercise_id' => 'required',
            'isMainPlan' => 'required',
            'expire_date' => 'required',
            'transaction_code' => 'required',
        ]);

        $client = Client::find($request->client_id);
        if (!$client) {
            return response()->json([
                'message' => 'Client not found'
            ], 404);
        }

        $exercise = Exercise::find($request->exercise_id);
        if (!$exercise) {
            return response()->json([
                'message' => 'Exercise not found'
            ], 404);
        }
        $price = $exercise->price;

        $instructor = Staff::where('id', $request->instructor_id)->
            where('position_id', '2')->first();

        // if (!$instructor) {
        //     return response()->json([
        //         'message' => 'Instructor not found'
        //     ], 404);
        // }

        $exercise_transaction = ExerciseTransaction::create([
            'client_id' => $client->id,
            'exercise_id' => $exercise->id,
            'instructor_id' => $instructor->id ?? null,
            'isMainPlan' => $request->isMainPlan,
            'expire_date' => $request->expire_date,
            'price' => $price,
            'transaction_code' => $request->transaction_code,
        ]);

        return response()->json([
            'message' => 'Exercise transaction created successfully',
            'data' => $exercise_transaction,
        ], 201);
    }

    public function show(){
        $records = ExerciseTransaction::with(['exercise', 'instructor', 'client'])->get();

        // Group transactions by transaction code
        $groupedRecords = $records->groupBy('transaction_code')->map(function ($transactions, $transactionCode) {
            $firstTransaction = $transactions->first();

            return [
                'transaction_code' => $transactionCode,
                'client_name' => $firstTransaction->client->firstname . ' ' . $firstTransaction->client->lastname,
                'email' => $firstTransaction->client->email,
                'gender' => $firstTransaction->client->gender,
                'address' => $firstTransaction->client->address,
                'contact_no' => $firstTransaction->client->contact_no,
                'transactions' => $transactions->map(function ($transaction) {
                    return [
                        'exercise_name' => $transaction->exercise->name,
                        'tag' => $transaction->exercise->tag,
                        'instructor_name' => optional($transaction->instructor)->firstname . ' ' . optional($transaction->instructor)->lastname,
                        'price' => $transaction->price,
                        'isMainPlan' => $transaction->isMainPlan,
                        'expire_date' => $transaction->expire_date,
                    ];
                }),
                'total_price' => $transactions->sum('price'),
                'created_at' => $firstTransaction->created_at,
            ];
        });

        return response()->json([
            'data' => $groupedRecords->values(),
            'message' => 'Records retrieved successfully'
        ]);
    }

    public function soft_delete_exercise_transaction(Request $request, $transaction_code){
        $transactions = ExerciseTransaction::where('transaction_code', $transaction_code)->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'message' => 'Transaction not found'
            ], 404);
        }

        foreach ($transactions as $transaction) {
            $transaction->delete(); // Soft delete each record
        }

        return response()->json([
            'message' => 'Transactions deleted (soft deleted) successfully'
        ]);
    }

    public function trashed_record_exercise_transaction(){
        $transactions = ExerciseTransaction::onlyTrashed()->with(['exercise', 'instructor', 'client'])->get();

        // Group trashed transactions by transaction code
        $groupedRecords = $transactions->groupBy('transaction_code')->map(function ($transactions, $transactionCode) {
            $firstTransaction = $transactions->first();

            return [
                'transaction_code' => $transactionCode,
                'client_name' => $firstTransaction->client->firstname . ' ' . $firstTransaction->client->lastname,
                'email' => $firstTransaction->client->email,
                'gender' => $firstTransaction->client->gender,
                'address' => $firstTransaction->client->address,
                'contact_no' => $firstTransaction->client->contact_no,
                'transactions' => $transactions->map(function ($transaction) {
                    return [
                        'exercise_name' => $transaction->exercise->name,
                        'tag' => $transaction->exercise->tag,
                        'instructor_name' => $transaction->instructor->firstname . ' ' . $transaction->instructor->lastname,
                        'price' => $transaction->price,
                        'isMainPlan' => $transaction->isMainPlan,
                        'expire_date' => $transaction->expire_date,
                    ];
                }),
                'total_price' => $transactions->sum('price'),
            ];
        });

        return response()->json([
            'data' => $groupedRecords->values(), // Convert to an indexed array
            'message' => 'Trashed records retrieved successfully'
        ]);
    }

    public function restore_record_exercise_transaction(Request $request, $transaction_code){
        $transactions = ExerciseTransaction::onlyTrashed()->where('transaction_code', $transaction_code)->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'message' => 'Transaction not found'
            ], 404);
        }

        foreach ($transactions as $transaction) {
            $transaction->restore();
        }

        return response()->json([
            'message' => 'Transactions restored successfully'
        ]);
    }

    public function force_delete_record_exercise_transaction(Request $request, $transaction_code){
        $transactions = ExerciseTransaction::onlyTrashed()->where('transaction_code', $transaction_code)->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'message' => 'Transaction not found'
            ], 404);
        }

        foreach ($transactions as $transaction) {
            $transaction->forceDelete();
        }

        return response()->json([
            'message' => 'Transactions permanently deleted successfully'
        ]);
    }





}
