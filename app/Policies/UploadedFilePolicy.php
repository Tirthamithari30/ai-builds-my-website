<?php

namespace App\Policies;

use App\Models\UploadedFile;
use App\Models\User;

class UploadedFilePolicy
{
    public function view(User $user, UploadedFile $file): bool
    {
        return $user->id === $file->business->user_id;
    }

    public function update(User $user, UploadedFile $file): bool
    {
        return $user->id === $file->business->user_id;
    }

    public function delete(User $user, UploadedFile $file): bool
    {
        return $user->id === $file->business->user_id;
    }
}
