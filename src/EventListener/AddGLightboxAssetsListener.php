<?php

declare(strict_types=1);

/*
 * This file is part of the Contao GLightbox extension.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoGLightbox\EventListener;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FrontendTemplate;
use Contao\LayoutModel;
use Contao\StringUtil;

/**
 * Outputs the `js_glightbox` template for MODERN Twig page layouts.
 *
 * Modern layouts do not process the layout's JavaScript templates (the `scripts`
 * field), so the classic activation ("enable the js_glightbox JavaScript
 * template in your page layout") has no effect there. This listener renders the
 * very same template when it is enabled in the layout, so the behaviour – and
 * any custom js_glightbox template the user created – is identical to the classic
 * layout. The classic `fe_page` layout keeps rendering the template itself, and a
 * page is either classic or modern, so the template is never output twice.
 *
 * Registered via services.yaml with the event name as a string and an `object`
 * parameter, so Contao\CoreBundle\Event\LayoutEvent is never resolved on Contao
 * versions that do not provide it.
 */
class AddGLightboxAssetsListener
{
    public function __construct(private readonly ContaoFramework $framework)
    {
    }

    public function onLayout(object $event): void
    {
        if (!method_exists($event, 'getLayout')) {
            return;
        }

        $layout = $event->getLayout();

        if (!$layout instanceof LayoutModel) {
            return;
        }

        $scripts = StringUtil::deserialize($layout->scripts, true);

        if (!\in_array('js_glightbox', $scripts, true)) {
            return;
        }

        $this->framework->initialize();

        /** @var FrontendTemplate $template */
        $template = $this->framework->createInstance(FrontendTemplate::class, ['js_glightbox']);

        // parse() also registers the stylesheet via $GLOBALS['TL_CSS'] (set inside the template).
        $GLOBALS['TL_BODY']['js_glightbox'] = $template->parse();
    }
}
