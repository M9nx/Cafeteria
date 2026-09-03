<?php

declare(strict_types=1);

namespace Cafeteria\Services;

use Cafeteria\DTO\ChecksFilter;
use Cafeteria\Core\Http\Response;

final class ReportExportService
{
    public function __construct(
        private readonly ReportQueryService $reports,
    ) {
    }

    public function export(ChecksFilter $filter): Response
    {
        $summary = $this->reports->summarize($filter);

        $handle = fopen('php://temp', 'w+');

        if ($handle === false) {
            throw new \RuntimeException('Unable to create CSV output.');
        }

        // UTF-8 BOM for better Excel compatibility.
        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv(
            $handle,
            [
                'User ID',
                'User',
                'Orders',
                'Total amount',
            ],
            ',',
            '"',
            '',
        );

        foreach (($summary['users'] ?? []) as $row) {
            fputcsv(
                $handle,
                [
                    $this->safeCsvCell($row['user_id'] ?? ''),
                    $this->safeCsvCell($row['user_name'] ?? ''),
                    $this->safeCsvCell($row['order_count'] ?? 0),
                    $this->safeCsvCell($row['total_amount'] ?? '0.00'),
                ],
                ',',
                '"',
                '',
            );
        }

        rewind($handle);

        $content = stream_get_contents($handle);

        fclose($handle);

        if ($content === false) {
            throw new \RuntimeException('Unable to read CSV output.');
        }

        return new Response(
            $content,
            200,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="checks-report.csv"',
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
            ],
        );
    }

    private function safeCsvCell(mixed $value): string
    {
        $value = (string) $value;

        if ($value === '') {
            return '';
        }

        $firstCharacter = $value[0];

        if (
            $firstCharacter === '='
            || $firstCharacter === '+'
            || $firstCharacter === '-'
            || $firstCharacter === '@'
        ) {
            return "'" . $value;
        }

        return $value;
    }
}