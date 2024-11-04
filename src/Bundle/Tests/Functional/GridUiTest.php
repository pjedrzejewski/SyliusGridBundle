<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Bundle\GridBundle\Tests\Functional;

use ApiTestCase\ApiTestCase;
use Coduo\PHPMatcher\Backtrace\VoidBacktrace;
use Coduo\PHPMatcher\Matcher;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

final class GridUiTest extends ApiTestCase
{
    private array $data;

    protected function setUp(): void
    {
        parent::setUp();

        $this->data = $this->loadFixturesFromFile('fixtures.yml');
    }

    /** @test */
    public function it_shows_authors_grid(): void
    {
        $this->client->request('GET', '/authors/');
        $response = $this->client->getResponse();

        $this->assertResponseCode($response, Response::HTTP_OK);

        $this->assertCount(10, $this->getAuthorNamesFromResponse());
    }

    /** @test */
    public function it_sorts_authors_by_name_ascending_by_default(): void
    {
        $this->client->request('GET', '/authors/?limit=100');

        $names = $this->getAuthorNamesFromResponse();

        $sortedNames = $names;
        sort($names);

        $this->assertSame($sortedNames, $names);
    }

    /** @test */
    public function it_sorts_authors_by_name_descending(): void
    {
        $this->client->request('GET', '/authors/?sorting[name]=desc&limit=100');

        $names = $this->getAuthorNamesFromResponse();

        $sortedNames = $names;
        rsort($names);

        $this->assertSame($sortedNames, $names);
    }

    /** @test */
    public function it_paginates_authors_by_10_by_default(): void
    {
        $this->client->request('GET', '/authors/');

        $this->assertCount(10, $this->getAuthorNamesFromResponse());
    }

    /** @test */
    public function it_paginates_authors_by_5_or_15(): void
    {
        $this->client->request('GET', '/authors/?limit=5');

        $this->assertCount(5, $this->getAuthorNamesFromResponse());

        $this->client->request('GET', '/authors/?limit=15');

        $this->assertCount(15, $this->getAuthorNamesFromResponse());
    }

    /** @test */
    public function it_filters_books_by_title(): void
    {
        $this->client->request('GET', sprintf(
            '/books/?criteria[title][type]=equal&criteria[title][value]=%s',
            urlencode('Book 5'),
        ));

        $titles = $this->getBookTitlesFromResponse();

        $this->assertCount(1, $titles);
        $this->assertSame('BOOK 5', $titles[0]);
    }

    /** @test */
    public function it_filters_books_by_title_with_contains(): void
    {
        $this->client->request('GET', sprintf(
            '/books/?criteria[title][type]=contains&criteria[title][value]=%s',
            urlencode('jurassic'),
        ));

        $titles = $this->getBookTitlesFromResponse();

        $this->assertCount(1, $titles);
        $this->assertSame('JURASSIC PARK', $titles[0]);
    }

    /** @test */
    public function it_filters_books_by_author(): void
    {
        $authorId = $this->data['author_michael_crichton']->getId();

        $this->client->request('GET', sprintf('/books/?criteria[author][]=%d', $authorId));

        $titles = $this->getBookTitlesFromResponse();

        $this->assertCount(2, $titles);
        $this->assertSame('JURASSIC PARK', $titles[0]);
    }

    /** @test */
    public function it_filters_books_by_authors(): void
    {
        $firstAuthorId = $this->data['author_michael_crichton']->getId();
        $secondAuthorId = $this->data['author_john_watson']->getId();

        $this->client->request('GET', sprintf('/books/?criteria[author][]=%d&criteria[author][]=%d', $firstAuthorId, $secondAuthorId));

        $titles = $this->getBookTitlesFromResponse();

        $this->assertCount(3, $titles);
        $this->assertSame('A STUDY IN SCARLET', $titles[0]);
    }

    /** @test */
    public function it_filters_books_by_authors_nationality(): void
    {
        $authorNationalityId = $this->data['author_michael_crichton']->getNationality()->getId();

        $this->client->request('GET', sprintf('/books/?criteria[nationality]=%d', $authorNationalityId));

        $titles = $this->getBookTitlesFromResponse();

        $this->assertCount(2, $titles);
        $this->assertSame('JURASSIC PARK', $titles[0]);
    }

