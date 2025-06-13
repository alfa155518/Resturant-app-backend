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
                return PaymentMethods::first();
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

            $paymentMethods = PaymentMethods::first();

            if (!$paymentMethods) {
                return self::notFound('Payment methods not found');
            }

            $validator = Validator::make($request->all(), PaymentMethods::$rules);

            if ($validator->fails()) {
                return self::validationFailed($validator->errors()->first());
            }

            $validatedData = $validator->validated();

            $paymentMethods->update($validatedData);

            PaymentMethods::clearPaymentMethodsCache();

            $response = response()->json([
                'status' => 'success',
                'data' => $paymentMethods,
            ]);
            return $this->adminSecurityHeaders($response);
        } catch (\Exception $e) {
            return self::serverError();
        }
    }
}
