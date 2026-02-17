<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;

class LocalizationController{
    // Easily support multiple languages within your application.
    // Publish lang directory: php artisan lang:publish
    // There are tewo approaches: lang- folders for each language (/en/messages.php), Json files for each lang (/lang/en.json, bn.json)
    // Set Locale in env: APP_LOCALE (default locale), APP_FALLBACK_LOCALE (when the default language does not contain a given translation string.)
    
    App::setLocale('bn'); // Set default locale for a single HTTP request at runtime.
    App::currentLocale(); // Return the currently used locale.
    App::isLocale('en'); // Check if the locale is the current locale.

    // In message.php: use keyed string.
    return [
        'welcome' => 'Welcome to our application!',
        'welcome' => 'Welcome, :name', // Using placeholder

        // Pluralization.
        'apples' => 'There is one apple|There are many apples',
        'apples' => '{0} There are none|[1,19] There are some|[20,*] There are many',
        'minutes_ago' => '{1} :value minute ago|[2,*] :value minutes ago',
        'apples' => '{0} There are none|{1} There is one|[2,*] There are :count', // Using count placeholder for integer.
        // echo trans_choice('messages.apples', 10); - There are some.
        // trans_choice('time.minutes_ago', 5, ['value' => 5]);
    ];

    //* If we use json file in es.json: {"I love programming.": "Me encanta programar."}
    // Pluralization in json: "There is one apple|There are many apples": "Hay una manzana|Hay muchas manzanas"

    //* Retrieving translation strings:
    echo __('messages.welcome'); // using dot notation. If not exist, will return the key.
    echo __('I love programming.'); // If string and key are same.
    echo __('messages.welcome', ['name' => 'rony']); // Giving placeholder.
    // If placeholder is :NAME, we will get RONY, if :Name, then Rony.

    //* Overriding Package Language Files:
    // if you need to override the English translation strings in messages.php for a package named skyrim/hearthfire, you should place a language file at: lang/vendor/hearthfire/en/messages.php
}