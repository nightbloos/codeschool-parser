<?php

declare(strict_types=1);

namespace CodeSchoolBundle\Util;

use Cocur\Slugify\Slugify;
use Symfony\Component\Filesystem\Filesystem;

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
        $fs = new Filesystem();

        if (!$fs->exists($newDir)) {
            $fs->mkdir($newDir);
        }
    }

    /**
     * @param string $path
     * @param string $content
     */
    public static function saveDescription(string $path, string $content)
    {
        $descriptionPath = $path.DIRECTORY_SEPARATOR.'description.txt';
        $fs = new Filesystem();

        if (!$fs->exists($descriptionPath)) {
            $fs->dumpFile($descriptionPath, $content);
        }
    }

    /**
     * @param ClientHelper $client
     * @param string       $path
     * @param string       $imageURL
     */
    public static function saveCover(ClientHelper $client, string $path, string $imageURL)
    {
        preg_match("#\.\w+$#", $imageURL, $extension);

        $coverPath = $path.DIRECTORY_SEPARATOR."cover.$extension[0]";
        $fs = new Filesystem();
        if (!$fs->exists($coverPath)) {
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
        $fs = new Filesystem();
        if (!$fs->exists($videoPath)) {
            $videoResource = fopen($videoPath, 'w+');
            $client->downloadResource($videoURL, $videoResource);
        }
    }
}
