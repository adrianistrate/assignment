<?php

namespace App\Command;

use App\Entity\Contact;
use App\Entity\Enum\ContactTypeEnum;
use App\Entity\Person;
use App\Util\ContactUtil;
use App\Util\MediaReferenceUtil;
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
        $crawler = new Crawler();
        $crawler->addXmlContent(file_get_contents($personsXML));

        $nodes = $crawler->filterXPath('//mep');

        $io->info(sprintf('Found %d persons', $nodes->count()));

        $nodes->each(function (Crawler $node) use($io) {
            $fullName = $node->filter('fullName')->text();

            $io->info(sprintf('Processing %s', $fullName));

            $names = explode(' ', $fullName);
            $firstName = u(implode(' ', array_slice($names, 0, count($names) - 1)))->lower()->title(true);
            $lastName = u($names[count($names) - 1])->lower()->title();

            $person = new Person();

            $person->setFirstName($firstName);
            $person->setLastName($lastName);
            $person->setCountry($node->filter('country')->text());
            $person->setCountry($node->filter('country')->text());
            $person->setPoliticalGroup($node->filter('politicalGroup')->text());

            $this->entityManager->persist($person);

            $personId = $node->filter('id')->text();

            $singlePersonPage = sprintf('https://www.europarl.europa.eu/meps/en/%s/%s/home', $personId, u($fullName)->replace(' ', '_')->upper()->ascii());
            $crawler = new Crawler();
            $crawler->addHtmlContent(file_get_contents($singlePersonPage));

            // Media references
            $mediaReferenceNodes = $crawler->filter(MediaReferenceUtil::MEDIA_REFERENCE_QUERY)->children();
            $io->text(sprintf('Found %d media references', $mediaReferenceNodes->count()));

            $mediaReferenceNodes->each(function (Crawler $node) use ($person) {
                $className = $node->attr('class');
                $href = $node->attr('href');

                $contactTypeEnum = MediaReferenceUtil::getType($className);

                $contact = new Contact();
                $contact->setPerson($person);
                $contact->setType($contactTypeEnum);
                $contact->setValue(MediaReferenceUtil::getValue($contactTypeEnum, $href));

                $this->entityManager->persist($contact);
            });

            // Contacts
            $contactNodes = $crawler->filter(sprintf('%s %s', ContactUtil::CONTACTS_QUERY, ContactUtil::CONTACT_QUERY));
            $io->text(sprintf('Found %d contacts', $contactNodes->count()));

            $contactNodes->each(function (Crawler $node) use ($person) {
                $fullAddress = ContactUtil::getFullAddressFormatted($node);

                $contact = new Contact();
                $contact->setPerson($person);
                $contact->setType(ContactTypeEnum::ADDRESS);
                $contact->setValue($fullAddress);

                $this->entityManager->persist($contact);
            });
        });

        $this->entityManager->flush();

        $io->success('Persons fetched!');

        return Command::SUCCESS;
    }
}
