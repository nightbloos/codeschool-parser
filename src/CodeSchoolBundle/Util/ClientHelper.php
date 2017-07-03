<?php

declare(strict_types=1);

namespace CodeSchoolBundle\Util;

use DiDom\Document;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Class ClientHelper.
 */
class ClientHelper
{
    const BASE_URL_PATH = 'https://www.codeschool.com/';
    const LOGIN_URL_PATH = 'https://www.codeschool.com/users/sign_in';

    /** @var Client */
    private $cookieJar;
    /** @var CookieJar */
    private $webClient;

    /**
     * ClientHelper constructor.
     */
    public function __construct()
    {
        $this->cookieJar = new CookieJar();
        $this->webClient = new Client([
            'base_uri' => self::BASE_URL_PATH,
            'cookies' => true,
        ]);
    }

    /**
     * @param InputInterface $input
     *
     * @throws \Exception
     */
    public function authUser(InputInterface $input)
    {
        $userName = $input->getOption('username');
        $password = $input->getOption('password');

        if (!$userName || !$password) {
            throw  new \Exception('Credentials are missing');
        }
        $result = $this->webClient->request('GET', self::LOGIN_URL_PATH, ['cookies' => $this->cookieJar]);
        $document = new Document($result->getBody()->getContents());

        $token = $document->find('input[name=authenticity_token]')[0]->getAttribute('value');
        $client = $this->webClient->request('POST', self::LOGIN_URL_PATH, [
            'cookies' => $this->cookieJar,
            'form_params' => [
                'authenticity_token' => $token,
                'user' => [
                    'remember_me' => true,
                    'login' => $userName,
                    'password' => $password,
                ],
            ], ]);

        if (!preg_match('#<title>Dashboard | Code School</title>#isu', $client->getBody()->getContents())) {
            throw new \Exception('Invalid credentials');
        }
    }

    /**
     * @param string $url
     * @param bool   $disableBase
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getRequest($url, $disableBase = false)
    {
        return $this->webClient->request('GET', ($disableBase ? '' : self::BASE_URL_PATH).$url, [
            'cookies' => $this->cookieJar,
        ]);
    }

    /**
     * @param $mediaURL
     * @param $resource
     */
    public function downloadResource($mediaURL, $resource)
    {
        $this->webClient->request('GET', $mediaURL, [
            'cookies' => $this->cookieJar,
            'sink' => $resource,
        ]);
    }
}
