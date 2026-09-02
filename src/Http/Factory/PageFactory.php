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
        $nParams = $route->getNParams();

        $pageConf = 0 === $nParams ? $route->def->noParamConf : $route->def->params[$nParams - 1];

        if (null === $pageConf) {
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

        $mutUrl = $pageConf->getBaseUrl() . $route->getPath();
        // $mutUrl = null !== $nearestPageAncestor ? $nearestPageAncestor->url : $pageConf->getBaseUrl();
        // if (null !== $route->parent && $route->parent->def->nParamsMax > count($route->parent->params)) {
        //     // The parent route is the same route definition with one more parameter.
        //     $mutUrl .= '/' . $route->params[count($route->params) - 1];
        // } elseif (null !== $route->parent) {
        //     $mutUrl .= "/{$route->seg}";
        //     if ([] !== $route->params) {
        //         $mutUrl .= '/' . implode('/', $route->params);
        //     }
        // }

        if ($pageConf instanceof EntPageConf) {
            if (0 === $nParams) {
                throw new UnexpectedValueException(
                    'The "by-param" configuration for a route with no parameters cannot be an EntPageConf as it would expect a parameter.',
                    ExceptionCode::HTTP_FACTORY_PAGEFACTORY_ENT_PAGE_METADATA_CONF_WITH_0_PARAM->value,
                );
            }
            $titleResult = $this->formatter->format($pageConf, $route->params[$nParams - 1]);
            if ($titleResult instanceof FormatErr) {
                return new PageEntTitleErr($titleResult);
            }
        } else {
            $titleResult = $pageConf->getTitle();
        }


        return new Page(
            $nearestPageAncestor,
            $pageConf->getControllerFqcn(),
            $titleResult,
            $mutUrl,
            $pageConf->isIndexed(),
            $pageConf->isInHierarchy(),
        );
    }

    public function fromStaticPageConf(StaticPageConf $conf, string $path, ?Page $parent): Page
    {
        return new Page(
            $parent,
            $conf->getControllerFqcn(),
            $conf->getTitle(),
            $conf->getBaseUrl() . $path,
            $conf->isIndexed(),
            $conf->isInHierarchy(),
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
