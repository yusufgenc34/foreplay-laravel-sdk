<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Enums;

enum PublisherPlatform: string
{
    case Facebook = 'facebook';
    case Instagram = 'instagram';
    case AudienceNetwork = 'audience_network';
    case Messenger = 'messenger';
    case TikTok = 'tiktok';
    case YouTube = 'youtube';
    case LinkedIn = 'linkedin';
    case Threads = 'threads';
    case WhatsApp = 'whatsapp';
}
