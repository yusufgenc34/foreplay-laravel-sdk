<?php

declare(strict_types=1);

namespace Foreplay\LaravelSdk\Enums;

enum Niche: string
{
    case Accessories = 'accessories';
    case AppSoftware = 'app/software';
    case Beauty = 'beauty';
    case BusinessProfessional = 'business/professional';
    case Education = 'education';
    case Entertainment = 'entertainment';
    case Fashion = 'fashion';
    case FoodDrink = 'food/drink';
    case HealthWellness = 'health/wellness';
    case HomeGarden = 'home/garden';
    case JewelryWatches = 'jewelry/watches';
    case Other = 'other';
    case Parenting = 'parenting';
    case Pets = 'pets';
    case RealEstate = 'real estate';
    case ServiceBusiness = 'service business';
    case Medical = 'medical';
    case CharityNfp = 'charity/nfp';
    case KidsBaby = 'kids/baby';
}
