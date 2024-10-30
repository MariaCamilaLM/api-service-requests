<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TranslationController extends Controller
{
    public function getTranslations(Request $request, $lang)
    {
        // Validate the language input
        if (!in_array($lang, ['en', 'es'])) {
            return response()->json(['error' => 'Invalid language'], 400);
        }

        // Load translations based on the requested language
        $translations = trans('messages', [], $lang);

        return response()->json($translations);
    }
}