    /** @test */
    public function it_filters_books_by_author_and_currency(): void
    {
        $authorId = $this->data['author_michael_crichton']->getId();

        $this->client->request('GET', sprintf('/books/?criteria[author]=%d&criteria[currencyCode]=%s', $authorId, 'EUR'));

        $titles = $this->getBookTitlesFromResponse();

        $this->assertCount(1, $titles);
        $this->assertSame('JURASSIC PARK', $titles[0]);
    }

    /** @test */
    public function it_sorts_books_ascending_by_author(): void
    {
        $this->client->request('GET', '/books/?sorting[author]=asc&limit=100');

        $names = $this->getBookAuthorsFromResponse();

        $sortedNames = $names;
        sort($names);

        $this->assertSame($sortedNames, $names);
    }

    /** @test */
    public function it_sorts_books_descending_by_authors_nationality(): void
    {
        $this->client->request('GET', '/books/?sorting[nationality]=desc&limit=100');

        $names = $this->getBookAuthorNationalitiesFromResponse();

        $sortedNames = $names;
        rsort($names);

        $this->assertSame($sortedNames, $names);
    }

    /** @test */
    public function it_filters_books_by_author_when_an_author_association_is_used_in_join_in_query_builder(): void
    {
        $authorId = $this->data['author_michael_crichton']->getId();

        $this->client->request('GET', sprintf('/by-american-authors/books/?criteria[author]=%d', $authorId));

        $titles = $this->getBookTitlesFromResponse();

        $this->assertCount(2, $titles);
        $this->assertSame('Jurassic Park', $titles[0]);
    }

    /** @test */
    public function it_sorts_authors_using_table_alias_defined_in_query_builder(): void
    {
        $this->client->request('GET', '/by-american-authors/books/?sorting[author]=asc');

        $titles = $this->getBookTitlesFromResponse();

        $this->assertCount(2, $titles);
        $this->assertSame('Jurassic Park', $titles[0]);
    }

    /** @test */
    public function it_filters_books_by_author_when_an_author_is_used_in_join_in_query_builder_without_association_in_the_mapping(): void
    {
        $authorId = $this->data['author_john_watson']->getId();

        $this->client->request('GET', sprintf('/by-english-authors/books/?criteria[author]=%d', $authorId));

        $titles = $this->getBookTitlesFromResponse();

        $this->assertCount(1, $titles);
        $this->assertSame('A Study in Scarlet', $titles[0]);
    }

    /** @test */
    public function it_includes_all_rows_even_when_sorting_by_a_nullable_path(): void
    {
        $this->client->request('GET', '/authors/');
        $totalItemsCountBeforeSorting = count($this->getAuthorNamesFromResponse());

        $this->client->request('GET', '/authors/?sorting[nationality]=desc');

        $totalItemsCountAfterSorting = count($this->getAuthorNamesFromResponse());

        $this->assertSame($totalItemsCountBeforeSorting, $totalItemsCountAfterSorting);
    }

    /** @return string[] */
    private function getBookTitlesFromResponse(): array
    {
        return $this->getCrawler()
            ->filter('[data-test-title]')
            ->each(
                fn (Crawler $node): string => $node->text(),
            );
    }

    /** @return string[] */
    private function getBookAuthorsFromResponse(): array
    {
        return $this->getCrawler()
            ->filter('[data-test-author]')
            ->each(
                fn (Crawler $node): string => $node->text(),
            );
    }

    /** @return string[] */
    private function getBookAuthorNationalitiesFromResponse(): array
    {
        return $this->getCrawler()
            ->filter('[data-test-nationality]')
            ->each(
                fn (Crawler $node): string => $node->text(),
            );
    }

    /** @return string[] */
    private function getAuthorNamesFromResponse(): array
    {
        return $this->getCrawler()
            ->filter('[data-test-name]')
            ->each(
                fn (Crawler $node): string => $node->text(),
            );
    }

    private function getCrawler(): Crawler
    {
        return $this->client->getCrawler();
    }

    protected function buildMatcher(): Matcher
    {
        return $this->matcherFactory->createMatcher(new VoidBacktrace());
    }
}
