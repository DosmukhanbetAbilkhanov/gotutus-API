<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Deep linking (App Links / Universal Links)
    |--------------------------------------------------------------------------
    | Values used by the /.well-known association files and the /h/{id} landing
    | page. Fill the fingerprint/team id from the release signing config.
    */

    'android' => [
        'package' => env('DEEPLINK_ANDROID_PACKAGE', 'com.tanys.app'),
        // SHA-256 of the *release* signing cert. Get it with:
        //   keytool -list -v -keystore ~/tanys-release.jks -alias tanys-release
        // (use the "SHA256" line, colon-separated uppercase hex). Comma-separate
        // multiple (e.g. upload + Play App Signing keys).
        'sha256_fingerprints' => array_values(array_filter(
            explode(',', (string) env('DEEPLINK_ANDROID_SHA256', ''))
        )),
        'store_url' => env('DEEPLINK_ANDROID_STORE_URL', 'https://play.google.com/store/apps/details?id=com.tanys.app'),
    ],

    'ios' => [
        'bundle_id' => env('DEEPLINK_IOS_BUNDLE_ID', 'com.tanys.app'),
        // Apple Developer Team ID (10 chars, e.g. ABCDE12345).
        'team_id' => env('DEEPLINK_IOS_TEAM_ID', ''),
        'store_url' => env('DEEPLINK_IOS_STORE_URL', 'https://apps.apple.com/app/tanys/id000000000'),
    ],

    // URL path prefix that opens a hangout in the app.
    'hangout_path' => '/h',
];
