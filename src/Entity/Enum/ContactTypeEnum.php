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
    case EUROPEAN_BLOG = 'european_blog';
    case GOOGLE_PLUS = 'google_plus';

    case ADDRESS = 'address';
}
