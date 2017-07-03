<?php

declare(strict_types=1);

namespace CodeSchoolBundle\Util;

use Cocur\Slugify\Slugify;

/**
 * Class FileHelper.
 */
class FileHelper
{
    /**
     * @param string $string
     *
     * @return string
     */
    public static function getSlug(string $string): string
    {
        $slugify = new Slugify();

        return $slugify->slugify($string);
    }

    /**
     * @param $path
     */
    public static function createDir(string $path)
    {
        $newDir = ROOT_DIR.$path;

        if (!is_dir($newDir)) {
            mkdir($newDir, 0777, true);
        }
    }

    /**
     * @param string $path
     * @param string $content
     */
    public static function saveDescription(string $path, string $content)
    {
        $descriptionPath = $path.DIRECTORY_SEPARATOR.'description.txt';
        if (!file_exists($descriptionPath)) {
            file_put_contents($descriptionPath, $content);
        }
    }

    /**
     * @param ClientHelper $client
     * @param string       $path
     * @param string       $imageURL
     */
    public static function saveCover(ClientHelper $client, string $path, string $imageURL)
    {
        $coverPath = $path.DIRECTORY_SEPARATOR.'cover.svg';
        if (!file_exists($coverPath)) {
            $coverResource = fopen($coverPath, 'w+');
            $client->downloadResource($imageURL, $coverResource);
        }
    }

    /**
     * @param ClientHelper $client
     * @param string       $videoPath
     * @param string       $videoURL
     */
    public static function saveVideo(ClientHelper $client, string $videoPath, string $videoURL)
    {
        if (!file_exists($videoPath)) {
            $videoResource = fopen($videoPath, 'w+');
            $client->downloadResource($videoURL, $videoResource);
        }
    }
}
