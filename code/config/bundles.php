<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Sentry\SentryBundle\SentryBundle::class => ['prod' => true],
    Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
];
