<?php

declare(strict_types=1);

namespace LM\WebFramework\SearchEngine;

use ArrayAccess;
use LM\WebFramework\DataStructures\SearchQuery;

class SearchEngine
{
    /**
     * @param array<string, string> $result
     * @param array<\LM\WebFramework\DataStructures\Searchable> $searchables
     */
    public function rankResult(
            SearchQuery $query,
            array $result,
            array $searchables,
        ): float {
        $rank = .0;
        foreach ($searchables as $s) {
            if (key_exists($s->getName(), $result) && is_string($result[$s->getName()])) {
                $k = 0;
                foreach ($query->getKeywords() as $kw) {
                    if (false !== stripos($result[$s->getName()], $kw)) {
                        $k += mb_strlen($kw);
                    }
                }
                $ratio = $k / $query->getTotalLength();
                $rank += (exp($ratio**2) - 1) / (exp(1) - 1) * $s->getImportance();
            }
        }
        return $rank;
    }
}