<?php

declare(strict_types=1);

namespace App\Healthcheck\Presentation\Web;

use App\CoreDomain\Resources\Attribute\WebRoute;
use App\Healthcheck\Presentation\Web\Request\TestActionRequest;
use App\Healthcheck\Presentation\Web\Response\TestActionHtmlResponse;

#[WebRoute('/test', ['GET'], 'web.test')]
final readonly class TestAction
{
    public function __invoke(
        TestActionRequest $request,
        TestActionHtmlResponse $response,
    ): TestActionHtmlResponse {
        return $response->context(['status' => $request->message])->respond();
    }
}
