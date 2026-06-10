<?php

namespace App\Observers;

use App\Enums\UserRole;
use App\Models\PhotographerProfile;
use App\Models\User;
use Illuminate\Support\Str;

class UserObserver
{
    public function created(User $user): void
    {
        if ($user->role !== UserRole::Photographer) {
            return;
        }

        if ($user->photographerProfile()->exists()) {
            return;
        }

        $baseSlug = Str::slug($user->name) ?: 'fotograf';
        $slug = $baseSlug;
        $i = 2;
        while (PhotographerProfile::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$i++;
        }

        $profile = $user->photographerProfile()->create([
            'display_name' => $user->name,
            'slug' => $slug,
            'active' => false,
            'verified' => false,
        ]);

        $profile->socialLinks()->create([]);
    }
}
