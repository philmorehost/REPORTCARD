<?php
/**
 * i18n.php - Internationalization Core
 *
 * Handles loading language files and providing a translation function.
 */

// Global variable to hold the loaded language strings
$translations = [];

/**
 * Loads the language file for the given language code.
 *
 * @param string $lang The language code (e.g., 'en', 'es'). Defaults to 'en'.
 */
function load_language($lang = 'en') {
    global $translations;

    $lang_file = __DIR__ . "/languages/{$lang}.php";

    // Default to English if the specified language file doesn't exist
    if (!file_exists($lang_file)) {
        $lang_file = __DIR__ . "/languages/en.php";
    }

    $translations = require $lang_file;
}

/**
 * Translates a given key into the currently loaded language.
 *
 * @param string $key The key for the language string.
 * @param array $replacements An associative array of placeholders to replace.
 * @return string The translated string or the key itself if not found.
 */
function __($key, $replacements = []) {
    global $translations;

    if (isset($translations[$key])) {
        $string = $translations[$key];
        // Handle replacements
        foreach ($replacements as $placeholder => $value) {
            $string = str_replace('{' . $placeholder . '}', $value, $string);
        }
        return $string;
    }

    // Return the key itself as a fallback
    return $key;
}

// --- Initial Language Load ---
// By default, load English. Pages can override this after checking user/school settings.
load_language('en');

// Example of how a page would override:
// if (isset($_SESSION['user_lang'])) {
//     load_language($_SESSION['user_lang']);
// }
?>