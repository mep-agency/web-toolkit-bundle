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

namespace Mep\WebToolkitBundle\Twig;

use Psr\Cache\InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Implements some Twig extensions for generic interaction with the bundle.
 *
 * @author Marco Lipparini <developer@liarco.net>
 * @author Alessandro Foschi <alessandro.foschi5@gmail.com>
 */
class TwigFunctionsExtension extends AbstractExtension
{
    private string $projectDir;

    public function __construct(
        protected AdapterInterface $adapter,
        protected Packages $packages,
        KernelInterface $kernel,
    ) {
        $this->projectDir = $kernel->getProjectDir();
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('svg', function (string $path, ?string $id = null, ?string $class = null): string {
                return $this->getInlineSvg($path, $id, $class);
            }, [
                'is_safe' => ['html'],
            ]),
            new TwigFunction('favicons', function (?string $path = null): ?string {
                return $this->createFaviconTags($path);
            }, [
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function getInlineSvg(string $path, ?string $id = null, ?string $class = null): string
    {
        $filePath = $this->projectDir.'/'.trim($path, '/');
        $file = new File($filePath);
        $mTime = $file->getMTime();

        try {
            $cacheItem = $this->adapter->getItem('mwt_svg_file_'.md5($filePath));
        } catch (InvalidArgumentException) {
            throw new RuntimeException('Unexpected error managing inline SVG cache: bad key argument');
        }

        if ('image/svg+xml' !== $file->getMimeType()) {
            throw new RuntimeException('Given file is not an SVG: '.$filePath);
        }

        // Check cache
        if (! $cacheItem->isHit() || $cacheItem->get()['mtime'] < $mTime) {
            // Cache missed, parse the SVG file
            $rawSvg = file_get_contents($filePath) ?: '';
            $matches = [];

            if (1 !== preg_match('#<svg[\s\S]+</svg>#', $rawSvg, $matches)) {
                throw new RuntimeException("Can't find <svg> tag in file: ".$filePath);
            }

            $cacheItem->set([
                'mtime' => $mTime,
                'data' => $matches[0],
            ]);

            $this->adapter->save($cacheItem);
        }

        $svgCode = $cacheItem->get()['data'];

        // Add HTML id/classes
        if (! empty($class)) {
            $svgCode = str_replace('<svg', '<svg class="'.$class.'"', $svgCode);
        }

        if (! empty($id)) {
            $svgCode = str_replace('<svg', '<svg id="'.$id.'"', $svgCode);
        }

        return $svgCode;
    }

    /**
     * Creates new tags for all favicons found in the given path.
     *
     * Usage: {{ favicons('build/path/to/my/icons') }}
     *
     * @return null|string Favicon tags or "null" if the given path cannot be found
     */
    public function createFaviconTags(string $path = null): ?string
    {
        if (null === $path) {
            $path = 'build/images/icons';
        }

        $tags = '';
        $regex = '/^icon-(\d+)\.(?:png|ico)$/';
        $absolutePath = $this->projectDir.preg_replace('#^build#', '/assets', $path);
        $finder = new Finder();

        if (! is_dir($absolutePath)) {
            return null;
        }

        $icons = $finder->in($absolutePath)
            ->files()
            ->name($regex)
            ->depth('== 0')
        ;

        foreach ($icons as $icon) {
            $file = new File($icon->getPathname());
            $iconUrl = $this->packages->getUrl($path.'/'.$icon->getFilename());
            $matches = [];

            preg_match($regex, $icon->getFilename(), $matches);

            $tags .= '<link rel="shortcut icon" sizes="'.$matches[1].'x'.$matches[1].'" href="'.$iconUrl.'" type="'.$file->getMimeType().'">'."\n";

            if ('180' === $matches[1]) {
                $tags .= '<link rel="apple-touch-icon" sizes="'.$matches[1].'x'.$matches[1].'" href="'.$iconUrl.'" type="'.$file->getMimeType().'">'."\n";
            }
        }

        return $tags;
    }
}