<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Client;
use App\Models\Inventory;
use App\Models\StaffCart;
use App\Models\StaffOrder;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\StaffOrderItem;
use Illuminate\Support\Facades\DB;

class ProductTransactionController extends Controller
{
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:inventories,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $transactionCode = Str::random(12);

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            $orderItems = [];

            foreach ($validated['items'] as $item) {
                $product = Inventory::find($item['product_id']);

                if (!$product) {
                    throw new Exception("Product with ID {$item['product_id']} not found");
                }

                $basePrice = $product->price;
                $quantity = $item['quantity'];

                if ($product->quantity < $quantity) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }

                $price = $basePrice * $quantity;
                $totalAmount += $price;

                $orderItems[] = [
                    'inventory_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                ];

                $product->decrement('quantity', $quantity);
            }

            $order = StaffOrder::create([
                'transaction_code' => $transactionCode,
                'total_amount' => $totalAmount,
                'status' => 'pending',
            ]);

            foreach ($orderItems as $item) {
                StaffOrderItem::create([
                    'order_id' => $order->id,
                    'inventory_id' => $item['inventory_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Checkout successful',
                'transaction_code' => $order->transaction_code,
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function show()
    {
        $orders = StaffOrder::with(['items'])->get(); // Eager load items

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    public function soft_delete_product_transaction(Request $request, $id)
    {
        // Find the transaction by ID

        $transaction = StaffOrder::where('id', $id)->first();
        // Check if the transaction exists
        if (!$transaction) {
            return response()->json([
                'message' => 'Transaction not found'
            ], 404);
        }

        // Soft delete the transaction
        $transaction->delete();

        return response()->json([
            'message' => 'Transaction deleted successfully',
            'data' => $transaction
        ]);
    }

    public function restore_product_transaction(Request $request, $id)
    {
        // Retrieve the soft-deleted transaction
        $transaction = StaffOrder::onlyTrashed()->where('id', $id)->first();

        if (!$transaction) {
            return response()->json([
                'message' => 'Transaction not found'
            ], 404);
        }

        // Restore the transaction (this will also restore related items)
        $transaction->restore();

        return response()->json([
            'message' => 'Transaction and related items restored successfully',
            'data' => $transaction
        ]);
    }

    public function force_delete_product_transaction(Request $request, $id)
    {
        // Retrieve the transaction, including soft-deleted records
        $transaction = StaffOrder::onlyTrashed()->where('id', $id)->first();

        if (!$transaction) {
            return response()->json([
                'message' => 'Transaction not found'
            ], 404);
        }

        // Force delete the transaction and its related items
        $transaction->forceDelete();

        return response()->json([
            'message' => 'Transaction and related items permanently deleted successfully',
        ]);
    }

    public function trashed_record_exercise_transaction(){
        $orders = StaffOrder::onlyTrashed()
            ->with(['client', 'items']) // This will now include trashed items in the 'items' relation
            ->get();

            $formattedOrders = $orders->groupBy('order_id')->map(function ($ordersGroup) {
                // Get the first order instance to access general order details
                $order = $ordersGroup->first();

                $totalAmount = $ordersGroup->pluck('items') // Collect all related items
                    ->flatten() // Flatten the nested collections
                    ->sum(function ($item) {
                        return $item->price * $item->quantity; // Sum up total amount
                    });

                return [
                    'order_id' => $order->id,
                    'client_id' => $order->client->id,
                    'client_name' => $order->client->firstname . ' ' . $order->client->lastname,
                    'client_email' => $order->client->email,
                    'gender' => $order->client->gender,
                    'contact_no' => $order->client->contact_no,
                    'total_amount' => $totalAmount,
                    'status' => $order->status,
                    'Items' => $ordersGroup->pluck('items') // Get all items
                        ->flatten() // Flatten the nested collections
                        ->map(function ($item) {
                            return [
                                'item_name' => $item->product->name,
                                'item_description' => $item->product->short_description,
                                'base_price' => $item->price,
                                'price' => $item->price * $item->quantity,
                                'quantity' => $item->quantity,
                            ];
                        }),
                    'transaction_date' => $order->created_at->format('Y-m-d'),
                ];
            });

            return $formattedOrders->values();
    }






}
