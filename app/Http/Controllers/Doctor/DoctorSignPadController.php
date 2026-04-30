<?php

declare(strict_types=1);

namespace App\Http\Controllers\Doctor;

use App\Models\Doctor;
use Creagia\LaravelSignPad\Actions\GenerateSignatureDocumentAction;
use Creagia\LaravelSignPad\Contracts\ShouldGenerateSignatureDocument;
use Creagia\LaravelSignPad\Signature;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

final class DoctorSignPadController extends BaseController
{
    use ValidatesRequests;

    public function __invoke(Request $request, GenerateSignatureDocumentAction $generateSignatureDocumentAction): RedirectResponse
    {
        $validatedData = $this->validate($request, [
            'sign' => ['required', 'string'],
        ]);

        /** @var Doctor $doctor */
        $doctor = auth()->guard('doctor')->user();

        $parts = explode(',', $validatedData['sign'], 2);
        $decodedImage = isset($parts[1]) ? base64_decode($parts[1]) : false;

        if ($decodedImage === false || $decodedImage === '') {
            abort(422, 'Invalid signature payload.');
        }

        $uuid = Str::uuid()->toString();
        $filename = "{$uuid}.png";

        if ($doctor->sign) {
            $doctor->deleteSignature();
        }

        $signature = Signature::create([
            'model_type' => Doctor::class,
            'model_id' => $doctor->id,
            'uuid' => $uuid,
            'from_ips' => $request->ips(),
            'filename' => $filename,
            'certified' => config('sign-pad.certify_documents'),
        ]);

        Storage::disk(config('sign-pad.disk_name'))->put($signature->getSignatureImagePath(), $decodedImage);

        if ($doctor instanceof ShouldGenerateSignatureDocument) {
            try {
                ($generateSignatureDocumentAction)(
                    $signature,
                    $doctor->getSignatureDocumentTemplate(),
                    $decodedImage
                );
            } catch (Throwable) {
                // Optional document generation
            }
        }

        return redirect()->route(config('sign-pad.redirect_route_name'));
    }
}
