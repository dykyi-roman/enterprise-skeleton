<?php

declare(strict_types=1);

namespace Healthcheck\Presentation\Web;

use Healthcheck\Presentation\Web\Request\TestRequest;
use Healthcheck\Presentation\Web\Resonse\TestHtmlResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;

final class TestAction
{
    #[Route('/test', name: 'web_test', methods: ['GET'])]
    public function __invoke(
        #[MapQueryString] TestRequest $request,
        TestHtmlResponse $response,
    ): TestHtmlResponse {
        return $response->context(['lang' => $request->lang])->respond();
    }
}
