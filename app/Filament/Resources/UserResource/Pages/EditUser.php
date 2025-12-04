<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Hook to upgrade guest users to user role when bouquets are assigned.
     * Also sync Spatie role with the selected role field.
     */
    protected function afterSave(): void
    {
        $user = $this->record;
        
        // Sync Spatie role with the role field if it was set
        if (isset($this->data['role'])) {
            $roleName = $this->data['role'];
            $user->syncRoles([$roleName]);
        }
        
        // Check if user is a guest and has packages assigned
        if ($user->hasRole('guest') && $user->hasPackageAssigned()) {
            $user->upgradeFromGuestToUser();
        }
    }

    /**
     * Mutate form data before filling the form.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Get the user's Spatie role and add it to the form data
        $user = $this->record;
        $data['role'] = $user->getRoleNames()->first() ?? 'guest';
        
        return $data;
    }
}
