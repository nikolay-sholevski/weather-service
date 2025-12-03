<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class GetCityWeatherRequest
{
    #[Assert\NotBlank(message: "City parameter is required.")]
    #[Assert\Length(
        min: 2,
        max: 80,
        minMessage: "City name must be at least {{ limit }} characters long.",
        maxMessage: "City name cannot be longer than {{ limit }} characters."
    )]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z\s\p{L}-]+$/u",
        message: "City name contains invalid characters."
    )]
    public ?string $city = null;
}

