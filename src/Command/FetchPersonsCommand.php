<?php

namespace App\Command;

use App\Entity\Person;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DomCrawler\Crawler;
use function Symfony\Component\String\u;

#[AsCommand(
    name: 'app:fetch:persons',
    description: 'Command to fetch persons from external XML',
)]
class FetchPersonsCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $personsXML = 'https://www.europarl.europa.eu/meps/en/full-list/xml/a';
        $domCrawler = new Crawler();
        $domCrawler->addXmlContent(file_get_contents($personsXML));

        $nodes = $domCrawler->filterXPath('//mep');

        $io->info(sprintf('Found %d persons', $nodes->count()));

        $nodes->each(function (Crawler $node) {
            $names = explode(' ', $node->filter('fullName')->text());
            $firstName = u(implode(' ', array_slice($names, 0, count($names) - 1)))->lower()->title(true);
            $lastName = u($names[count($names) - 1])->lower()->title();

            $person = new Person();

            $person->setFirstName($firstName);
            $person->setLastName($lastName);
            $person->setCountry($node->filter('country')->text());
            $person->setCountry($node->filter('country')->text());
            $person->setPoliticalGroup($node->filter('politicalGroup')->text());

            $this->entityManager->persist($person);
        });

        $this->entityManager->flush();

        $io->success('Persons fetched!');

        return Command::SUCCESS;
    }
}
