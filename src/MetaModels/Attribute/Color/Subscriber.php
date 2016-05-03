<?php

/**
 * This file is part of MetaModels/attribute_color.
 *
 * (c) 2012-2016 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage AttributeColor
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2016 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_color/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Attribute\Color;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ManipulateWidgetEvent;
use MetaModels\DcGeneral\Data\Model;
use MetaModels\DcGeneral\Events\BaseSubscriber;

/**
 * Handle events regarding the color attributes.
 */
class Subscriber extends BaseSubscriber
{
    /**
     * {@inheritDoc}
     */
    public function registerEventsInDispatcher()
    {
        $this->addListener(
            ManipulateWidgetEvent::NAME,
            array($this, 'addColorPicker')
        );
    }

    /**
     * Append the date picker to the widget.
     *
     * @param ManipulateWidgetEvent $event The event.
     *
     * @return void
     */
    public static function addColorPicker(ManipulateWidgetEvent $event)
    {
        $model = $event->getModel();
        if (!$model instanceof Model) {
            return;
        }
        /** @var Model $model */
        $metaModel = $model->getItem()->getMetaModel();
        $property  = $event->getProperty()->getName();

        if (!$metaModel->getAttribute($property) instanceof Color) {
            return;
        }

        $environment = $event->getEnvironment();
        $widget      = $event->getWidget();

        $imageEvent = new GenerateHtmlEvent(
            'pickcolor.gif',
            $environment->getTranslator('MSC.colorpicker'),
            'style="vertical-align:top;cursor:pointer" id="moo_' . $property . '"'
        );

        $environment->getEventDispatcher()->dispatch(ContaoEvents::IMAGE_GET_HTML, $imageEvent);

        /** @noinspection PhpUndefinedFieldInspection */
        $widget->wizard = $imageEvent->getHtml() . '
            <script>
            new MooRainbow("moo_' . $property . '", {
            id:"ctrl_' . $property . '_0",
            startColor:((cl = $("ctrl_' . $property . '_0").value.hexToRgb(true)) ? cl : [255, 0, 0]),
            imgPath:"plugins/colorpicker/images/",
            onComplete: function(color) {
                $("ctrl_' . $property . '_0").value = color.hex.replace("#", "");
            }
            });
            </script>';
    }
}
