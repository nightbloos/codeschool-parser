<?php

declare(strict_types=1);

namespace CodeSchoolBundle\Model;

use CodeSchoolBundle\Util\ClientHelper;
use CodeSchoolBundle\Util\FileHelper;
use DiDom\Element;

/**
 * Class Course.
 */
class Course
{
    /** @var string */
    private $name;

    /** @var string */
    private $shortDescription;

    /** @var string */
    private $fullDescription;

    /** @var string */
    private $link;

    /** @var null|string */
    private $image;

    /** @var Videos[] */
    private $videos = [];

    /** @var string */
    private $directoryPath;

    /**
     * Course constructor.
     *
     * @param Element|\DOMElement $courseDomElement
     * @param string              $directoryPath
     */
    public function __construct($courseDomElement, string $directoryPath)
    {
        $courseHeader = $courseDomElement->find('article div.course-content h2.course-title a.course-title-link')[0];
        $this->link = $courseHeader->getAttribute('href');
        $this->image = $courseDomElement->find('article div.course-badge img.badge-img')[0]->getAttribute('src');
        $this->name = trim($courseHeader->text());
        $this->shortDescription = $courseDomElement->find('article div.course-content p.course-tagline')[0]->text();
        $this->directoryPath = $directoryPath.DIRECTORY_SEPARATOR.FileHelper::getSlug($this->getName());
    }

    /**
     * @param string $name
     *
     * @return Course
     */
    public function setName(string $name): Course
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $shortDescription
     *
     * @return Course
     */
    public function setShortDescription(string $shortDescription): Course
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    /**
     * @param string $fullDescription
     *
     * @return Course
     */
    public function setFullDescription(string $fullDescription): Course
    {
        $this->fullDescription = $fullDescription;

        return $this;
    }

    /**
     * @param string $link
     *
     * @return Course
     */
    public function setLink(string $link): Course
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @param string $image
     *
     * @return Course
     */
    public function setImage(string $image): Course
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @param Videos $video
     *
     * @return Course
     */
    public function addVideo(Videos $video): Course
    {
        $this->videos[] = $video;

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
     * @return string
     */
    public function getNameForDir(): string
    {
        return str_replace([' ', '/', '.'], '_', $this->name).'/';
    }

    /**
     * @return string
     */
    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    /**
     * @return string
     */
    public function getFullDescription(): string
    {
        return $this->fullDescription;
    }

    /**
     * @return string
     */
    public function getLink(): string
    {
        return $this->link;
    }

    /**
     * @return string
     */
    public function getImage(): string
    {
        return $this->image;
    }

    /**
     * @return Videos[]
     */
    public function getVideos()
    {
        return $this->videos;
    }

    /**
     * @param string $directoryPath
     *
     * @return Course
     */
    public function setDirectoryPath(string $directoryPath): Course
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
     * * @param ClientHelper $client
     */
    public function parseVideos(ClientHelper $client)
    {
        FileHelper::createDir($this->getDirectoryPath());
        echo "\t\t\t\t Scanning -> $this->name course \n";
        $courseVideoLink = $this->getLink().'/videos';
        $courseResponse = $client->getRequest($courseVideoLink);

        preg_match('#new CS\.Classes\.VideoManager\((.*?)\);#isu', $courseResponse->getBody()->getContents(), $result);
        echo sprintf("\t\t\t\t -> found %s video(s) \n", count($result));
        if (count($result) !== 0) {
            $jsonWrap = \GuzzleHttp\json_decode($result[1], true);
            foreach ($jsonWrap['media'] as $media) {
                $video = new Videos($media, $this->getDirectoryPath());
                $video->parseVideoPath($client);
                $video->downloadVideo($client);
                $this->addVideo($video);
            }
        }
    }

    /**
     * @param ClientHelper $client
     */
    public function generateMeta(ClientHelper $client)
    {
        $description = 'Name: '.$this->getName()."\r\n";
        $description .= 'Description: '.$this->getShortDescription()."\r\n";
        $description .= 'Link: '.$this->getLink()."\r\n";

        FileHelper::saveDescription($this->getDirectoryPath(), $description);
    }
}
