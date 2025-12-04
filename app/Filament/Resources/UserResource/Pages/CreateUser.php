<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Hook to assign role to newly created user.
     */
    protected function afterCreate(): void
    {
        $user = $this->record;
        
        // Assign Spatie role based on the role field
        if (isset($this->data['role'])) {
            $roleName = $this->data['role'];
            $user->assignRole($roleName);
        } else {
            // Default to guest role if not specified
            $user->assignRole('guest');
        }
    }
}
