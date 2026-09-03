<?php

declare(strict_types=1);

namespace Medas\TestSupport;

use Medas\EntityManager\Repository;
use Medas\HttpClient\Interfaces\RequestController;
use Medas\HttpRequestFaker\{CapturedResponse, RequestFaker};
use Medas\HttpRequestHandler\Request\Method;
use PHPUnit\Framework\TestCase;

/**
 * Base class for functional tests that drive the application through faked HTTP
 * requests. Provides the "build a request, optionally as a user, dispatch and
 * capture" ergonomics on top of RequestFaker, plus a per-test clean cookie jar.
 *
 * App-specific fixtures (loading seeded accounts, resolving tenants, etc.) belong
 * in a subclass, not here.
 */
abstract class FunctionalTestCase extends TestCase
{
    private RequestFaker $requestFaker;
    private Repository $repository;

    protected function requestFaker(): RequestFaker
    {
        return $this->requestFaker ??= service(RequestFaker::class);
    }

    protected function repository(): Repository
    {
        return $this->repository ??= service(Repository::class);
    }

    /**
     * The recording stub bound to the outgoing-HTTP RequestController - assert on
     * its ->requests, set its ->responseCode, or reset() it between tests. Requires
     * RecordingHttpPackage to be registered, otherwise the real client resolves.
     */
    protected function recordedHttp(): RecordingRequestController
    {
        /** @var RecordingRequestController $controller */
        $controller = service(RequestController::class);

        return $controller;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Each test starts with a clean cookie jar, so nothing leaks between flows.
        $this->requestFaker()->clearJar();
    }

    /**
     * Real requests carry an Authorization header, and some controllers read it
     * via #[HeaderValue('Authorization')] and can't even be constructed without
     * it. The value isn't validated here - authentication is by the user passed to
     * requestAs - so any placeholder works. Override to change it, or return null
     * to send no header.
     */
    protected function authorizationHeader(): string|null
    {
        return 'Bearer functional-test-token';
    }

    /**
     * Builds and dispatches a request as the given user - whatever the app's
     * authentication->user accepts. The faker documents this seam: set the user
     * directly rather than parsing a token.
     *
     * @param array<string, mixed> $bodyData
     */
    protected function requestAs(
        object $user,
        Method $method,
        string $uri,
        array  $bodyData = [],
    ): CapturedResponse
    {
        $request = $this->requestFaker()->buildRequest(
            $method,
            $uri,
            headers: $this->authorizationHeaders(),
            bodyData: $bodyData,
        );

        $request->authentication->user = $user;

        return $this->requestFaker()->captureDispatch($request);
    }

    /** @return array<string, string> */
    private function authorizationHeaders(): array
    {
        $header = $this->authorizationHeader();

        return $header === null ? [] : ['Authorization' => $header];
    }

    /**
     * Like requestAs, but with uploaded files alongside the body - for multipart
     * upload endpoints. Each file is a $_FILES-style array (name, type, tmp_name,
     * error, size), keyed by its form field name.
     *
     * @param array<string, mixed>                $bodyData
     * @param array<string, array<string, mixed>> $fileData
     */
    protected function requestAsWithFiles(
        object $user,
        Method $method,
        string $uri,
        array  $bodyData = [],
        array  $fileData = [],
    ): CapturedResponse
    {
        $request = $this->requestFaker()->buildRequest(
            $method,
            $uri,
            headers: $this->authorizationHeaders(),
            bodyData: $bodyData,
            fileData: $fileData,
        );

        $request->authentication->user = $user;

        return $this->requestFaker()->captureDispatch($request);
    }

    /**
     * Builds and dispatches a request with no authenticated user, so authorisation
     * sees no user - for asserting that access is denied.
     *
     * @param array<string, mixed> $bodyData
     */
    protected function requestAnonymously(
        Method $method,
        string $uri,
        array  $bodyData = [],
    ): CapturedResponse
    {
        $request = $this->requestFaker()->buildRequest(
            $method,
            $uri,
            headers: $this->authorizationHeaders(),
            bodyData: $bodyData,
        );

        return $this->requestFaker()->captureDispatch($request);
    }
}
