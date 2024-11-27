<?php

declare(strict_types=1);

namespace App\Domain1\Presentation\Console;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:test:elasticsearch',
    description: 'Test Elasticsearch connection and basic operations'
)]
final class ElasticsearchTestCommand extends Command
{
    private const ELASTICSEARCH_HOST = 'es-elasticsearch:9200';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $baseUrl = sprintf('http://%s', self::ELASTICSEARCH_HOST);

        // Test 1: Check cluster health
        $io->section('Testing Elasticsearch Connection');
        $health = $this->makeRequest($baseUrl . '/_cluster/health');
        $io->success(sprintf('Cluster Status: %s', $health['status'] ?? 'N/A'));

        // Test 2: Create an index
        $io->section('Creating test index');
        $indexName = 'test-index-' . time();
        $this->makeRequest($baseUrl . '/' . $indexName, 'PUT');
        $io->success(sprintf('Index "%s" created', $indexName));

        // Test 3: Index a document
        $io->section('Indexing test document');
        $document = [
            'title' => 'Test Document',
            'content' => 'This is a test document for Elasticsearch',
            'timestamp' => date('c'),
        ];
        $response = $this->makeRequest($baseUrl . '/' . $indexName . '/_doc', 'POST', $document);
        $documentId = $response['_id'] ?? null;
        $io->success(sprintf('Document indexed with ID: %s', $documentId));

        // Test 4: Search for the document
        $io->section('Searching for document');
        $searchQuery = [
            'query' => [
                'match' => [
                    'content' => 'test document'
                ]
            ]
        ];
        $searchResult = $this->makeRequest($baseUrl . '/' . $indexName . '/_search', 'GET', $searchQuery);
        $hits = $searchResult['hits']['total']['value'] ?? 0;
        $io->success(sprintf('Found %d document(s)', $hits));

        // Test 5: Clean up - delete the test index
        $io->section('Cleaning up');
        $this->makeRequest($baseUrl . '/' . $indexName, 'DELETE');
        $io->success('Test index deleted');

        return Command::SUCCESS;
    }

    private function makeRequest(string $url, string $method = 'GET', array $data = null): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($statusCode >= 400) {
            throw new \RuntimeException(sprintf(
                'Elasticsearch request failed with status code %d: %s',
                $statusCode,
                $response
            ));
        }

        return json_decode($response, true) ?? [];
    }
}
