<?php

declare(strict_types=1);

namespace CodeSchoolBundle\Command;

use CodeSchoolBundle\Model\Paths;
use CodeSchoolBundle\Util\ClientHelper;
use CodeSchoolBundle\Util\FileHelper;
use DiDom\Document;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CodeSchoolParserCommand.
 */
class CodeSchoolParserCommand extends Command
{
    /** @var ClientHelper $webClient */
    private $client;

    /** @var Paths[] */
    private $paths = [];

    public function configure()
    {
        $this
            ->setName('parse:cs:main')
            ->addOption('username', 'u', InputOption::VALUE_REQUIRED, 'Your username')
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'Your password');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->client = new ClientHelper();
        $this->client->authUser($input);

        $this->getPaths();
        FileHelper::createDir('videos');
    }

    private function getPaths()
    {
        $pathsDirectoryPath = 'videos/paths';
        FileHelper::createDir($pathsDirectoryPath);

        echo "Scanning Paths\n";
        $result = $this->client->getRequest('learn');
        $document = new Document($result->getBody()->getContents());
        $pathCards = $document->find('.card.card--a');
        echo sprintf("-> found %s path(s) \n", count($pathCards));
        $paths = [];
        foreach ($pathCards as $card) {
            $path = new Paths($card, $pathsDirectoryPath);
            $path->parseCourseSubGroup($this->client);
            $path->generateMeta($this->client);
            $paths[] = $path;
        }
        $this->paths = $paths;
    }
}
