# LMWF repository guide

This document describes the project as it exists in this repository. It is intended as orientation for maintainers, application developers, reviewers, and AI coding tools. The sections on current behavior describe code that is present; the sections on gaps and recommendations are assessments, not promises about planned work.

## Project summary

LMWF is a small PHP web framework distributed as the Composer library **lmwf-team/lmwf**. Its stated goal is to provide reusable web application building blocks without taking control away from the application or requiring a large framework stack. The framework supplies configuration and dependency-injection setup, request routing, controller contracts, session and CSRF helpers, form-data transformers, typed data models and validation, image upload helpers, and utilities for translating database rows into application data.

LMWF is best understood as a **framework toolkit**, not a complete ready-to-run website or a full-stack application. The consuming application supplies its controllers, repository implementations, views or templates, database queries and connection management, authentication policy, and response content. LMWF uses PSR interfaces at key HTTP, container, and logging boundaries so application code can retain control of those choices.

The package requires PHP 8.5 or newer. It is licensed under MIT. Composer maps the LMWF namespace to src/ and the test namespace to tests/. See [composer.json](composer.json) for package metadata and dependencies and [README.md](README.md) for the project’s brief published overview.

## Product scope

### What the repository provides

- A kernel initializer that assembles configuration and a PHP-DI container, registers framework configuration objects, initializes logging, and can install an error-to-exception handler.
- JSON-backed application configuration with a distributed file, an optional local file, and programmatic overrides.
- A route-definition tree, URL path matching, route parameters, wildcard child routes, role metadata, page metadata, and controller dispatch.
- Controller interfaces based on PSR-7 request and response objects.
- Configurable error-controller dispatch for common routing and access outcomes.
- Optional Content Security Policy header generation, including a per-instance nonce placeholder.
- Session-backed CSRF values, a small set of login/session helpers, and flash messages.
- Form configuration and transformers for strings, integers, dates, checkboxes, string-backed enums, composable array forms, CSRF fields, and image uploads.
- Type models and validators for scalar values, enums, arrays/entities, and lists, plus structured validation-violation objects.
- Immutable collection and value types, including application objects/lists, filenames, slugs, pages, and image-format settings.
- Conversion helpers between application values and database-compatible scalars/rows, designed to work with the model types.
- Image upload naming, WebP conversion, thumbnail generation, and a utility for listing uploaded images.
- A small keyword-query parser and weighted substring-ranking helper.

### What it does not currently provide as an integrated feature

The repository does not contain a view or template engine, HTML form renderer, SQL query builder, database connection or transaction manager, complete authentication/login system, general authorization/role service, or production application skeleton. The database module converts data formats; it does not issue queries. The search module parses and scores supplied text; it does not index documents or query a search backend. Applications must assemble these pieces themselves.

## Runtime architecture

The main web request path is implemented by [src/Http/HttpRequestHandler.php](src/Http/HttpRequestHandler.php):

1. **Initialize the application.** Kernel::init() reads and validates configuration, builds the PHP-DI container, registers AppConf and HttpConf, initializes the static logger facade, and optionally installs a PHP error handler. Kernel::initBare() builds only a container from caller-provided definitions.
2. **Create a request.** respondToOngoingRequest() builds a Guzzle PSR-7 server request from PHP globals. The lower-level generateResponse() method can instead be called with an existing PSR-7 request, which is useful for integration and tests.
3. **Match the path.** Router splits the URI path into decoded segments and walks the configured RouteDef tree. Definitions may reserve a number of parameter segments, have named children, or use a * child as a wildcard.
4. **Choose a controller.** A matched route selects a page configuration for its parameter count. The handler resolves the configured controller class from the container and calls its IRoutedController::generateResponse() method.
5. **Apply the current role check.** The handler currently derives one of two role lists from whether SessionManager reports a logged-in user: ADMIN or VISITOR. It compares those strings with the route’s role list.
6. **Map routing outcomes.** Missing routes, invalid route-parameter counts, unsupported methods, and access outcomes become controller-issue codes. The handler resolves the configured error controller and asks it to produce the response.
7. **Add configured CSP policy.** If CSP directives exist, the handler builds a Content-Security-Policy value and replaces {NONCE} entries with the injected nonce.
8. **Emit the response.** sendResponse() writes the status, headers, and body through PHP’s global response functions.

The handler accepts the literal method names GET, HEAD, POST, READ, PUT, PATCH, OPTIONS, and DELETE. In particular, READ is present in the code’s allow-list even though it is not a commonly used standard HTTP method.

