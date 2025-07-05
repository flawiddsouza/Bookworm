<?php

namespace App\Classes;

class Formatter
{
    /**
     * Format series information for display.
     *
     * Input:
     * $seriesInfo = [
     *     ["name" => "The Power Fantasy", "index" => 1],
     *     ["name" => "The Power Fantasy", "index" => 2],
     *     ["name" => "The Power Fantasy", "index" => 3],
     *     ["name" => "The Power Fantasy", "index" => 5]
     * ];
     *
     * Output:
     * The Power Fantasy #1-3, #5
     *
     * @param array $seriesInfo
     * @return string
     */
    public static function formatSeriesInfo($seriesInfo)
    {
        if (empty($seriesInfo)) {
            return '';
        }

        // Group by series name
        $groupedSeries = [];
        foreach ($seriesInfo as $series) {
            $name = $series['name'];
            if (!isset($groupedSeries[$name])) {
                $groupedSeries[$name] = [];
            }
            $groupedSeries[$name][] = (int)$series['index'];
        }

        $result = [];
        foreach ($groupedSeries as $seriesName => $indices) {
            // Sort indices
            sort($indices);

            // Group consecutive numbers
            $ranges = [];
            $start = $indices[0];
            $end = $indices[0];

            for ($i = 1; $i < count($indices); $i++) {
                if ($indices[$i] == $end + 1) {
                    // Consecutive number
                    $end = $indices[$i];
                } else {
                    // Non-consecutive, save the current range
                    if ($start == $end) {
                        $ranges[] = "#$start";
                    } else {
                        $ranges[] = "#$start-$end";
                    }
                    $start = $end = $indices[$i];
                }
            }

            // Add the last range
            if ($start == $end) {
                $ranges[] = "#$start";
            } else {
                $ranges[] = "#$start-$end";
            }

            $result[] = $seriesName . ' ' . implode(', ', $ranges);
        }

        return implode(', ', $result);
    }
}
