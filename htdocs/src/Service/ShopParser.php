<?php

namespace App\Service;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ShopParser
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * Парсит магазин и возвращает массив товаров.
     */
    public function parse(string $url): array
    {
        try {
            $response = $this->httpClient->request('GET', $url);
            $html = $response->getContent();
        } catch (TransportExceptionInterface $e) {
            // Проблемы с сетью
            error_log('Network error: ' . $e->getMessage());
            return [];
        } catch (HttpExceptionInterface $e) {
            // HTTP ошибки (4xx, 5xx)
            error_log('HTTP error: ' . $e->getMessage());
            return [];
        }

        // Здесь парсим HTML (можно использовать Symfony DomCrawler или PHP-DI)
        $products = $this->extractProducts($html);

        return $products;
    }

    private function extractProducts(string $html): array
    {
        $crawler = new Crawler($html);
        $products = [];

        // Парсим каждый товарный блок
        $crawler->filter('section.section--catalog-product a.catalog-item-product')->each(
            function (Crawler $node) use (&$products) {
                // Название товара
                $name = $node->filter('.catalog-item-product__title')->text('', true);

                // Цена (удаляем "руб." и пробелы)
                $price = $node->filter('.catalog-item-product__price')->text('0', true);
                $price = (float)preg_replace('/[^\d]/', '', $price);

                // Старая цена (если есть)
                $oldPrice = 0;
                if ($node->filter('.catalog-item-product__old-rice')->count() > 0) {
                    $oldPrice = $node->filter('.catalog-item-product__old-rice')->text('0', true);
                    $oldPrice = (float)preg_replace('/[^\d]/', '', $oldPrice);
                }

                // Кэшбэк (если есть)
                $cashback = '';
                if ($node->filter('.catalog-item-cashback')->count() > 0) {
                    $cashback = $node->filter('.catalog-item-cashback')->text('', true);
                }

                // URL изображения
                $image = '';
                if ($node->filter('.catalog-item-product__goods img')->count() > 0) {
                    $image = $node->filter('.catalog-item-product__goods img')->attr('src');
                }

                // URL товара
                $url = $node->attr('href');

                $products[] = [
                    'name' => trim($name),
                    'price' => $price,
                    'old_price' => $oldPrice,
                    'cashback' => trim($cashback),
                    'image' => $image,
                    'url' => $url
                ];
            }
        );

        return $products;
    }

    private function extractProductName(string $html): string
    {
        preg_match('/<h3 class="product-title">(.*?)<\/h3>/', $html, $match);
        return $match[1] ?? 'Unknown';
    }

    private function extractProductPrice(string $html): float
    {
        preg_match('/<span class="price">(.*?)<\/span>/', $html, $match);
        return (float) preg_replace('/[^0-9.]/', '', $match[1] ?? '0');
    }
}