Page configuration can describe a static title or a repository-backed entity title. The recursive route tree is keyed by path segment; a child named * is the wildcard. A route’s page configuration list selects a static or entity-backed page configuration for the route with no parameter and for each supported parameter count. Route-level indexed and inHierarchy flags are propagated through the parser. PageFactory assembles parent-page metadata and URLs. EntPageTitleFormatter can fill title placeholders in the form {{ property_name }} from an entity loaded through an IRepo implementation.

## Module map

| Area | Main responsibility | Representative code |
| --- | --- | --- |
| LMWF Kernel | Bootstrap, container setup, logging setup, PHP error handler | [src/Kernel.php](src/Kernel.php) |
| LMWF Conf | Read application settings, parse routes, validate controller/repository class names | [src/Conf/AppConf.php](src/Conf/AppConf.php), [src/Conf/RouteDefParser.php](src/Conf/RouteDefParser.php) |
| LMWF Http | Request handling, routing, controller contracts, page configuration, CSP nonce | [src/Http/HttpRequestHandler.php](src/Http/HttpRequestHandler.php), [src/Http/Routing/Router.php](src/Http/Routing/Router.php) |
| LMWF Session | PHP session access, CSRF token, username marker, custom string values, flash messages | [src/Session/SessionManager.php](src/Session/SessionManager.php) |
| LMWF Form | Build form field metadata and convert submitted body/upload data | [src/Form/FormFactory.php](src/Form/FormFactory.php), [src/Form/Transformer](src/Form/Transformer) |
| LMWF Constraint | Type models and value constraints used to describe accepted data | [src/Constraint](src/Constraint) |
| LMWF Validation | Validate values against models and return structured violations | [src/Validation/ValidatorFactory.php](src/Validation/ValidatorFactory.php) |
| LMWF Database | Convert database scalar/row data to and from application data | [src/Database/DbEntityManager.php](src/Database/DbEntityManager.php) |
| LMWF Repo | Repository interface for finding one entity | [src/Repo/IRepo.php](src/Repo/IRepo.php) |
| LMWF File | Image filename allocation, upload listing, thumbnail creation | [src/File/FileService.php](src/File/FileService.php), [src/File/ThumbnailWriter.php](src/File/ThumbnailWriter.php) |
| LMWF DataStructures | Immutable collections and common value objects | [src/DataStructures](src/DataStructures) |
| LMWF SearchEngine | Parse a small query syntax and score string fields | [src/SearchEngine](src/SearchEngine) |
| LMWF ErrorHandling | Logger facade, exception codes, unexpected-value exception types | [src/ErrorHandling](src/ErrorHandling) |

The architectural dependency intent is partly recorded in [deptrac.yaml](deptrac.yaml). That file should be read as an incomplete boundary map, not proof that every module boundary is enforced.

## Configuration

AppConf::createFromEnvFile() reads a required distributed lmwf_app.json from the supplied folder and optionally reads .lmwf_app.local.json. It also accepts programmatic configuration values. The merge is a shallow PHP array union, so earlier values win and a value for a key replaces the whole value at that key; nested objects are not recursively merged.

The effective precedence is:

1. Values explicitly passed to createFromEnvFile().
2. Values from .lmwf_app.local.json for keys not supplied by the caller.
3. Values from lmwf_app.json for keys still missing.
4. The supplied configuration-folder path for confFolderPath, if it is not already set.

Kernel::init() can also be used without a folder by passing configuration data directly. AppConf converts raw nested arrays to the framework’s collection types and exposes validated properties such as isDev, handleExceptions, baseUrl, language, appRootPath, uploadRelPath, publicRelPath, thumbnailFormats, and the parsed HTTP configuration.

Configuration includes a route tree, CSP directives, error-controller class names, and thumbnail formats. Route definitions can name routed controllers, optional repositories for entity pages, page titles, role lists, child routes, and page metadata flags. Route/controller and repository class names are checked against their expected interfaces when parsed. Dots in configured class names are converted to namespace separators where the parser supports that convention.

There is no complete production configuration schema or example file at the repository root. Tests contain fixtures and mocks that show pieces of the expected structure, but applications should treat the constructor reads in AppConf and parser constants in RouteDefParser as the authoritative current contract.

## Main extension contracts

### Controllers and repositories

- A general error controller implements IController and returns a PSR-7 ResponseInterface from generateResponse(request, serverParams).
- A routed page controller implements IRoutedController and returns either a PSR-7 response or a ControllerIssue.
- Controllers are resolved from the dependency-injection container by configured class name.
- A repository implements IRepo::find(string $id, ?EntityModel $overrideModel = null): ?AppObject. The interface currently defines only single-entity lookup.

