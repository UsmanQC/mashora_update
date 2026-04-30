<?php

declare(strict_types=1);

namespace App\Filament\Doctor\Resources\Profile\Pages;

use App\Filament\Doctor\Pages\DoctorDashboard;
use App\Filament\Doctor\Resources\Profile\DoctorProfileResource;
use App\Models\Doctor;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Component as SchemaComponent;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

final class EditDoctorProfile extends EditRecord
{
    protected static string $resource = DoctorProfileResource::class;

    /** @var array{account_holder_name: string, account_number: string, iban_number: string}|null */
    protected ?array $pendingBankAccount = null;

    protected function authorizeAccess(): void
    {
        parent::authorizeAccess();

        abort_unless((int) $this->getRecord()->getKey() === (int) Filament::auth()->id(), 403);
    }

    public function getTitle(): string|Htmlable
    {
        return __('Profile');
    }

    public function defaultForm(Schema $schema): Schema
    {
        return parent::defaultForm($schema)->columns(1);
    }

    public function saveFormComponentOnly(SchemaComponent $component): void
    {
        parent::saveFormComponentOnly($component);

        Notification::make()
            ->success()
            ->title(__('Saved'))
            ->send();
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getCancelFormAction(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Doctor $doctor */
        $doctor = $this->getRecord();
        $bank = $doctor->bankAccount;

        return [
            ...$data,
            'bank_account_holder_name' => $bank?->account_holder_name ?? '',
            'bank_account_number' => $bank?->account_number ?? '',
            'bank_iban_number' => $bank?->iban_number ?? '',
            'current_password' => '',
            'new_password' => '',
            'new_password_confirmation' => '',
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->pendingBankAccount = null;

        $bankKeys = ['bank_account_holder_name', 'bank_account_number', 'bank_iban_number'];
        $hasBankField = collect($bankKeys)->contains(fn (string $key): bool => array_key_exists($key, $data));

        if ($hasBankField) {
            $this->pendingBankAccount = [
                'account_holder_name' => $data['bank_account_holder_name'] ?? '',
                'account_number' => $data['bank_account_number'] ?? '',
                'iban_number' => $data['bank_iban_number'] ?? '',
            ];

            foreach ($bankKeys as $key) {
                unset($data[$key]);
            }
        }

        if (! array_key_exists('new_password', $data) || ! filled($data['new_password'])) {
            unset($data['current_password'], $data['new_password'], $data['new_password_confirmation']);
        } else {
            validator([
                'current_password' => $data['current_password'] ?? '',
                'new_password' => $data['new_password'],
                'new_password_confirmation' => $data['new_password_confirmation'] ?? '',
            ], [
                'current_password' => ['required', 'current_password:doctor'],
                'new_password' => ['required', Password::defaults(), 'confirmed'],
            ])->validate();

            $data['password'] = Hash::make($data['new_password']);
            unset($data['current_password'], $data['new_password'], $data['new_password_confirmation']);
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Doctor $record */
        if ($data !== []) {
            $record->update($data);
        }

        if ($this->pendingBankAccount !== null) {
            $record->bankAccount()->updateOrCreate(
                ['doctor_id' => $record->id],
                $this->pendingBankAccount,
            );
            $this->pendingBankAccount = null;
        }

        return $record;
    }

    public function switchLocale(string $locale): void
    {
        if (! in_array($locale, ['en', 'ar'], true)) {
            return;
        }

        session(['locale' => $locale]);
        app()->setLocale($locale);

        $this->redirect(DoctorProfileResource::getUrl('edit', [
            'record' => Filament::auth()->id(),
        ], panel: 'doctor'));
    }

    protected function getCancelFormAction(): Action
    {
        return Action::make('cancel')
            ->label(__('Back to dashboard'))
            ->url(DoctorDashboard::getUrl(panel: 'doctor'))
            ->color('gray');
    }
}
