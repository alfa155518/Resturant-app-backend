<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\PaymentMethods;
use App\Traits\AdminSecurityHeaders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;


class PaymentMethodsController extends Controller
{
    use AdminSecurityHeaders;

    /**
     * get payment methods
     *
     * @return mixed
     */
    public function getPaymentMethods()
    {
        try {

            $paymentMethods = Cache::rememberForever('paymentMethods', function () {
                return PaymentMethods::all();
            });
            if (!$paymentMethods) {
                return self::notFound('Payment methods not found');
            }
            $response = response()->json([
                'status' => 'success',
                'data' => $paymentMethods,
            ]);
            return $this->adminSecurityHeaders($response);
        } catch (\Exception $e) {
            return self::serverError();
        }
    }

    /**
     * update payment methods
     *
     * @param Request $request
     * @return mixed
     */
    public function updatePaymentMethods(Request $request)
    {
        try {
            // Validation
            $validator = Validator::make($request->all(), PaymentMethods::$rules);
            if ($validator->fails()) {
                return self::validationFailed($validator->errors()->first());
            }

            // Get validated payment_methods array
            $validatedData = $validator->validated()['payment_methods'];

            // Update each payment method
            foreach ($validatedData as $methodData) {
                $paymentMethod = PaymentMethods::find($methodData['id']);

                if (!$paymentMethod) {
                    return self::notFound("Payment method");
                }

                $paymentMethod->update([
                    'name' => $methodData['name'],
                    'enabled' => $methodData['enabled'],
                ]);
            }

            // Clear cache
            PaymentMethods::clearPaymentMethodsCache();

            // Return response
            $response = response()->json([
                'status' => 'success',
                'message' => 'Payment methods updated successfully',
            ]);

            return $this->adminSecurityHeaders($response);
        } catch (\Exception $e) {
            return self::serverError();
        }
    }
}
