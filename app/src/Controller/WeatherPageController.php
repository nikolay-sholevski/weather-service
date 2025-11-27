<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WeatherPageController extends AbstractController
{
    #[Route('/weather', name: 'weather_page', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('weather/index.html.twig');
    }
}
