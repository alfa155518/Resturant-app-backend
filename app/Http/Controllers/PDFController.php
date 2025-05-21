<?php

namespace App\Http\Controllers;


use App\Models\ReservationCheckouts;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PDFController extends Controller
{
    /**
     * Generate and download a PDF for a reservation checkout
     *
     * @param int $reservationCheckoutId
     * @return \Illuminate\Http\Response
     */
    public function downloadReservationCheckoutPdf($reservationCheckoutId)
    {
        try {
            if (!is_numeric($reservationCheckoutId)) {
                throw new \InvalidArgumentException('Invalid reservation checkout ID');
            }

            $reservationCheckout = ReservationCheckouts::findOrFail($reservationCheckoutId);

            // Format reservation time with Carbon (Egypt timezone)
            $reservationTime = Carbon::parse($reservationCheckout->metadata['reservation_time'])
                ->timezone('Africa/Cairo')
                ->format('l, F j, Y \a\t g:i A');

            $data = [
                'id' => $reservationCheckout->id,
                'payment_status' => ucfirst($reservationCheckout->payment_status),
                'reservation_id' => $reservationCheckout->metadata['reservation_id'],
                'reservation_time' => $reservationTime,
                'guest_count' => $reservationCheckout->metadata['guest_count'],
                'payment_date' => Carbon::parse($reservationCheckout->payment_date)->timezone('Africa/Cairo')->format('F j, Y \a\t g:i A'),
                'table_id' => $reservationCheckout->metadata['table_id'],
                'payment_method' => ucfirst($reservationCheckout->payment_method),
                'table_name' => "T00" . $reservationCheckout->metadata['table_id']
            ];

            // Add logo if it exists
            $logoPath = public_path('images/logo.png');
            if (Storage::exists($logoPath)) {
                $data['logoData'] = base64_encode(Storage::get($logoPath));
            }

            $pdf = Pdf::loadView('pdf.downloadUserReservationCheckout-pdf', $data);
            $pdf->setPaper('a4');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'isRemoteEnabled' => true,
                'dpi' => 150,
                'defaultFont' => 'sans-serif'
            ]);

            $filename = 'reservation-checkout-' . $reservationCheckout->id . '.pdf';
            return $pdf->download($filename);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Reservation checkout not found: ' . $reservationCheckoutId);
            return response()->json(['error' => 'Reservation checkout not found'], 404);

        } catch (\InvalidArgumentException $e) {
            Log::error('Invalid argument: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 400);

        } catch (\Exception $e) {
            Log::error('Error generating PDF: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to generate PDF'], 500);
        }
    }
}