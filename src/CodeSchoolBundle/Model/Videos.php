<?php

declare(strict_types=1);

namespace CodeSchoolBundle\Model;

use CodeSchoolBundle\Util\ClientHelper;
use CodeSchoolBundle\Util\FileHelper;

/**
 * Class Videos.
 */
class Videos {
    /** @var string */
    private $name;

    /** @var string */
    private $downloadUrl;

    /** @var array */
    private $media;

    /** @var string */
    private $directoryPath;

    /**
     * Videos constructor.
     *
     * @param array  $media
     * @param string $directoryPath
     */
    public function __construct(array $media, string $directoryPath)
    {
        $this->media = $media;
        $this->name = $media['title'];
        $this->directoryPath = $directoryPath;
    }

    /**
     * @param string $name
     *
     * @return Videos
     */
    public function setName(string $name): Videos
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $downloadUrl
     *
     * @return Videos
     */
    public function setUrl(string $downloadUrl): Videos
    {
        $this->downloadUrl = $downloadUrl;

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
    public function getNameForFile(): string
    {
        return FileHelper::getSlug($this->getName()) . '.mp4';
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->downloadUrl;
    }

    /**
     * @param string $directoryPath
     *
     * @return Videos
     */
    public function setDirectoryPath(string $directoryPath): Videos
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
    public function parseVideoPath(ClientHelper $client)
    {
        $mediaResp = $courseRes = $client->getRequest($this->media['media'], true);
        $jsonMedia = \GuzzleHttp\json_decode($mediaResp->getBody()->getContents(), true);
        $vidPath = $jsonMedia['media'][0]['sources'][0]['src'];
        $this->setUrl($vidPath);
    }

    /**
     * @param ClientHelper $client
     */
    public function downloadVideo(ClientHelper $client)
    {
        echo sprintf("\t\t\t\t\t -> downloading video %s\n", $this->getName());
        FileHelper::saveVideo(
            $client,
            $this->getDirectoryPath() . DIRECTORY_SEPARATOR . $this->getNameForFile(),
            $this->getUrl()
        );
    }
}
