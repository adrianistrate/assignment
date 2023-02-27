<?php

namespace App\Message;

/**
 *
 */
final class DiscoveredPerson
{
    /**
     * @param string $personId
     * @param string $fullName
     * @param string $country
     * @param string $politicalGroup
     */
    public function __construct(private readonly string $personId, private readonly string $fullName, private readonly string $country, private readonly string $politicalGroup)
    {

    }

    /**
     * @return string
     */
    public function getPersonId(): string
    {
        return $this->personId;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->fullName;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->country;
    }

    /**
     * @return string
     */
    public function getPoliticalGroup(): string
    {
        return $this->politicalGroup;
    }
}
