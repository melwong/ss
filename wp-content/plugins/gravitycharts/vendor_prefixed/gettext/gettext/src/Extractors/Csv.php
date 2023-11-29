<?php
/**
 * @license MIT
 *
 * Modified by gravitykit on 07-September-2023 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace GravityKit\GravityCharts\Foundation\ThirdParty\Gettext\Extractors;

use GravityKit\GravityCharts\Foundation\ThirdParty\Gettext\Translations;
use GravityKit\GravityCharts\Foundation\ThirdParty\Gettext\Utils\HeadersExtractorTrait;
use GravityKit\GravityCharts\Foundation\ThirdParty\Gettext\Utils\CsvTrait;

/**
 * Class to get gettext strings from csv.
 */
class Csv extends Extractor implements ExtractorInterface
{
    use HeadersExtractorTrait;
    use CsvTrait;

    public static $options = [
        'delimiter' => ",",
        'enclosure' => '"',
        'escape_char' => "\\"
    ];

    /**
     * {@inheritdoc}
     */
    public static function fromString($string, Translations $translations, array $options = [])
    {
        $options += static::$options;
        $handle = fopen('php://memory', 'w');

        fputs($handle, $string);
        rewind($handle);

        while ($row = static::fgetcsv($handle, $options)) {
            $context = array_shift($row);
            $original = array_shift($row);

            if ($context === '' && $original === '') {
                static::extractHeaders(array_shift($row), $translations);
                continue;
            }

            $translation = $translations->insert($context, $original);

            if (!empty($row)) {
                $translation->setTranslation(array_shift($row));
                $translation->setPluralTranslations($row);
            }
        }

        fclose($handle);
    }
}