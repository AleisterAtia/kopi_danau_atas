<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    /**
     * Pre-validate the status transition before the model save runs.
     *
     * The Booking observer will reject illegal transitions with a
     * ValidationException, but surfacing the error here as a Filament
     * notification gives the admin a friendlier message and prevents
     * the save from being attempted at all.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        /** @var Booking $record */
        $record = $this->record;

        $from = $record->status;
        $to = $data['status'] ?? $from;

        if (! Booking::canTransitionTo($from, $to)) {
            Notification::make()
                ->title('Transisi status tidak diizinkan')
                ->body("Tidak bisa mengubah status dari '{$from}' menjadi '{$to}'.")
                ->danger()
                ->send();

            $this->halt();
        }

        return $data;
    }
}