### Data models, validation, and forms

Constraint models describe nullability, scalar types, allowed values, ranges, regular expressions, entity fields, and list item models. ValidatorFactory selects the matching validator. Validation reports null for success or a typed violation object for a failure; callers should not assume that a violation is already suitable for display to an end user.

Forms use both a model and explicit presentation configuration. FormConfFactory requires model properties to be configured or explicitly ignored and can infer common field types from scalar models. FormFactory creates transformers for submitted data; it does not render HTML. Transformers convert PHP-parsed request data into application values, while model validation remains a separate step.

The default form transformer includes CSRF validation. Empty strings from text-style fields become null; integer strings are cast to integers; dates become DateTimeImmutable; enum values that do not match a case are represented by NonExistingEnumCase; checkboxes map the submitted string on to true. Image fields use Guzzle’s uploaded-file interface and GD to decode and re-encode content as WebP.

### Persistence and files

DbEntityManager performs data conversion, not persistence. It flattens nested entity values into prefixed columns, handles scalar values such as booleans, dates, and backed enums, and reconstructs model-shaped application objects from joined result rows. Application-specific repositories remain responsible for SQL, query execution, transactions, and database error policy.

Image uploads are written below the configured upload path as WebP and may receive thumbnails based on configured dimensions and qualities. The implementation uses PHP GD functions. The filesystem directories, web exposure rules, retention/deletion policy, upload authorization, and size limits must be addressed by the consuming application and deployment.

## Engineering philosophy visible in the code

The project’s stated philosophy is lightweight composition and application control. Several implementation choices support that:

- **Small dependency surface.** Runtime dependencies are PHP-DI, Guzzle, portable ASCII helpers, and PSR logging; framework code relies on PSR container and HTTP message interfaces at integration boundaries.
- **Explicit modules.** The src/ namespace tree separates configuration, HTTP, constraints, data structures, forms, validation, persistence conversion, file handling, sessions, search, and error handling.
- **Typed contracts.** Code uses native parameter and return types, PHP enums, interface contracts, PHPDoc generics/shapes, and union return types to describe behavior.
- **Strict PHP mode.** All 135 PHP source files under src/ currently declare strict_types=1.
- **Value-oriented APIs.** AppObject, AppList, ImmutableArray, Page, Filename, route definitions, page configuration, and many violation/configuration classes are readonly or otherwise designed to return new values instead of mutating in place.
- **Structured validation and errors.** Validation produces violation objects, routing uses issue values for expected routing failures, and many exceptions use named codes from ExceptionCode.
- **Automated code checks.** The repository configures PSR-12 checking, PHPStan level 10 with strict-rule extensions, and PHPUnit with strict output/warning/risky-test settings.

These are patterns to preserve and strengthen. They are not uniformly enforced in every class. In particular, the repository’s README describes an exception policy that is explicitly marked work in progress, and mutability varies substantially by module.

## Rules and conventions for changes

### PHP and source layout

- Put library implementation under src/; mirror the namespace below LMWF to its directory path.
- Use Composer’s PSR-4 autoloading. Keep one primary class, interface, or enum per PHP file.
- Start PHP files with declare(strict_types=1);.
- Prefer native types for every parameter, property, and return value. Use PHPDoc for shapes, templates, class-string relationships, and refinements PHP cannot express natively.
- Validate configuration and external input at the boundary, then pass narrow typed values into the core.
- Use final classes by default. Use interfaces where an application extension point or replaceable boundary is intentional.
- Prefer immutable value objects and readonly classes for data without a lifecycle. Avoid publicly mutable properties and avoid letting a constructed model silently change after it has been shared.
- Keep side effects at clear boundaries: globals/session access, logging, file I/O, response emission, and container setup. Keep transformations and model validation deterministic where practical.
- Use enums or typed issue/result values when a finite set of outcomes is expected. Keep exception codes unique and distinguish invalid program state from ordinary user-submission failures.
- Preserve PSR-7 request/response contracts and PSR container/logger integration points.

### Tests and static checks

The intended local aggregate check is:

~~~sh
composer install
./dev/all.sh
~~~

dev/all.sh runs the PSR-12 check, PHPUnit, then PHPStan. Individual entry points are ./dev/psr12.sh, ./dev/test.sh, and ./dev/stan.sh. ./dev/fixstyle.sh runs PHP Code Beautifier and Fixer. Docker wrappers live under docker/; the dev container is described by .devcontainer/devcontainer.json and compose.yaml.

