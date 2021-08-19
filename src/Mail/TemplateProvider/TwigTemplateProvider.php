<?php

/*
 * This file is part of the MEP Web Toolkit package.
 *
 * (c) Marco Lipparini <developer@liarco.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Mep\WebToolkitBundle\Mail\TemplateProvider;

use Mep\WebToolkitBundle\Contract\Mail\TemplateIdentifierInterface;
use Mep\WebToolkitBundle\Contract\Mail\TemplateProviderInterface;
use Mep\WebToolkitBundle\Exception\Mail\MissingRequiredTwigTemplateFileException;
use Mep\WebToolkitBundle\Mail\TemplateIdentifier\TwigTemplate;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class TwigTemplateProvider implements TemplateProviderInterface
{
    public const SUBJECT_TEMPLATE_NAME = 'subject.html.twig';

    public const HTML_TEMPLATE_NAME = 'html_body.html.twig';

    public const TEXT_TEMPLATE_NAME = 'text_body.html.twig';

    public function __construct(
        private Environment $twig,
    ) {}

    public function supports(TemplateIdentifierInterface $templateIdentifier): bool
    {
        return $templateIdentifier instanceof TwigTemplate;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function render(TemplateIdentifierInterface $templateIdentifier, array $parameters = []): Email
    {
        /** @var TwigTemplate $templateIdentifier */
        $this->validateTemplateIndentifier($templateIdentifier);

        $email = new Email();
        $subject = $this->templateFileExists($templateIdentifier, self::SUBJECT_TEMPLATE_NAME) ?
            $this->twig->render(
                $this->getTemplatePath($templateIdentifier, self::SUBJECT_TEMPLATE_NAME),
                $parameters
            ) : null;
        $text = $this->templateFileExists($templateIdentifier, self::TEXT_TEMPLATE_NAME) ?
            $this->twig->render(
                $this->getTemplatePath($templateIdentifier, self::TEXT_TEMPLATE_NAME),
                $parameters
            ) : null;
        $html = $this->templateFileExists($templateIdentifier, self::HTML_TEMPLATE_NAME) ?
            $this->twig->render(
                $this->getTemplatePath($templateIdentifier, self::HTML_TEMPLATE_NAME),
                $parameters
            ) : null;

        if ($subject !== null) {
            $email->subject(trim($subject));
        }

        $email->text($text ?? html_entity_decode(strip_tags($html)));

        if ($html !== null) {
            $email->html($html);
        }

        return $email;
    }

    private function validateTemplateIndentifier(TwigTemplate $twigTemplate): bool
    {
        if (
            ! $this->templateFileExists($twigTemplate, TwigTemplateProvider::TEXT_TEMPLATE_NAME)
            && ! $this->templateFileExists($twigTemplate, TwigTemplateProvider::HTML_TEMPLATE_NAME)
        ) {
            throw new MissingRequiredTwigTemplateFileException($twigTemplate);
        }

        return true;
    }

    private function getTemplatePath(TwigTemplate $twigTemplate, string $file): string
    {
        return $twigTemplate->getTemplatesFolder() . '/' . $file;
    }

    private function templateFileExists(TwigTemplate $twigTemplate, string $file): bool
    {
        return $this->twig->getLoader()->exists($this->getTemplatePath($twigTemplate, $file));
    }
}
