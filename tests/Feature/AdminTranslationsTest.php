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
     * Without ->label() Filament shows the column name in English ("Sound", "Created at"), so every
     * field, entry and column needs one. Relation managers no resource registers never appear and are skipped.
     */
    public function test_every_admin_panel_field_has_a_label(): void
    {
        $components = 'TextInput|Textarea|Select|Toggle|Checkbox|DatePicker|DateTimePicker|FileUpload|SpatieMediaLibraryFileUpload'
            .'|RichEditor|Repeater|KeyValue|ColorPicker|TagsInput|Radio|CheckboxList|TextEntry|IconEntry|ImageEntry'
            .'|SpatieMediaLibraryImageEntry|RepeatableEntry|ViewEntry|TextColumn|IconColumn|ImageColumn|ToggleColumn'
            .'|SpatieMediaLibraryImageColumn|SelectFilter|TernaryFilter';

        $files = collect(File::allFiles(app_path('Filament')))
            ->filter(fn (SplFileInfo $file): bool => $file->getExtension() === 'php');
        $allSource = $files->map(fn (SplFileInfo $file): string => $file->getContents())->implode("\n");

        $unlabeled = [];

        foreach ($files as $file) {
            $source = $file->getContents();
            $class = $file->getBasename('.php');

            if (str_contains($file->getRelativePath(), 'RelationManagers') && substr_count($allSource, "{$class}::class") === 0) {
                continue;
            }

            preg_match_all("/\b(?:{$components})::make\('([^']+)'\)/", $source, $matches, PREG_OFFSET_CAPTURE);

            foreach ($matches[0] as $index => [$call, $offset]) {
                // The component's chain runs until the next component is made.
                $rest = substr($source, $offset + strlen($call));
                $chain = preg_match('/\b[A-Z]\w+::make\(/', $rest, $next, PREG_OFFSET_CAPTURE) ? substr($rest, 0, $next[0][1]) : $rest;

                if (! str_contains($chain, '->label(') && ! str_contains($chain, '->hiddenLabel(')) {
                    $unlabeled[] = $file->getRelativePathname().": {$matches[1][$index][0]}";
                }
            }
        }

        $this->assertSame([], $unlabeled, 'Give these a translated ->label(): '.implode(' | ', $unlabeled));
    }

    /**
     * @param  callable(string): bool  $shouldCheck
     * @return array<string, array<int, string>> key => files using it
     */
    private function untranslatedKeys(string $locale, callable $shouldCheck): array
    {
        $files = collect([app_path('Filament'), app_path('Enums'), app_path('Exports'), resource_path('views/pdf'), resource_path('views/assessments'), resource_path('views/reports')])
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