PHPStan is configured at level 10 in dev/phpstan/phpstan.neon, with strict-rules and disallowed-call extensions. Some strict-rule options are disabled explicitly and a few test-path ignores exist, so the configuration should be treated as strong static analysis rather than a proof of correctness.

Tests live under tests/ and are discovered by [phpunit.xml](phpunit.xml). Existing focused test areas include configuration, constraints/models, data structures, database conversion, file listing, forms, HTTP/routing, and validators. PHPUnit is configured to fail on warnings, risky tests, output, and PHPUnit deprecations.

GitHub Actions currently runs style, PHPStan, and PHPUnit workflows on pushes to master; these workflows do not declare a pull_request trigger. The documentation workflow runs after PHPUnit and also supports manual dispatch. Its Deptrac step is marked continue-on-error, so architecture analysis is not a required CI gate.

## Incomplete areas and quality findings

This section records opportunities grounded in the current source and tooling. They should be assessed against intended product scope before being treated as bugs.

### 1. Immutability is a strong pattern but not a repository-wide invariant

Many configuration and value types are readonly, and the immutable collection API is a useful foundation. Other important types are mutable:

- Constraint models such as AbstractModel, ArrayModel, EntityModel, StringModel, and ListModel store mutable object state. ArrayModel exposes a protected properties array, and EntityModel::addItselfAsProperty() mutates its newly created model’s property array after construction.
- Several constraint implementations and violation structures are not readonly.
- SessionManager, Log, Kernel, file services, and the response emitter necessarily interact with mutable state or external systems.
- ImmutableArray is shallowly immutable: its internal array cannot be changed through the wrapper, but arbitrary mutable objects can be stored as values.

For future core code, prefer fully initialized readonly value objects. For model composition, keep a builder or named factory mutable only during assembly, then freeze the resulting model graph. Do not advertise deep immutability unless contained values are immutable too. Keep session, filesystem, logger, and HTTP emission state in narrow adapters rather than forcing those objects into readonly form.

### 2. Exception and expected-error policy needs a single contract

The README says exceptions generally indicate defects and are not meant to be handled, but the form layer defines exceptions for missing fields, illegal user input, and CSRF mismatch. Routing also has issue/result objects, while configuration and conversion mostly throw exceptions. handleExceptions is loaded into AppConf and HttpConf, but a search of the current source shows the request handler does not consult it.

Document which failures are expected from user input, which should become HTTP responses, and which represent programmer or infrastructure errors. Prefer typed results/violations for routine validation failures, or explicitly document recoverable exception types and their handling layer. Either implement the configured exception-handling behavior or remove/rename the setting until it has behavior. The README also says each thrown exception has a unique code listed in ExceptionCode, while multiple current throw sites use built-in exceptions without such a code; align that claim with the actual policy.

### 3. Some model validation guarantees are weaker than their comments imply

- ArrayValidator checks every declared property and reports missing declared properties, but it does not reject keys present in the input that are absent from the model. Its class comment describes a stricter key set than the implementation enforces.
- ListModel can carry a range constraint, but ListValidator validates list shape and each item without checking that range.
- StringValidator checks string length, regex, and enum constraints. The image constraint currently contributes filename-oriented metadata; it is not itself an image-content, size, or dimension validator.
- IntTransformer casts a non-empty string directly to int; it does not establish that the original text was a valid integer representation. Model validation can catch the resulting value’s type/range, but cannot recover malformed text that already cast to zero.

Clarify whether models represent closed records or open dictionaries, make validators match that contract, and test unknown keys, absent-vs-null properties, range boundaries, malformed numeric text, and upload constraints.

### 4. HTTP and authorization behavior is intentionally minimal and needs explicit limits

The handler’s route authorization currently maps a logged-in marker to ADMIN and an absent marker to VISITOR; the source itself includes a TODO for a real role system. This is a routing gate, not a general authentication or authorization implementation. The library has no password/login flow or policy engine. PHP session cookie settings, session ID rotation, identity lifecycle, and permission assignment are application/deployment responsibilities.

sendResponse() emits a body for every method, including HEAD; it does not itself enforce HEAD’s body semantics. The supported method list includes READ. Path segments are decoded with urldecode(), which treats plus signs as spaces; confirm that behavior is intended for URL path segments. Add method-specific and encoding tests before treating these details as a stable HTTP contract.

Only the configured CSP header is added by this handler. Other response security headers and production error-display settings are not centrally enforced. The README explicitly warns applications to disable PHP error display.

### 5. Upload handling needs stronger operational and security contracts

