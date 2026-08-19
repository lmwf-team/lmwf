<?php

declare(strict_types=1);

namespace LMWF\Http\Routing;

use LMWF\Http\DataStructures\PageEntConf;
use Psr\Container\ContainerInterface;

final readonly class EntPageTitleFormatter
{
    public function __construct(
        private ContainerInterface $container,
    ) {
    }

    public function format(PageEntConf $entConf, string $entId): string|FormatErr
    {
        $matches = [];
        $pregMatchResult = preg_match_all('/{{ ([a-z][a-z_]+[a-z]) }}/', $entConf->title, $matches);

        if (false === $pregMatchResult) {
            return FormatErr::MatchErr;
        }

        $repo = $this->container->get($entConf->repoFqcn);
        $ent = $repo->find($entId);
        if (null === $ent) {
            return FormatErr::EntNotFound;
        }
        $title = $entConf->title;
        for ($i = 0; $i < $pregMatchResult; $i++) {
            $propertyName = $this->getNonDecimalIntStr($matches[1][$i]);
            if (null === $propertyName) {
                return FormatErr::PropertyNameIsNotStr;
            }

            $title = str_replace(
                $matches[0][$i],
                $ent->getString($propertyName),
                $title,
            );
        }

        return $title;
    }

    /**
     * @return non-decimal-int-string
     * @todo Should be moved to its own class or as a global function?
     * @todo Should return Err instead.
     */
    private function getNonDecimalIntStr(string $value): ?string
    {
        $array = [$value => null];
        if (in_array($value, array_keys($array), strict: true)) {
            return $value;
        }
        return null;
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses
enum FormatErr
{
    case EntNotFound;
    case MatchErr;
    case PropertyNameIsNotStr;
    case StrReplaceUnknownError;
}
