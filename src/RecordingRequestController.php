<?php

declare(strict_types=1);

namespace Medas\TestSupport;

use Medas\HttpClient\{Interfaces\RequestController, Request, Response};

/**
 * Test double for outgoing HTTP. Records every request and returns a canned
 * response (200 by default), so code paths that call out run fully without
 * leaving the process. Bound to the RequestController interface by
 * RecordingHttpPackage.
 *
 * It's a singleton within a run, so reset() between tests to clear recorded
 * requests and restore the default response.
 */
class RecordingRequestController implements RequestController
{
    /** @var Request[] */
    public array $requests = [];

    public int $responseCode = 200;

    // What the canned response carries, for code that reads what it fetched (a
    // file's content, say). Empty by default: a response without a body.
    public string $responseBody = '';

    public function execute(Request $request): Response
    {
        $this->requests[] = $request;

        return new Response(
            code: $this->responseCode,
            rawBody: $this->responseBody,
            body: $this->responseBody === '' ? null : $this->responseBody,
            header: '',
            curlInfo: [],
        );
    }

    public function reset(): void
    {
        $this->requests = [];
        $this->responseCode = 200;
        $this->responseBody = '';
    }
}
