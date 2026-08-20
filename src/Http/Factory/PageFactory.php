<?php

declare(strict_types=1);

namespace LMWF\Http\Factory;

use LMWF\DataStructures\Page;
use LMWF\Http\Routing\{EntPageTitleFormatter, FormatErr};
use LMWF\Http\Routing\Route;

final readonly class PageFactory
{
    public function __construct(
        private EntPageTitleFormatter $formatter,
    ) {
    }

    /**
     * @return null|Page|PageEntTitleErr The page extracted from the route and
     * its definition, null if the definition does not provide any controller,
     * or PageEntTitleErr if there was a problem formatting the title for the
     * page when an entity was requested.
     * @todo Find a way to type that return type depends on routedef's pageTitle
     * type.
     */
    public function getPage(Route $route): null|Page|PageEntTitleErr
    {
        $pageConf = $route->def->pageParam;
        if (null === $pageConf) {
            return null;
        }

        $currentParentRoute = $route;
        $nearestPageAncestor = null;
        while (null !== $currentParentRoute = $currentParentRoute->parent) {
            // If the current parent route has a page, we save it and break.
            if (null !== $nearestPageAncestor = $this->getPage($currentParentRoute)) {
                break;
            }
        }

        if ($nearestPageAncestor instanceof PageEntTitleErr) {
            return $nearestPageAncestor;
        }

        $url = null !== $nearestPageAncestor ? $nearestPageAncestor->url : $pageConf->baseUrl;
        if ('' !== $route->seg || [] !== $route->params) {
            // If this route and its parent share the same definition, i.e. if
            // this route is the same route with only one more parameter.
            if ($route->parent->def === $route->def) {
                $parentLastParam = count($route->parent->params);
                $url .= "/{$route->params[$parentLastParam]}";
            } else {
                $url .= "/{$route->seg}";
                foreach ($route->params as $param) {
                    $url .= "/$param";
                }
            }
        }

        $titleResult = 1 === count($route->params) && null !== $pageConf->entConf ?
            $this->formatter->format($pageConf->entConf, $route->params[0]) :
            $pageConf->title;
        if ($titleResult instanceof FormatErr) {
            return new PageEntTitleErr($titleResult);
        }

        return new Page(
            $nearestPageAncestor,
            $titleResult,
            $url,
            $pageConf->isIndexed,
            $pageConf->isPartOfHierarchy,
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
