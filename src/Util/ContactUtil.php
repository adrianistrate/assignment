<?php

namespace App\Util;

use Symfony\Component\DomCrawler\Crawler;
use function Symfony\Component\String\u;

/**
 *
 */
class ContactUtil
{
    public const CONTACTS_QUERY = '#contacts';
    public const CONTACT_QUERY = '.col-xl-4.col-md-6';
    public const CONTACT_TITLE_QUERY = '.erpl_title-h3';
    public const CONTACT_CARD_DETAILS_QUERY = '.erpl_contact-card-list span';
    public const CONTACT_PHONE_QUERY = '.es_icon-phone';
    public const CONTACT_FAX_QUERY = '.es_icon-fax';

    /**
     * @param Crawler $contactNode
     * @return string
     */
    public static function getFullAddressFormatted(Crawler $contactNode): string
    {
        $title = $contactNode->filter(self::CONTACT_TITLE_QUERY)->text();
        $addressRaw = $contactNode->filter(self::CONTACT_CARD_DETAILS_QUERY)->first()->html();
        $address = strip_tags(u($addressRaw)->replace('<br>', " "));

        $phone = '';
        $phoneFilter = $contactNode->filter(self::CONTACT_PHONE_QUERY);
        if($phoneFilter->count()) {
            $phone = u($phoneFilter->siblings()->links()[0]->getUri())->replace('tel:', '');
        }

        $fax = '';
        $faxFilter = $contactNode->filter(self::CONTACT_FAX_QUERY);
        if($faxFilter->count()) {
            $fax = u($faxFilter->siblings()->links()[0]->getUri())->replace('tel:', '');
        }

        return u(sprintf('%s: %s %s %s ', $title, $address, $phone, $fax))->replace("\r", "");
    }
}
