<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Symfony\Component\Finder\SplFileInfo;
use Tests\TestCase;

/**
 * The admin panel runs in Arabic, so every label passed to __() in the panel code must have an Arabic translation,
 * and snake_case keys must also have readable English.
 */
class AdminTranslationsTest extends TestCase
{
    public function test_every_admin_panel_string_has_an_arabic_translation(): void
    {
        $missing = $this->untranslatedKeys('ar', fn (string $key): bool => true);

        $this->assertSame([], $missing, 'Add these keys to lang/ar.json: '.implode(' | ', array_keys($missing)));
    }

    public function test_snake_case_keys_have_readable_english(): void
    {
        $missing = $this->untranslatedKeys('en', fn (string $key): bool => (bool) preg_match('/^[a-z]+(_[a-z]+)+$/', $key));

        $this->assertSame([], $missing, 'Add these keys to lang/en.json: '.implode(' | ', array_keys($missing)));
    }

    /**
     * @param  callable(string): bool  $shouldCheck
     * @return array<string, array<int, string>> key => files using it
     */
    private function untranslatedKeys(string $locale, callable $shouldCheck): array
    {
        $files = collect([app_path('Filament'), app_path('Enums'), resource_path('views/pdf')])
            ->flatMap(fn (string $directory): array => File::allFiles($directory))
            ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'php');

        $missing = [];

        foreach ($files as $file) {
            preg_match_all("/(?:__|trans_choice)\(\s*'((?:[^'\\\\]|\\\\.)*)'/", $file->getContents(), $matches);

            foreach ($matches[1] as $key) {
                $key = stripslashes($key);

                // Package namespaces and placeholders such as "-" are not ours to translate.
                if (str_contains($key, '::') || ! preg_match('/\pL/u', $key) || ! $shouldCheck($key)) {
                    continue;
                }

                if (! Lang::hasForLocale($key, $locale)) {
                    $missing[$key][] = $file->getRelativePathname();
                }
            }
        }

        return $missing;
    }
}
