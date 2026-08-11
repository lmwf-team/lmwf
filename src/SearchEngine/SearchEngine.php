<?php

declare(strict_types=1);

namespace LM\WebFramework\SearchEngine;

final readonly class SearchEngine
{
    /**
     * @param array<string, string> $result
     * @param list<Searchable> $searchables
     */
    public function rankResult(
        SearchQuery $query,
        array $result,
        array $searchables,
    ): float {
        $rank = .0;
        foreach ($searchables as $s) {
            if (key_exists($s->name, $result) && is_string($result[$s->name])) {
                $k = 0;
                foreach ($query->getKeywords() as $kw) {
                    if (false !== stripos($result[$s->name], $kw)) {
                        $k += mb_strlen($kw);
                    }
                }
                $ratio = $k / $query->getTotalLength();
                $rank += (exp($ratio ** 2) - 1) / (exp(1) - 1) * $s->importance;
            }
        }
        return $rank;
    }
}
