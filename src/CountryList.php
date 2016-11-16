<?php

namespace MLocati\CountryNames;

use Exception;

class CountryList
{
    /**
     * @var string
     */
    protected $languageCode;

    /**
     * @var string|null
     */
    protected $dataDirectory = null;

    /**
     * @var Country[]|null
     */
    protected $list = null;

    /**
     * @param string $languageCode
     */
    public function __construct($languageCode)
    {
        $this->languageCode = $languageCode;
        $this->dataDirectory = null;
        $this->list = null;
    }

    /**
     * @return string
     */
    public function getDataDirectory()
    {
        return ($this->dataDirectory === null) ? (dirname(__FILE__).DIRECTORY_SEPARATOR.'data') : $this->dataDirectory;
    }

    /**
     * @param string $dataDirectory
     *
     * @return static
     */
    public function setDataDirectory($dataDirectory)
    {
        $this->dataDirectory = (is_string($dataDirectory) && $dataDirectory !== '') ? $dataDirectory : null;

        return $this;
    }

    /**
     * @return string
     */
    public function getDataFile()
    {
        return $this->getDataDirectory().DIRECTORY_SEPARATOR.$this->languageCode.'.json';
    }

    /**
     * @param bool $forceReload
     */
    protected function load($forceReload = false)
    {
        if ($forceReload || $this->list === null) {
            $list = array();
            $dataFile = $this->getDataFile();
            if (is_file($dataFile)) {
                $contents = @file_get_contents($dataFile);
                if ($contents === false) {
                    throw new Exception('Failed to read existing file '.$dataFile);
                }
                $data = @json_decode($contents, true);
                if (empty($data) || !is_array($data)) {
                    throw new Exception('Failed to decode contents of file '.$dataFile);
                }
                foreach ($data as $code => $names) {
                    $list[$code] = new Country($code, $names);
                }
            }
            $this->list = $list;
        }
    }

    public function clear()
    {
        $this->list = array();
    }

    /**
     * @param Country $country
     *
     * @return static
     */
    public function add(Country $country)
    {
        $this->load();
        $this->list[$country->getCode()] = $country;

        return $this;
    }

    /**
     * @param Country|string $what
     */
    public function remove($what)
    {
        $code = ($what instanceof Country) ? $country->getCode() : (string) $what;
        $this->load();
        if (isset($this->list[$code])) {
            unset($this->list[$code]);
        }
    }

    public function save()
    {
        $dataFile = $this->getDataFile();
        $this->load();
        if (empty($this->list)) {
            if (is_file($dataFile)) {
                if (@unlink($dataFile) !== true) {
                    throw new Exception('Failed to delete file '.$dataFile);
                }
            }
        } else {
            $data = array();
            foreach ($this->list as $code => $country) {
                $data[$code] = $country->getAllNames();
            }
            $flags = 0;
            $flags |= defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0;
            $flags |= defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : 0;
            $flags |= defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0;
            $contents = @json_encode($data, $flags);
            if (!$contents) {
                throw new Exception('Failed to encode contents for file '.$dataFile);
            }
            if (@file_put_contents($dataFile, $contents) === false) {
                throw new Exception('Failed to save to file '.$dataFile);
            }
        }
    }

    /**
     * @param string $code
     *
     * @return Country|null
     */
    public function getCountryByCode($code)
    {
        $this->load();

        return isset($this->list[$code]) ? $this->list[$code] : null;
    }

    /**
     * @param string $name
     *
     * @return Country|null
     */
    public function getCountryByName($name)
    {
        $this->load();

        $normalizedName = Normalizer::normalize($name);
        $result = null;
        foreach ($this->list as $country) {
            if ($country->containsNormalizedName($normalizedName)) {
                if ($result === null) {
                    $result = $country;
                } else {
                    $result = null;
                    break;
                }
            }
        }

        return $result;
    }
}
