<?php

namespace App\Http\Controllers;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMXPath;
use Illuminate\Http\Request;

class CrawlerController extends Controller
{
    public string $startingURL;

    public string $currentURL;

    public array $pages = [];

    public array $uniqueImages = [];
    
    public array $internalLinks = [];

    public array $externalLinks = [];

    public function startPageCrawl(Request $request)
    {
        $validatedData = $request->validate([
            'starting_url' => 'required|url',
            'num_to_crawl' => 'required|numeric',
        ]);

        $this->startingURL = rtrim($validatedData['starting_url'], '/');
        $this->internalLinks[] = $this->startingURL;

        for ($i = 0; $i < $validatedData['num_to_crawl']; $i++) {
            $this->currentURL = $this->internalLinks[$i];
            $this->pages[$this->currentURL] = $this->crawlPage($this->currentURL);
        }
        dd([
            'pages' => $this->pages,
            'average' => [
                'pageLoadTime' => $this->average($this->pages, 'pageLoadTime'),
                'titleLength' => $this->average($this->pages, 'titleLength'),
                'wordCount' => $this->average($this->pages, 'wordCount')
            ],
            'unique' => [
                'pages' => count($this->pages),
                'images' => count($this->uniqueImages),
                'internalLinks' => count($this->internalLinks),
                'externalLinks' => count($this->externalLinks),
            ]
        ]);
        
        return view('crawler', [
            'pages' => $this->pages,
            'average' => [
                'pageLoadTime' => $this->average($this->pageLoadTimes),
                'titleLength' => $this->average($this->titleLengths),
                'wordCount' => $this->average($this->wordCounts)
            ],
            'unique' => [
                'pages' => count($this->pages),
                'images' => count($this->uniqueImages),
                'internalLinks' => count($this->internalLinks),
                'externalLinks' => count($this->externalLinks),
            ]
        ]);
    }

    public function getPageContent(string $url)
    {
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ];

        $startTime = microtime(true);

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        
        $pageContent = curl_exec($curl);
        $responseCode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);

        curl_close($curl);

        $endTime = microtime(true);
        $pageLoadTime = round(($endTime - $startTime), 2);

        return [ $responseCode, $pageContent, $pageLoadTime ];
    }

    public function crawlPage(string $url)
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

    public function crawlPageContent(string $content)
    {   
        $dom = new DOMDocument();
        $dom->loadHTML($this->cleanHTML($content));
        $xpath = new DOMXPath($dom);

        $images = $xpath->query('//img');
        $links = $xpath->query('//a');
        $title = $xpath->query('//title');
        $body = $xpath->query('//body');

        $this->crawlImages($images);
        $this->crawlLinks($links);

        return [
            $this->getWordCount($body),
            $this->getTitleLength($title),
        ];
    }
    
    public function cleanHTML(string $html)
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);

        $dom->loadHTML($html);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);

        $scriptTags = $xpath->query('//script');
        $styleTags = $xpath->query('//style');

        foreach(iterator_to_array($scriptTags) as $scriptNode) {
            $scriptNode->parentNode->removeChild($scriptNode);
        }

        foreach(iterator_to_array($styleTags) as $styleNode) {
            $styleNode->parentNode->removeChild($styleNode);
        }

        return $dom->saveHTML();
    }

    public function getAbsoluteUrl(string $url)
    {
        // Check the value at first index of the url string
        switch(substr($url, 0, 1)) {
            // If the href link is indicates linking to a certain section of the same page
            case '#':
                return $this->currentURL;
            
            // If the href link indicates a relative url
            case '/':
                return $this->getParsedUrl() . $url;
        }

        // If it's an external link, return the entire url
        return $url;
    }

    public function getParsedUrl()
    {
        $parsedUrl = parse_url($this->startingURL);

        return $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
    }

    public function crawlLinks(DOMNodeList $links)
    {
        for($i = 0; $i < $links->length; $i++) {
            $this->getHrefLink($links->item($i));
        }
    }

    public function getHrefLink(DOMElement $link)
    {
        $href = $link->getAttribute('href');

        if($href) {
            $hrefLink = rtrim($this->getAbsoluteUrl($href), '/');

            $linkType = str_contains($hrefLink, $this->getParsedUrl()) === true ? 'internal' : 'external';
 
            if($linkType == 'internal' AND !in_array($hrefLink, $this->internalLinks)) {
                $this->internalLinks[] = $hrefLink;
            }

            if($linkType == 'external' AND !in_array($hrefLink, $this->externalLinks)) {
                $this->externalLinks[] = $hrefLink;
            }
        }
    }

    public function crawlImages(DOMNodeList $images)
    {
        for($i = 0; $i < $images->length; $i++) {
            $this->getImageSource($images->item($i));
        }
    }

    public function getImageSource(DOMElement $image)
    {
        $imageSrc = $image->getAttribute('data-src') ?? $image->getAttribute('src');

        if($imageSrc AND !in_array($imageSrc, $this->uniqueImages)) {
            $this->uniqueImages[] = $imageSrc;
        }
    }

    public function getTitleLength(DOMNodeList $title)
    {
        return strlen($title->item(0)->nodeValue);
    }

    public function getWordCount(DOMNodeList $body)
    {
        return str_word_count($this->getNodeText($body->item(0)));
    }

    public function getNodeText(DOMNode $node) {
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

    public function average(Array $array, $column = null) {
        if($column)
            return $this->average(array_column($array, $column));

        return array_sum($array) / count($array);
    }
}
