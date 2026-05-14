<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Enums;

enum DisplayFormat: string
{
    case Carousel = 'carousel';
    case Dco = 'dco';
    case Dpa = 'dpa';
    case Event = 'event';
    case Image = 'image';
    case MultiImages = 'multi_images';
    case MultiMedias = 'multi_medias';
    case MultiVideos = 'multi_videos';
    case PageLike = 'page_like';
    case Text = 'text';
    case Video = 'video';
}
