<?php

namespace App\Entity\Enum;

/**
 *
 */
enum ContactTypeEnum: string
{
    case EMAIL = 'email';
    case TWITTER = 'twitter';
    case WEBSITE = 'website';
    case INSTAGRAM = 'instagram';
    case FACEBOOK = 'facebook';
    case LINKEDIN = 'linkedin';
    case YOUTUBE = 'youtube';
    case BLOG = 'blog';
    case TELEGRAM = 'telegram';

    case ADDRESS = 'address';
}
