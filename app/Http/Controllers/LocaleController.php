<?php

namespace App\Http\Controllers;

class LocaleController extends Controller
{
    public function switch(string $locale)
    {
        if (in_array($locale, ['id', 'en'])) {
            session(['locale' => $locale]);
        }

        return redirect()->back();
    }
}
