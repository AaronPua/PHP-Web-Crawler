<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Services\CrawlerService;

class CrawlerController extends Controller
{
    /** @var CrawlerService */
    protected $crawlerService;

    /**
     * Instantiate a new controller instance.
     * 
     * @param CrawlerService $crawlerService
     */
    public function __construct(CrawlerService $crawlerService)
    {
        $this->crawlerService = $crawlerService;
    }

    /**
     * Start the process of crawling a specified number of pages.
     *
     * @param Request $request
     * @return View
     */
    public function startPageCrawl(Request $request): View
    {
        $validatedData = $request->validate([
            'starting_url' => 'required|url',
            'num_to_crawl' => 'required|numeric',
        ]);

        // Remove trailing slash for the url.
        $trimmedURL = rtrim($validatedData['starting_url'], '/');

        $this->crawlerService->setStartingURL($trimmedURL);

        // Execute the page crawling process based on user specified number of pages.
        $this->crawlerService->executeCrawl($validatedData['num_to_crawl']);

        $crawlResults = $this->crawlerService->getCrawlResults();
        
        return view('crawler', [ 'crawlResults' => $crawlResults ]);
    }
}
