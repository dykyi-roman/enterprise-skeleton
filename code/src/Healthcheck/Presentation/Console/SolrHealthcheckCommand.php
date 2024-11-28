<?php

declare(strict_types=1);

namespace App\Healthcheck\Presentation\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:healthcheck:solr',
    description: 'Test Solr connection and basic operations'
)]
final class SolrHealthcheckCommand extends Command
{
    private const string SOLR_HOST = 'es-solr:8983';
    private const string CORE_NAME = 'default';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $baseUrl = sprintf('http://%s/solr', self::SOLR_HOST);

        try {
            // Test 1: Check Solr status
            $io->section('Testing Solr Connection');
            $status = $this->makeRequest($baseUrl.'/admin/info/system');

            /* @var array{lucene: array{solr-spec-version: string}, mode: string} $status */
            $io->success(sprintf(
                'Solr Version: %s, Mode: %s',
                $status['lucene']['solr-spec-version'] ?? 'N/A',
                $status['mode'] ?? 'N/A'
            ));

            // Test 2: Check core status
            $io->section('Checking core status');
            $coreStatus = $this->makeRequest($baseUrl.'/admin/cores?'.http_build_query([
                'action' => 'STATUS',
                'core' => self::CORE_NAME,
            ]));

            /** @var array{status: array<string, mixed>} $coreStatus */
            if (empty($coreStatus['status'][self::CORE_NAME])) {
                throw new \RuntimeException('Core "'.self::CORE_NAME.'" not found or not properly initialized');
            }

            $io->success(sprintf('Core "%s" is available', self::CORE_NAME));

            // Test 3: Add a document
            $io->section('Adding test document');
            $document = [
                'add' => [
                    'doc' => [
                        'id' => uniqid(),
                        'title' => 'Test Document',
                        'content' => 'This is a test document for Solr',
                        'timestamp' => date('c'),
                    ],
                ],
            ];

            $updateUrl = sprintf('%s/%s/update/json/docs?commit=true', $baseUrl, self::CORE_NAME);
            $this->makeRequest($updateUrl, 'POST', $document['add']['doc']);
            $io->success('Document added successfully');

            // Test 4: Search for document
            $io->section('Searching for document');
            $searchResult = $this->makeRequest($baseUrl.'/'.self::CORE_NAME.'/select?'.http_build_query([
                'q' => 'content:test',
                'wt' => 'json',
            ]));

            /** @var array{response: array{numFound: int}} $searchResult */
            $numFound = $searchResult['response']['numFound'] ?? 0;
            $io->success(sprintf('Found %d document(s)', $numFound));

            // Test 5: Delete all documents
            $io->section('Cleaning up');
            $deleteUrl = sprintf('%s/%s/update?commit=true', $baseUrl, self::CORE_NAME);
            $this->makeRequest(
                $deleteUrl,
                'POST',
                ['delete' => ['query' => '*:*']]
            );
            $io->success('All test documents deleted');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<array-key, mixed>
     *
     * @throws \RuntimeException
     */
    private function makeRequest(string $url, string $method = 'GET', ?array $data = null): array
    {
        $ch = curl_init();
        if (false === $ch) {
            throw new \RuntimeException('Failed to initialize CURL');
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if (null !== $data) {
            $jsonData = json_encode($data);
            if (false === $jsonData) {
                throw new \RuntimeException('Failed to encode JSON data');
            }

            $headers = [
                'Content-Type: application/json',
                'Content-Length: '.(string) strlen($jsonData),
            ];

            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);
        if (false === $response) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException("CURL Error: $error");
        }

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode >= 400) {
            throw new \RuntimeException(sprintf('Solr request failed with status code %d: %s', $statusCode, (string) $response));
        }

        /** @var array<array-key, mixed> $decoded */
        $decoded = json_decode((string) $response, true);
        if (JSON_ERROR_NONE !== json_last_error() || !is_array($decoded)) {
            throw new \RuntimeException('Failed to decode JSON response: '.(string) $response);
        }

        return $decoded;
    }
}
