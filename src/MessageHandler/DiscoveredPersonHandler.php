<?php

namespace App\MessageHandler;

use App\Entity\Contact;
use App\Entity\Enum\ContactTypeEnum;
use App\Entity\Person;
use App\Message\DiscoveredPerson;
use App\Util\ContactUtil;
use App\Util\MediaReferenceUtil;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function Symfony\Component\String\u;

/**
 *
 */
#[AsMessageHandler]
final class DiscoveredPersonHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @param EntityManagerInterface $entityManager
     * @param HttpClientInterface $httpClient
     */
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly HttpClientInterface $httpClient)
    {
    }

    /**
     * @param DiscoveredPerson $discoveredPerson
     * @return void
     * @throws \Exception
     */
    public function __invoke(DiscoveredPerson $discoveredPerson): void
    {
        $this->logger->debug('Processing person', [$discoveredPerson]);

        $fullName = $discoveredPerson->getFullName();
        $names = explode(' ', $fullName);

        $firstName = u(implode(' ', array_slice($names, 0, count($names) - 1)))->lower()->title(true);
        $lastName = u($names[count($names) - 1])->lower()->title();

        $person = new Person();

        $person->setFirstName($firstName);
        $person->setLastName($lastName);
        $person->setCountry($discoveredPerson->getCountry());
        $person->setPoliticalGroup($discoveredPerson->getPoliticalGroup());

        $personId = $discoveredPerson->getPersonId();

        $singlePersonPage = sprintf('https://www.europarl.europa.eu/meps/en/%s/%s/home', $personId, u($fullName)->replace(' ', '_')->upper()->ascii());

        $singlePersonPageContent = $this->httpClient->request('GET', $singlePersonPage)->getContent();

        $crawler = new Crawler();
        $crawler->addHtmlContent($singlePersonPageContent);

        // Media references
        $mediaReferenceNodes = $crawler->filter(MediaReferenceUtil::MEDIA_REFERENCE_QUERY)->children();
        $this->logger->debug(sprintf('Found %d media references', $mediaReferenceNodes->count()));

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
        $this->logger->debug(sprintf('Found %d contacts', $contactNodes->count()));

        if($contactNodes->count()) {
            $contactNodes->each(function (Crawler $node) use ($person) {
                $fullAddress = ContactUtil::getFullAddressFormatted($node);

                $contact = new Contact();
                $contact->setPerson($person);
                $contact->setType(ContactTypeEnum::ADDRESS);
                $contact->setValue($fullAddress);

                $this->entityManager->persist($contact);
            });
        }

        $this->entityManager->persist($person);

        $this->entityManager->flush();
    }
}
