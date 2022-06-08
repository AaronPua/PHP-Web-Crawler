<?php

namespace App\Services;

use DOMDocument;
use DOMNode;
use DOMNodeList;
use DOMXPath;

class CrawlerService
{
    /**
     * The starting URL for the crawling process.
     * 
     * @var string 
     */
    protected string $startingURL;

    /**
     * The current URL of the page being crawled.
     * 
     * @var string 
     */
    protected string $currentURL;

    /**
     *  All crawled pages containing the following Key-Value pair:
     *  Key: page URL
     *  Value: [httpCode, pageLoadTime, wordCount, titleLength]
     * 
     * @var array 
     * 
     */
    protected array $crawledPages = [];

    /**
     * Unique images from all crawled pages.
     *
     * @var array
     */
    protected array $uniqueImages = [];
    
    /**
     * Unique internal links from all crawled pages.
     *
     * @var array
     */
    protected array $internalLinks = [];

    /**
     * Unique external links from all crawled pages.
     *
     * @var array
     */
    protected array $externalLinks = [];

    /**
     * Instantiate a new CrawlerService instance.
     */
    public function __construct()
    {
        
    }

    /**
     * Set the starting page's URL for the beginning of the crawl process.
     *
     * @param string $startingURL
     * @return void
     */
    public function setStartingURL(string $startingURL): void
    {
        $this->startingURL = $startingURL;

        // Designate the starting URL as an internal link.
        $this->internalLinks[] = $this->startingURL;
    }

    /**
     * Get the results for all the pages crawled.
     *
     * @return array
     */
    public function getCrawlResults(): array
    {
        return [
            'pages' => $this->crawledPages,
            'average' => [
                'pageLoadTime' => $this->average($this->crawledPages, 'pageLoadTime'),
                'titleLength' => $this->average($this->crawledPages, 'titleLength'),
                'wordCount' => $this->average($this->crawledPages, 'wordCount')
            ],
            'unique' => [
                'pages' => count($this->crawledPages),
                'images' => count($this->uniqueImages),
                'internalLinks' => count($this->internalLinks),
                'externalLinks' => count($this->externalLinks),
            ]
        ];
    }

    /**
     * Execute page crawls based on the user specified number of pages.
     *
     * @param integer $limit
     * @return void
     */
    public function executeCrawl(int $limit): void
    {
        for ($i = 0; $i < $limit ; $i++) {
            // Set the current URL of the page being crawled.
            $this->currentURL = $this->internalLinks[$i];

            // Set the current URL as the key and results of the crawl as the value.
            $this->crawledPages[$this->currentURL] = $this->crawlPage($this->currentURL);
        }
    }

    /**
     * Crawl page to obtain:
     *  1. HTTP Status Code
     *  2. Page Load Time
     *  3. Word Count
     *  4. Title Length
     * 
     * @return array
     */
    public function crawlPage(string $url): array
    {
        list($httpCode, $pageContent, $pageLoadTime) = $this->getPageContent($url);

        list($wordCount, $titleLength) = $this->crawlPageContent($pageContent);
        
        return [
            'httpCode' => $httpCode,
            'pageLoadTime' => $pageLoadTime,
            'wordCount' => $wordCount,
            'titleLength' => $titleLength
        ];
    }

    /**
     * Get the contents of the page to be crawled.
     * cURL is the library that lets you make HTTP requests in PHP.
     * @param string $url
     * @return array
     */
    public function getPageContent(string $url): array
    {
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
        ];

        $startTime = microtime(true);
        
        $curl = curl_init();
        curl_setopt_array($curl, $options);

        // Make the HTTP request and get the HTML response for the page.
        $pageContent = curl_exec($curl);

        // Get the HTTP Status Code for the page.
        $responseCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

        curl_close($curl);
        $endTime = microtime(true);
        
        // Get the page load time and round up to 2 decimal places.
        $pageLoadTime = round(($endTime - $startTime), 2);

