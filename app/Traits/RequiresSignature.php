<?php

namespace App\Traits;

use Creagia\LaravelSignPad\Signature;

trait RequiresSignature
{
    public function sign()
    {
        return $this->morphOne(Signature::class, 'model');
    }

    public function getSignatureRoute(): string
    {
        return route('doctor.sign-pad.store', ['token' => md5(config('app.key').get_class($this))]);
    }

    public function hasBeenSigned(): bool
    {
        return ! is_null($this->signature);
    }

    public function deleteSignature(): bool
    {
        return $this->sign?->delete() ?? false;
    }
}
