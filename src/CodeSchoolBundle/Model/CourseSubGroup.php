<?php

namespace CodeSchoolBundle\Model;

use CodeSchoolBundle\Util\ClientHelper;
use CodeSchoolBundle\Util\FileHelper;
use DiDom\Element;

/**
 * Class CourseSubGroup.
 */
class CourseSubGroup
{
    /** @var string */
    private $name;

    /** @var string */
    private $description;

    /** @var Course[] */
    private $courses = [];

    /** @var string */
    private $directoryPath;

    /**
     * CourseSubGroup constructor.
     *
     * @param Element $subGroupDomElement
     * @param string  $directoryPath
     */
    public function __construct(Element $subGroupDomElement, string $directoryPath)
    {
        $this->name = trim($subGroupDomElement->find('div.js-pathFilter-subgroup h2 span.prxs')[0]->text());
        $this->description = trim($subGroupDomElement->find('div.js-pathFilter-subgroup p.tss')[0]->text());
        $this->directoryPath = $directoryPath.DIRECTORY_SEPARATOR.FileHelper::getSlug($this->getName());
    }

    /**
     * @param string $name
     *
     * @return CourseSubGroup
     */
    public function setName(string $name): CourseSubGroup
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $description
     *
     * @return CourseSubGroup
     */
    public function setDescription(string $description): CourseSubGroup
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @param Course $course
     *
     * @return CourseSubGroup
     */
    public function addCourse(Course $course): CourseSubGroup
    {
        $this->courses[] = $course;

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
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return Course[]
     */
    public function getCourses()
    {
        return $this->courses;
    }

    /**
     * @param string $directoryPath
     *
     * @return CourseSubGroup
     */
    public function setDirectoryPath(string $directoryPath): CourseSubGroup
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
     * @param Element[]|\DOMElement[] $coursesDomElements
     * @param ClientHelper            $client
     */
    public function parseCourses($coursesDomElements, ClientHelper $client)
    {
        FileHelper::createDir($this->getDirectoryPath());
        echo "\t\t\t Scanning -> $this->name \n";
        echo sprintf("\t\t\t -> found %s courses(s) \n", count($coursesDomElements));
        foreach ($coursesDomElements as $coursesDomElement) {
            if (trim($coursesDomElement->find('article div.course-content div.mbxs span.label')[0]->text()) !== 'Coming Soon') {
                $course = new Course($coursesDomElement, $this->getDirectoryPath());
                $course->parseVideos($client);
                $course->generateMeta($client);
                $this->addCourse($course);
            }
        }
    }

    public function generateMeta()
    {
        $description = 'Name: '.$this->getName()."\r\n";
        $description .= 'Description: '.$this->getDescription()."\r\n";

        FileHelper::saveDescription($this->getDirectoryPath(), $description);
    }
}
