<?php

namespace App\Command;

use App\Service\ShopParser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:parse-shop',
    description: 'Парсит интернет-магазин и выводит товары'
)]
class ParseShopCommand extends Command
{
    protected static string $defaultName = 'app:parse-shop';

    private ShopParser $parser;

    public function __construct(ShopParser $parser)
    {
        parent::__construct();
        $this->parser = $parser;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Парсит интернет-магазин и выводит товары')
            ->addArgument('url', InputArgument::REQUIRED, 'URL магазина');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $url = $input->getArgument('url');
        $products = $this->parser->parse($url);

        $output->writeln("Найдено товаров: " . count($products));
        foreach ($products as $product) {
            $output->writeln("{$product['name']} - {$product['price']} руб.");
        }

        return Command::SUCCESS;
    }
}
