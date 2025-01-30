<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Responder;

interface TemplateResponderInterface extends ResponderInterface
{
    public function template(): string;
}
