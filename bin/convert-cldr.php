<?php

use MLocati\CountryNames\Country;
use MLocati\CountryNames\CountryList;

if (!isset($argv[2]) || isset($argv[4])) {
    fwrite(STDERR, <<<EOT

Syntax: php {$argv[0]} <LanguageCode> <XmlFilePath> [DestinationDirectory]

EOT
);
    exit(1);
}
$languageCode = $argv[1];
$sourceFile = $argv[2];
$destinationDirectory = isset($argv[3]) ? $argv[3] : null;
echo "Parsing language $languageCode from $sourceFile... ";

require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'autoload.php';

try {
    $xml = @file_get_contents($sourceFile);
    if (!$xml) {
        throw new Exception('Failed to read file '.$sourceFile);
    }
    $document = new SimpleXMLElement($xml);
    if (!isset($document->localeDisplayNames)) {
        throw new Exception('Invalid CLDR file (missing localeDisplayNames): '.$sourceFile);
    }
    $localeDisplayNames = $document->localeDisplayNames;
    if (!isset($localeDisplayNames->territories)) {
        throw new Exception('Invalid CLDR file (missing localeDisplayNames/territories): '.$sourceFile);
    }
    $territories = $localeDisplayNames->territories;
    if (!isset($territories->territory)) {
        throw new Exception('Invalid CLDR file (missing localeDisplayNames/territories/territory): '.$sourceFile);
    }
    $data = array();
    foreach ($territories->territory as $territory) {
        $name = (string) $territory;
        $code = '';
        $alt = '';
        foreach ($territory->attributes() as $attributeName => $attributeValue) {
            switch ($attributeName) {
                case 'type':
                    $code = (string) $attributeValue;
                    break;
                case 'alt':
                    $alt = (string) $attributeValue;
                    switch ($alt) {
                        case 'short':
                        case 'variant':
                            break;
                        default:
                            throw new Exception('Invalid CLDR file (unknown ALT territory attribute value '.$alt.'): '.$sourceFile);
                    }
                    break;
                default:
                    throw new Exception('Invalid CLDR file (unknown territory attribute '.$attributeName.'): '.$sourceFile);
            }
        }
        if (isset($data[$code])) {
            if ($alt === '') {
                if ($data[$code][0] !== null) {
                    throw new Exception('Duplicated "Main" name for Country '.$code);
                }
                $data[$code][0] = $name;
            } else {
                $data[$code][] = $name;
            }
        } else {
            if ($alt === '') {
                $data[$code] = array($name);
            } else {
                $data[$code] = array(null, $name);
            }
        }
        if ($code === '') {
            throw new Exception('Invalid CLDR file (missing territory attribute TYPE): '.$sourceFile);
        }
    }
    if (empty($data)) {
        throw new Exception('No data found!');
    }
    $countryList = new CountryList($languageCode);
    $countryList->clear();
    if ($destinationDirectory !== null) {
        $countryList->setDataDirectory($destinationDirectory);
    }
    foreach ($data as $code => $names) {
        if ($names[0] === null) {
            throw new Exception('No "Main" name found for Country '.$code);
        }
        $country = new Country($code, $names);
        $countryList->add($country);
    }
    $countryList->save();
} catch (Exception $x) {
    fwrite(STDERR, $x->getMessage()."\n");
    exit(1);
}
echo "Done\n";
