# PHP Web Crawler
Using PHP, build a web crawler to display information about a given website.

- Crawl 4-6 pages of our website [agencyanalytics.com](https://agencyanalytics.com "agencyanalytics.com") given a single entry point.  Once the crawl is complete, display the following results:

  - Number of pages crawled 
  - Number of a unique images 
  - Number of unique internal links 
  - Number of unique external links 
  - Average page load in seconds 
  - Average word count 
  - Average title length


- Display a table that shows each page you crawled and the HTTP status code.

- Deploy to a server that can be accessed over the internet. You can use any host of your choosing such as AWS, Google Cloud, Heroku, Azure etc. Be sure to include the url in your submission.

# Requirements
- The app is built with PHP 
- The crawler is built for this challenge and not from a library 
- The app is properly deployed to a hosting service Code is hosted in a public repository, like Github.com


- Bonus: Use of PHP 7 or 8 and its features 
- Bonus: Use of a framework such as Laravel or Phalcon

# Deployment
The app has been deployed to a Heroku server: https://afternoon-hamlet-64717.herokuapp.com

# Environment Setup
### Install via Composer
The app has been setup using Composer and ran on Laravel's local development server using the Artisan CLI's `serve` command.

Detailed instructions can be found at https://laravel.com/docs/9.x#installation-via-composer.

1. Clone the github repository to a directory of your choice.
~~~
https://github.com/AaronPua/PHP-Web-Crawler.git
~~~

2. In the terminal, navigate to that directory.
~~~
cd Projects/PHPWebCrawler
~~~

3. Make a copy of the `.env.example` file and rename to `.env`.
~~~
.env
~~~

4. Install the necessary packages.
~~~
npm install
~~~
~~~
composer install
~~~
~~~
php artisan key:generate
~~~

5. Run the Artisan CLI's `serve` command.
~~~
php artisan serve
~~~

6. Access the app in the browser.
~~~
http://localhost:8000
~~~

# Notes
- This app is built using `Laravel 9.16.0` and `PHP 8.1.6`.
- The front-end of the app is built with the help of [TailwindCSS](https://tailwindcss.com/).
- The back-end of the app utilized the concept of `Service Classes` to decouple business logic from the controller. Find out more:
  - https://farhan.dev/tutorial/laravel-service-classes-explained/#service-classes-to-the-rescue
  - https://blackdeerdev.com/laravel-services-pattern