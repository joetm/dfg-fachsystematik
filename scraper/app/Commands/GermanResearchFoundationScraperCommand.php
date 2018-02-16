<?php

require __DIR__ . '/../../vendor/autoload.php';

use GuzzleHttp\Client;
use App\Commands\Command;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class WebScraperCommand
 */
class GermanResearchFoundationScraperCommand extends Command
{
    /**
     * @var bool
     */
    private $debug;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var float
     */
    private $timeout;

    /**
     * The base url.
     *
     * @var string
     */
    private $baseUrl = 'http://www.dfg.de';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "usage: php DfgScraperCommand filename\r\n";

    /**
     * WebScraperCommand constructor.
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->debug = isset($options['debug']) ? $options['debug'] : false;
        $this->timeout = isset($options['timeout']) ? $options['timeout'] : 60;
    }

    /**
     * Create a new endpoint.
     *
     * @param string $baseUrl
     */
    private function createClient(string $baseUrl)
    {
        $options = [
            'base_uri' => $baseUrl,
            'debug' => $this->debug,
            'allow_redirection' => false,
            'timeout'  => $this->timeout,
        ];

        $this->client = new Client($options);
    }

    /**
     * Get project list.
     *
     * @return array
     */
    private function getBoards()
    {
        //$uri = '/dfg_profil/gremien/fachkollegien/faecher/index.jsp';
        $uri = 'en/dfg_profile/statutory_bodies/review_boards/subject_areas/index.jsp';

        $response = $this->client->get($uri);

        $crawler = new Crawler($response->getBody()->getContents());
        $boards = $crawler->filter('table')->each(function (Crawler $node, $index) {
            return [
                'id' => ($index+1)*100,
                'title' => $node->filter('caption')->text(),
                'reviewBoards' => $node->filter('.fachkolleg')->each(function (Crawler $node) {
                    $a = $node->filter('a');

                    return [
                        'id' => (int)$node->filter('td')->first()->text(),
                        'title' => $a->text(),
                        'href' => $this->baseUrl . $a->extract(['href'])[0],
                        'subjectAreas' => $node->filter('.subKat > a, .fachInhalt > a')->each(function (Crawler $node) {
                            return [
                                'id' => substr($node->extract(['href'])[0], -6),
                                'title' => $node->text(),
                                'href' => $this->baseUrl . $node->extract(['href'])[0]
                            ];
                        })
                    ];
                })
            ];
        });

        return $boards;
    }

    /**
     * Save content in json format.
     *
     * @param $filename
     * @param $content
     */
    private function save($filename, $content)
    {
        $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;

        file_put_contents($filename, json_encode($content, $options));
    }

    /**
     * Execute the console command.
     *
     * @param int $argc
     * @param array $argv
     */
    public function handle(int $argc, array $argv)
    {
        if ($argc == 2) {
            $filename = $argv[1];

            $this->createClient($this->baseUrl);

            $data = [
                'title' => 'German Research Foundation (DFG) Boards',
                'description' => 'This document contains the DFG board structure.',
                'created' => date('Y-m-d H:i:s'),
                'boards' => $this->getBoards()
            ];

            $this->save($filename, $data);

        } else {
            echo $this->signature;
        }
    }
}

$command = new GermanResearchFoundationScraperCommand();

$command->handle($argc, $argv);