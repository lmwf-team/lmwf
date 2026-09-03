<?php

declare(strict_types=1);

namespace LMWF\Http\Factory;

use LMWF\DataStructures\Page;
use LMWF\ErrorHandling\ExceptionCode;
use LMWF\Http\DataStructures\EntPageConf;
use LMWF\Http\DataStructures\IPageConf;
use LMWF\Http\DataStructures\StaticPageConf;
use LMWF\Http\Routing\{EntPageTitleFormatter, FormatErr};
use LMWF\Http\Routing\Route;
use UnexpectedValueException;

final readonly class PageFactory
{
    public function __construct(
        private EntPageTitleFormatter $formatter,
    ) {
    }

    /**
     * @return null|Page|PageEntTitleErr The page extracted from the route and
     * its definition, null if the route does not provide a page,
     * or PageEntTitleErr if there was a problem formatting the title for the
     * page when an entity was requested.
     * @todo Find a way to type that return type depends on routedef's pageTitle
     * type.
     */
    public function create(Route $route): null|Page|PageEntTitleErr
    {
        $nParams = $route->nParams;

        if (null === $route->pageConf) {
            return null;
        }

        $mutRoute = $route;
        $nearestPageAncestor = null;
        while (null !== $mutRoute = $mutRoute->parent) {
            // If the current parent route has a page, we save it and break.
            if (null !== $nearestPageAncestor = $this->create($mutRoute)) {
                break;
            }
        }

        if ($nearestPageAncestor instanceof PageEntTitleErr) {
            return $nearestPageAncestor;
        }

        $mutUrl = $route->pageConf->getBaseUrl() . $route->path;

        if ($route->pageConf instanceof EntPageConf) {
            if (0 === $nParams) {
                throw new UnexpectedValueException(
                    'The "by-param" configuration for a route with no parameters cannot be an EntPageConf as it would expect a parameter.',
                    ExceptionCode::HTTP_FACTORY_PAGEFACTORY_ENT_PAGE_METADATA_CONF_WITH_0_PARAM->value,
                );
            }
            $titleResult = $this->formatter->format($route->pageConf->getTitle(), $route->params[$nParams - 1], $route->pageConf->repoFqcn);
            if ($titleResult instanceof FormatErr) {
                return new PageEntTitleErr($titleResult);
            }
        } else {
            $titleResult = $route->pageConf->getTitle();
        }


        return new Page(
            $nearestPageAncestor,
            $route->pageConf->getControllerFqcn(),
            $titleResult,
            $mutUrl,
            $route->pageConf->isIndexed(),
            $route->pageConf->isInHierarchy(),
        );
    }
}

// phpcs:ignore PSR1.Classes.ClassDeclaration.MultipleClasses
final readonly class PageEntTitleErr
{
    public function __construct(
        public FormatErr $formatErr,
    ) {
    }
}
