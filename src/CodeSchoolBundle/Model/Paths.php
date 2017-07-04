<?php

declare(strict_types=1);

namespace CodeSchoolBundle\Model;

use CodeSchoolBundle\Util\ClientHelper;
use CodeSchoolBundle\Util\FileHelper;
use DiDom\Document;
use DiDom\Element;

/**
 * Class Paths.
 */
class Paths
{
    /** @var string */
    private $name;

    /** @var string */
    private $description;

    /** @var string */
    private $logoPath;

    /** @var string */
    private $link;

    /** @var CourseSubGroup[] */
    private $coursesSubGroup = [];

    /** @var string */
    private $directoryPath;

    /**
     * Paths constructor.
     *
     * @param Element $card
     * @param string  $directoryPath
     */
    public function __construct(Element $card, string $directoryPath)
    {
        $this->link = ltrim($card->find('a')[0]->getAttribute('href'), '/');
        $this->name = $card->find('div.bucket-content h2 span.path-title-link')[0]->text();
        $this->description = $card->find('div.bucket-content p.mbf')[0]->text();
        $this->logoPath = ltrim($card->find('div.bucket-media img.badge-img')[0]->getAttribute('src'));
        $this->directoryPath = $directoryPath.DIRECTORY_SEPARATOR.FileHelper::getSlug($this->getName());
    }

    /**
     * @param string $name
     *
     * @return Paths
     */
    public function setName(string $name): Paths
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $description
     *
     * @return Paths
     */
    public function setDescription(string $description): Paths
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $logoPath
     *
     * @return Paths
     */
    public function setLogoPath(string $logoPath): Paths
    {
        $this->logoPath = $logoPath;

        return $this;
    }

    /**
     * @return string
     */
    public function getLogoPath(): string
    {
        return $this->logoPath;
    }

    /**
     * @param string $link
     *                     *
     *
     * @return Paths
     */
    public function setLink(string $link): Paths
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @param CourseSubGroup $coursesSubGroup
     *
     * @return Paths
     */
    public function addCourseSubGroup(CourseSubGroup $coursesSubGroup): Paths
    {
        $this->coursesSubGroup[] = $coursesSubGroup;

        return $this;
    }

    /**
     * @return CourseSubGroup[]
     */
    public function getCourseSubGroup()
    {
        return $this->coursesSubGroup;
    }

    /**
     * @param string $directoryPath
     *
     * @return Paths
     */
    public function setDirectoryPath(string $directoryPath): Paths
    {
        $this->directoryPath = $directoryPath;

        return $this;
    }

    /**
     * @return string
     */
    public function getDirectoryPath(): string
    {
        return $this->directoryPath;
    }

    /**
     * @param ClientHelper $client
     */
    public function parseCourseSubGroup(ClientHelper $client)
    {
        FileHelper::createDir($this->getDirectoryPath());
        echo "\t Scanning -> $this->name \n";
        $courseListResponse = $client->getRequest($this->getLink());
        $document = new Document($courseListResponse->getBody()->getContents());
        $preCourseSubGroups = $document->find('div.mbl:not(.card)');
        echo sprintf("\t\t -> found %s group(s) \n", count($preCourseSubGroups));
        foreach ($preCourseSubGroups as $preCourseSubGroup) {
            $title = $preCourseSubGroup->find('div.js-pathFilter-subgroup h2 span.prxs');
            if (count($title) >= 0) {
                $courseSubGroups = new CourseSubGroup($preCourseSubGroup, $this->directoryPath);
                $courseSubGroups->parseCourses($preCourseSubGroup->find('div.js-pathFilter-item'), $client);
                $courseSubGroups->generateMeta();
                $this->addCourseSubGroup($courseSubGroups);
            }
        }
    }

    /**
     * @param ClientHelper $client
     */
    public function generateMeta(ClientHelper $client)
    {
        $description = 'Name: '.$this->getName()."\r\n";
        $description .= 'Description: '.$this->getDescription()."\r\n";
        $description .= 'Link: '.ClientHelper::BASE_URL_PATH.$this->getLink()."\r\n";

        FileHelper::saveDescription($this->getDirectoryPath(), $description);
        FileHelper::saveCover($client, $this->getDirectoryPath(), $this->getLogoPath());
    }
}
