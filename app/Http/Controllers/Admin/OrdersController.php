<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Checkouts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class OrdersController extends Controller
{
    /**
     * Get all orders
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function allOrders()
    {
        try {
            $orders = Checkouts::select('id', 'user_id', 'metadata', 'amount_total', 'payment_status', 'payment_date', 'customer_name', 'customer_email', 'delivery_status', 'created_at', 'updated_at')
                ->get();

            if (!$orders) {
                return self::notFound('Orders');
            }

            return response()->json([
                'status' => 'success',
                'data' => $orders
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error getting orders: ' . $e->getMessage());
            return self::serverError();
        }
    }

    /**
     * Update the specified order
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int|string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrder(Request $request, $id)
    {
        try {

            $validated = Validator::make($request->all(), [
                'delivery_status' => 'required|in:pending,delivered,completed,cancelled',
            ]);

            $order = Checkouts::find($id);

            if (!$order) {
                return self::notFound('Order');
            }

            if ($validated->fails()) {
                return self::validationFailed($validated->errors()->first());
            }

            $order->update([
                'delivery_status' => $validated->validated()['delivery_status'],
            ]);


            return response()->json([
                'status' => 'success',
                'message' => 'Order updated successfully',
                'data' => $order
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating order: ' . $e->getMessage());
            return self::serverError();
        }
    }

    /**
     * Delete the specified order
     *
     * @param  int|string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteOrder($id)
    {

        try {
            $order = Checkouts::select(['id', 'payment_status', 'delivery_status'])
                ->find($id);

            if (!$order) {
                return self::notFound('Order');
            }

            // Prevent deletion of orders that are already completed or in progress
            if (in_array($order->delivery_status, ['delivered'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete order that is already delivered.'
                ], 422);
            }

            // Use database transaction for data consistency
            \DB::beginTransaction();

            try {
                $order->items()->delete();

                $order->delete();

                \DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Order deleted successfully.',
                ], 200);

            } catch (\Exception $e) {
                \DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            \Log::error('Error deleting order #' . $id . ': ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete order. Please try again later.'
            ], 500);
        }
    }
}