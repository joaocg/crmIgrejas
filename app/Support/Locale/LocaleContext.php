<?php

namespace App\Support\Locale;

use App\Models\User;

class LocaleContext
{
    public function resolve(?User $user = null): string
    {
        $locale = $user?->locale;

        if (is_string($locale) && $locale !== '') {
            return $locale;
        }

        return config('app.locale');
    }
}
