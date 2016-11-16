<?php

namespace MLocati\CountryNames;

class Country
{
    /**
     * @var string
     */
    protected $code;

    /**
     * @var string[]
     */
    protected $allNames;

    /**
     * @var string|null
     */
    private $normalized;

    /**
     * @param string $code
     * @param string[] $allNames
     */
    public function __construct($code, array $allNames)
    {
        $this->code = $code;
        $this->allNames = array_values($allNames);
        $this->normalized = null;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getCanonicalName()
    {
        return $this->allNames[0];
    }

    /**
     * @return string[]
     */
    public function getVariants()
    {
        $result = array();
        if (isset($this->allNames[1])) {
            $copy = $this->allNames;
            unset($copy[0]);
            $result = array_values($copy);
        }

        return $result;
    }

    /**
     * @return string[]
     */
    public function getAllNames()
    {
        return $this->allNames;
    }

    public function addVariants(array $variants)
    {
        foreach ($variants as $variant) {
            if (!in_array($variant, $this->allNames)) {
                $this->allNames[] = $variant;
                $this->normalized = null;
            }
        }
    }

    protected function getNormalized()
    {
        if ($this->normalized === null) {
            $l = array();
            foreach ($this->allNames as $n) {
                $s = Normalizer::normalize($n);
                if ($s === '') {
                    throw new \Exception('Invalid country name');
                }
                $l[] = $s;
            }
            if (empty($l)) {
                throw new \Exception('No country names');
            }
            $this->normalized = implode('', $l);
        }

        return $this->normalized;
    }

    /**
     * @param string $string
     *
     * @return bool
     */
    public function containsRawName($string)
    {
        return $this->containsNormalizedName(Normalizer::normalize($string));
    }

    /**
     * @param string $string
     *
     * @return bool
     */
    public function containsNormalizedName($string)
    {
        return ($string !== '') && (strpos($this->getNormalized(), $string) !== false);
    }
}