        return [ $responseCode, $pageContent, $pageLoadTime ];
    }


    /**
     * Crawl through page content to obtain:
     *  1. Unique Images
     *  2. Unique Internal and External Links
     *  3. Word Count
     *  4. Title Length
     *
     * @param string $content
     * @return array
     */
    public function crawlPageContent(string $content): array
    {   
        $dom = new DOMDocument();
        $dom->loadHTML($this->cleanHTML($content));
        $xpath = new DOMXPath($dom);

        $images = $xpath->query('//img');  // Look for <img> tags.
        $links = $xpath->query('//a');     // Look for <a> tags.
        $title = $xpath->query('//title'); // Look for <title> tags.
        $body = $xpath->query('//body');   // Look for <body> tags.

        $this->crawlImages($images);
        $this->crawlLinks($links);

        return [
            $this->getWordCount($body),
            $this->getTitleLength($title),
        ];
    }

    /**
     * Clean up HTML to prepare it for crawling.
     *
     * @param string $html
     * @return string
     */
    public function cleanHTML(string $html): string
    {
        $dom = new DOMDocument();

        // Disable error reporting to resume execution. 
        // A bug with DOMDocument: https://stackoverflow.com/a/6090728
        libxml_use_internal_errors(true);

        $dom->loadHTML($html);
        libxml_clear_errors(); // Clear internal error buffer to free up memory.

        $xpath = new DOMXPath($dom); // XPath object to parse HTML.

        $scriptTags = $xpath->query('//script'); // Look for <script> tags.
        $styleTags = $xpath->query('//style');   // Look for <style> tags.

        // Remove <script> tags.
        foreach(iterator_to_array($scriptTags) as $scriptNode) {
            $scriptNode->parentNode->removeChild($scriptNode);
        }

        // Remove <style> tags.
        foreach(iterator_to_array($styleTags) as $styleNode) {
            $styleNode->parentNode->removeChild($styleNode);
        }

        return $dom->saveHTML();
    }

    /**
     * Get the absolute URL depending on the case.
     *
     * @param string $url
     * @return string
     */
    public function getAbsoluteURL(string $url): string
    {
        // Check the value at first index of the url string.
        switch(substr($url, 0, 1)) {
            // Indicates linking to a certain section within the same page.
            case '#':
                return $this->currentURL;
            
            // Indicates a relative url.
            case '/':
                return $this->getParsedURL() . $url;

            // If it's an external link, return the entire url.
            default:
                return $url;    
        }
    }

    /**
     * Get a parsed version of the starting URL with scheme and host. Example:
     * Scheme: http or https
     * Host: agencyanalytics.com
     * @return string
     */
    public function getParsedURL(): string
    {
        $parsedUrl = parse_url($this->startingURL);

        return $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
    }

    /**
     * Crawl for unique internal and external links.
     *
     * @param DOMNodeList $links
     * @return void
     */
    public function crawlLinks(DOMNodeList $links): void
    {
        foreach($links as $link) {
            $href = $link->getAttribute('href');

            if($href) {
                // Remove trailing slash for the url.
                $hrefLink = rtrim($this->getAbsoluteURL($href), '/');

                // Determine if a link is internal or external by matching against the starting URL.
                $linkType = str_contains($hrefLink, $this->getParsedURL()) === true ? 'internal' : 'external';
    
                if($linkType == 'internal' AND !in_array($hrefLink, $this->internalLinks))
                    $this->internalLinks[] = $hrefLink;

                if($linkType == 'external' AND !in_array($hrefLink, $this->externalLinks))
                    $this->externalLinks[] = $hrefLink;
            }
        }
    }

    /**
     * Crawl for unique images.
     *
     * @param DOMNodeList $images
     * @return void
     */
    public function crawlImages(DOMNodeList $images): void
    {
        foreach($images as $image) {
            // Look for the image source in 'data-src', fallback to looking in 'src' if 'data-src' is not present.
            $imageSrc = $image->getAttribute('data-src') ?? $image->getAttribute('src');

            if($imageSrc AND !in_array($imageSrc, $this->uniqueImages))
                $this->uniqueImages[] = $imageSrc;
        }
    }

    /**
     * Get the length of the page's title.
     *
     * @param DOMNodeList $title
     * @return int
     */
    public function getTitleLength(DOMNodeList $title): int
    {
        return strlen($title->item(0)->nodeValue);
    }

    /**
     * Get the number of words in the page.
     *
     * @param DOMNodeList $body
     * @return int
     */
    public function getWordCount(DOMNodeList $body): int
    {
        return str_word_count($this->getNodeText($body->item(0)));
    }

    /**
     * Recursively search into children of the node for DOMText node and join them with a whitespace.
     * Adapted from https://stackoverflow.com/a/37964731
     * @param DOMNode $node
     * @return string
     */
    public function getNodeText(DOMNode $node): string
    {
        if (is_a($node, "DOMText"))
            return trim($node->nodeValue);

        $nodeValues = [];
        foreach ($node->childNodes as $child) {
            $nodeText = $this->getNodeText($child);
            if ($nodeText != "")
                $nodeValues[] = $nodeText;
        }

        return trim(implode(" ", $nodeValues));
    }

    /**
     * Recursively search for average if an array column is specified.
     *
     * @param Array $array
     * @param integer|string|null| $column
     * @return int|float
     */
    public function average(Array $array, int|string|null $column = null): int|float
    {
        if($column)
            return $this->average(array_column($array, $column));

        return array_sum($array) / count($array);
    }
}