<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Exécute les tests unitaires au premier démarrage de l'application (env dev uniquement).
 * Les résultats s'affichent dans la console du serveur Symfony.
 */
class TestRunnerSubscriber implements EventSubscriberInterface
{
    private static bool $alreadyRun = false;

    public function __construct(private KernelInterface $kernel)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 0],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (self::$alreadyRun) {
            return;
        }

        if ($this->kernel->getEnvironment() !== 'dev') {
            return;
        }

        self::$alreadyRun = true;

        $projectDir = $this->kernel->getProjectDir();
        $php        = PHP_BINARY;
        $phpunit    = $projectDir . '/vendor/bin/phpunit';

        $output = [];
        exec(sprintf('%s %s --testdox --colors=never 2>&1', escapeshellarg($php), escapeshellarg($phpunit)), $output);

        $separator = str_repeat('─', 60);
        fwrite(STDERR, "\n\033[1;36m$separator\033[0m\n");
        fwrite(STDERR, "\033[1;36m  Tests unitaires — lancement automatique\033[0m\n");
        fwrite(STDERR, "\033[1;36m$separator\033[0m\n");

        foreach ($output as $line) {
            if (str_contains($line, '✔') || str_contains($line, 'OK')) {
                fwrite(STDERR, "\033[32m$line\033[0m\n");
            } elseif (str_contains($line, '✘') || str_contains($line, 'FAIL') || str_contains($line, 'ERROR')) {
                fwrite(STDERR, "\033[31m$line\033[0m\n");
            } else {
                fwrite(STDERR, "$line\n");
            }
        }

        fwrite(STDERR, "\033[1;36m$separator\033[0m\n\n");
    }
}
