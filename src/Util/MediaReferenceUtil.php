<?php

namespace App\Util;

use App\Entity\Enum\ContactTypeEnum;
use function Symfony\Component\String\u;

/**
 *
 */
class MediaReferenceUtil
{
    public const MEDIA_REFERENCE_QUERY = '.erpl_social-share-horizontal';

    /**
     * @param string $className
     * @return ContactTypeEnum
     * @throws \Exception
     */
    public static function getType(string $className): ContactTypeEnum
    {
        $rawType = u($className)->replace(' mr-2', '')->trim();

        switch($rawType) {
            case 'link_email': {
                return ContactTypeEnum::EMAIL;
            }
            case 'link_twitt': {
                return ContactTypeEnum::TWITTER;
            }
            case 'link_website': {
                return ContactTypeEnum::WEBSITE;
            }
            case 'link_instagram': {
                return ContactTypeEnum::INSTAGRAM;
            }
            case 'link_fb': {
                return ContactTypeEnum::FACEBOOK;
            }
            case 'link_linkedin': {
                return ContactTypeEnum::LINKEDIN;
            }
            case 'link_youtube': {
                return ContactTypeEnum::YOUTUBE;
            }
            case 'link_blog': {
                return ContactTypeEnum::BLOG;
            }
            case 'link_telegram': {
                return ContactTypeEnum::TELEGRAM;
            }
            default: {
                throw new \Exception(sprintf('Unknown type "%s"', $rawType));
            }
        }
    }

    /**
     * @param string $href
     * @param ContactTypeEnum $contactTypeEnum
     * @return string
     */
    public static function getValue(ContactTypeEnum $contactTypeEnum, string $href): string
    {
        switch($contactTypeEnum) {
            case ContactTypeEnum::EMAIL: {
                return u($href)->replace('mailto:', '')->trim();
            }
            default: {
                return $href;
            }
        }
    }
}
