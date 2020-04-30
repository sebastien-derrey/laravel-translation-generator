<?php

namespace Sdlab\TranslationGenerator;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class GenerateTranslationFiles extends Command
{
    private $translationMethodsFootprint = ['trans', '__', '@lang'];
    private $searchFolders = ['app', 'resources'];
    private $translateFileDir = 'resources/lang';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translation:generate-files'.
    '{--origin= : the string getted will be associated as translation for this language}' .
    '{--locale=* : translation files will be generated for these languages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate files for translation';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $translations = $this->getTranslations();
        $this->copyTranslations(
            $translations,
            collect($this->option('locale')),
            $this->option('origin')
        );
    }

    function getTranslations(): Collection
    {
        return collect($this->translationMethodsFootprint)
            ->map(
                function ($footprint) {
                    return collect($this->searchFolders)->map(
                        function ($folderToSearch) use ($footprint) {
                            exec("grep --include=\*.php -rw './" . $folderToSearch . "' -e \"" . $footprint . "(\"", $matches);
                            return collect($matches)->map(
                                function ($line) use ($footprint) {
                                    preg_match('#.*:.*' . $footprint . '\(\\\'(.*)\\\'\)#', $line, $translation);

                                    return $translation[1];
                                }
                            );
                        }
                    );
                }
            )
            ->flatten()
            ->filter()
            ->unique()
            ->mapWithKeys(
                function ($value) {
                    return [stripcslashes($value) => ''];
                }
            );
    }

    function copyTranslations(Collection $translations, Collection $locales, string $originalLocale = null)
    {
        $locales->each(
            function ($locale) use ($originalLocale, $translations) {
                $fileName = $this->translateFileDir . '/' . $locale . '.json';
                if (!file_exists($fileName)) {
                    touch($fileName);
                } else {
                    $translations = $translations->merge($this->getFileTranslations($locale));
                }
                $this->saveTranslationInFile($translations, $locale, $originalLocale);
            }
        );
    }

    function getFileTranslations($locale): Collection
    {
        return collect(json_decode(file_get_contents($this->getLocaleFilePathName($locale))));
    }

    function saveTranslationInFile(Collection $translations, string $locale, string $originalLocale = null)
    {
        $fileName = $this->getLocaleFilePathName($locale);
        $content = $translations
            ->sortKeys()
            ->map(
                function ($translationValue, $key) use ($locale, $originalLocale) {
                    if (!$translationValue && ($originalLocale === $locale)) {
                        $translationValue = $key;
                    }

                    return "\t" . '"' . $key . '": "' . $translationValue . '",' . PHP_EOL;
                }
            )
            ->implode('');
        file_put_contents($fileName, '{' . PHP_EOL . substr($content, 0, -2) . PHP_EOL . '}');
    }

    function getLocaleFilePathName($locale)
    {
        return $this->translateFileDir . '/' . $locale . '.json';
    }
}