The image transformer decodes uploaded bytes and writes WebP files, but imagewebp() results are not checked. Thumbnail directory creation, upload size/dimension limits, cleanup on partial failure, collision handling under concurrency, ownership checks for previously submitted filenames, and lifecycle/deletion policy are not handled as a complete workflow here. A hidden previous-filename field is syntax-checked as a Filename, but the transformer does not prove that the file belongs to the current user or record.

Before exposing uploads in an application, enforce authorization and resource limits at the application boundary, use non-guessable or record-bound references where appropriate, ensure target directories are provisioned safely, check GD operation results, and define cleanup and retention behavior. Treat stored filenames as identifiers, not proof of access rights.

### 6. Runtime platform requirements are under-declared

The code calls GD functions for image processing and uses multibyte string functions. composer.json does not declare corresponding extension requirements. The supplied docker/lmwf/Dockerfile installs ZIP and Xdebug but does not install GD; it also does not document enabling mbstring. As a result, a Composer-valid installation or the included Docker environment is not necessarily a complete runtime for every feature.

Declare required PHP extensions in Composer where appropriate and make the Docker/development/CI environments install the extensions used by the features they exercise. If an extension is optional, detect its absence and fail with a focused, actionable error.

### 7. Architecture and documentation enforcement are incomplete

The module tree and deptrac.yaml show intent to constrain dependencies, but Repo has no layer, the File collector uses File/ rather than the ./src/... form used by most other layers, the ruleset has no explicit policy entry for every declared layer, and the Deptrac job is allowed to fail. Add all owned namespaces to the layer map, check collector paths, define rules in both directions where useful, and make the architecture check a required CI job once the current graph is clean.

The README is very short, and its Style section ends with an unfinished sentence. There is no complete configuration schema, controller example, form lifecycle guide, deployment checklist, or clear statement of the extension boundaries above. Generated PHPDoc is useful as an API reference but does not replace these integration instructions.

### 8. Automated verification does not yet cover every exposed area

The test tree has good focused coverage in several important modules, especially routing, data conversion, collections, and validators. It has no dedicated SearchEngine or Session test directory, and no dedicated ImgFileTransformer or ThumbnailWriter test file is present. The upload, CSRF, authorization, and error-controller boundaries deserve direct tests because they combine external input and side effects. Search queries also need an empty-query test: rankResult() divides by total query length, which is zero for a query with no keywords.

CI checks run on pushes to master, not pull requests. Add PR triggers and focused test coverage for the behaviors above as the maintainers choose to make them contractual. This guide was prepared by source/configuration inspection; it does not report test or static-analysis results.

### 9. Some abstractions and naming are still evolving

Source comments contain many TODOs around route parameter types, error codes, parser tests, model naming, form configuration, repository methods, exception handling, and image behavior. DbEntityManager is a collection of conversion operations rather than an entity manager in the database lifecycle sense. SearchEngine is a ranking helper rather than a complete search service. Names and documentation should continue to describe actual responsibilities rather than imply unimplemented behavior.

## Suggested direction for high-quality changes

When adding or changing functionality, use this order of preference:

1. Define the contract, including invalid-input behavior and ownership of side effects.
2. Represent stable data as validated readonly objects and keep mutability at explicit construction or I/O boundaries.
3. Narrow untrusted mixed data immediately; do not let raw request/config/database values flow deep into domain code.
4. Separate expected user-input failures from programmer/infrastructure failures.
5. Keep validation independent from form parsing and persistence conversion.
6. Preserve framework/application boundaries: LMWF offers contracts and adapters; applications own policy, rendering, queries, and deployment choices.
7. Add focused PHPUnit coverage and ensure PSR-12/PHPStan checks pass. For behavior that crosses a boundary, test both success and failure paths.
8. Update this guide or the README when a public extension contract, configuration key, or deployment requirement changes.

## Repository landmarks

- [README.md](README.md): package pitch, generated documentation link, and minimal security/style notes.
- [composer.json](composer.json): PHP requirement, runtime/dev dependencies, namespace mappings, and package metadata.
- [src/](src): library implementation.
- [tests/](tests): PHPUnit tests, fixtures, and mocks.
- [dev/](dev): local quality-check scripts and PHPStan/PHPDocumentor configuration.
- [docker/](docker) and [compose.yaml](compose.yaml): container-based development wrappers.
- [.github/workflows/](.github/workflows): CI and documentation deployment workflows.
- [deptrac.yaml](deptrac.yaml): intended module dependency graph.
- [phpunit.xml](phpunit.xml): PHPUnit suite and strictness settings.
