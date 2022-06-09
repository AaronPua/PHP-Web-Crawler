<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ __('crawler.page_title') }}</title>

        <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    </head>

    <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-900">
                <a href="/">{{ __('crawler.header_title') }}</a>
            </h1>
        </div>
    </header>

    <body class="bg-gray-100">
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="mt-5 md:mt-0 md:col-span-2">
                <form method="POST" action="{{ route('crawler.post') }}">
                    @csrf

                    <div class="shadow overflow-hidden sm:rounded-md">
                        <div class="px-4 py-5 bg-white sm:p-6">
                            <div class="grid grid-cols-6 gap-6">
                                <div class="col-span-6 sm:col-span-3">
                                    <label for="first-name" class="block text-sm font-medium text-gray-700">{{ __('crawler.form.website') }}</label>
                                    <input required type="text" id="starting_url" name="starting_url" placeholder="https://duckduckgo.com"
                                        class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>

                                <div class="col-span-6 sm:col-span-3">
                                    <label for="num_to_crawl" class="block text-sm font-medium text-gray-700">{{ __('crawler.form.num_to_crawl') }}</label>
                                    <select required type="text" id="num_to_crawl" name="num_to_crawl" 
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    @for ($i = 1; $i <= 6; $i++)
                                        <option value='{{ $i }}'>{{ $i }}</option>
                                    @endfor
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">{{ __('crawler.form.button_start') }}</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="px-4 py-6 sm:px-0">
                @if ($crawlResults)
                    <div class="border-4 border-dashed border-gray-200 rounded-lg">
                        <div class="container mx-auto px-6 pt-6 pb-3">
                            <div class="w-full overflow-hidden rounded-lg shadow-lg">
                                <div class="w-full overflow-x-auto">
                                    <table class="w-full">
                                        <thead>
                                            <tr class="text-md font-semibold tracking-wide text-left text-gray-900 bg-gray-200 border-b border-gray-600">
                                                <th class="px-4 py-3">{{ __('crawler.results.unique.pages_crawled') }}</th>
                                                <th class="px-4 py-3">{{ __('crawler.results.unique.images') }}</th>
                                                <th class="px-4 py-3">{{ __('crawler.results.unique.internal_links') }}</th>
                                                <th class="px-4 py-3">{{ __('crawler.results.unique.external_links') }}</th>
                                                <th class="px-4 py-3">{{ __('crawler.results.average.page_load') }}</th>
                                                <th class="px-4 py-3">{{ __('crawler.results.average.word_count') }}</th>
                                                <th class="px-4 py-3">{{ __('crawler.results.average.title_length') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white">
                                            <tr class="text-gray-700">
                                                <td class="px-4 py-3 border">{{ $crawlResults['unique']['crawledPages'] }}</td>
                                                <td class="px-4 py-3 border">{{ $crawlResults['unique']['images'] }}</td>
                                                <td class="px-4 py-3 border">{{ $crawlResults['unique']['internalLinks'] }}</td>
                                                <td class="px-4 py-3 border">{{ $crawlResults['unique']['externalLinks'] }}</td>
                                                <td class="px-4 py-3 border">{{ $crawlResults['average']['pageLoadTime'] }}</td>
                                                <td class="px-4 py-3 border">{{ $crawlResults['average']['wordCount'] }}</td>
                                                <td class="px-4 py-3 border">{{ $crawlResults['average']['titleLength'] }}</td>
                                            </tr>                                      
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="container mx-auto px-6 pt-3 pb-6">
                            <div class="w-full overflow-hidden rounded-lg shadow-lg">
                                <div class="w-full overflow-x-auto">
                                    <table class="w-full">
                                        <thead>
                                            <tr class="text-md font-semibold tracking-wide text-left text-gray-900 bg-gray-200 border-b border-gray-600">
                                                <th class="px-4 py-3">{{ __('crawler.results.crawled_pages.page') }}</th>
                                                <th class="px-4 py-3">{{ __('crawler.results.crawled_pages.http_code') }}</th>
                                                <th class="px-4 py-3">{{ __('crawler.results.crawled_pages.word_count') }}</th>
                                                <th class="px-4 py-3">{{ __('crawler.results.crawled_pages.title_length') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white">
                                            @foreach ($crawlResults['pages'] as $key => $value)
                                                <tr class="text-gray-700">
                                                    <td class="px-4 py-3 border">{{ $key }}</td>
                                                    <td class="px-4 py-3 border">{{ $value['httpCode'] }}</td>
                                                    <td class="px-4 py-3 border">{{ $value['wordCount'] }}</td>
                                                    <td class="px-4 py-3 border">{{ $value['titleLength'] }}</td>
                                                </tr>                                      
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="border-4 border-dashed border-gray-200 rounded-lg h-96"></div>
                @endif
            </div>

        </div>
    </body>
</html>
