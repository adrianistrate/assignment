<?php

namespace App\Command;

use App\Message\DiscoveredPerson;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:fetch:persons',
    description: 'Command to fetch persons from external XML',
)]
class FetchPersonsCommand extends Command
{
    public function __construct(private readonly MessageBusInterface $messageBus, private readonly HttpClientInterface $httpClient)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

//        $personsXML = 'https://www.europarl.europa.eu/meps/en/full-list/xml/a';
        $personsXML = 'https://www.europarl.europa.eu/meps/en/full-list/xml/';

        $xmlContent = $this->httpClient->request('GET', $personsXML)->getContent();

        $crawler = new Crawler();
        $crawler->addXmlContent($xmlContent);

        $nodes = $crawler->filterXPath('//mep');

        $io->info(sprintf('Found %d persons', $nodes->count()));

        $nodes->each(function (Crawler $node) use ($io) {
            $fullName = $node->filter('fullName')->text();

            $io->info(sprintf('Processing %s', $fullName));

            $personId = $node->filter('id')->text();
            $country = $node->filter('country')->text();
            $politicalGroup = $node->filter('politicalGroup')->text();

            $this->messageBus->dispatch(new DiscoveredPerson($personId, $fullName, $country, $politicalGroup));
        });

        $io->success('Persons fetched and pushed to queue');

        return Command::SUCCESS;
    }
}
