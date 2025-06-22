<?php

namespace App\Controller;

use App\Service\ShopParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ShopController extends AbstractController
{
    #[Route('/parse-shop', name: 'parse_shop')]
    public function parseShop(ShopParser $parser): JsonResponse
    {
        $url = 'https://www.nashaigra.ru/catalog/konki/';
        $products = $parser->parse($url);

        return $this->json($products);
    }
}
