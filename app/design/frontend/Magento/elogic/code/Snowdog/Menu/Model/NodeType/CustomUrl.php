<?php
/**
 * Snowdog
 *
 * @author      Paweł Pisarek <pawel.pisarek@snow.dog>.
 * @category
 * @package
 * @copyright   Copyright Snowdog (http://snow.dog)
 */

namespace Snowdog\Menu\Model\NodeType;

use Magento\Framework\Profiler;
use Snowdog\Menu\Model\Menu\Node\Image\File as NodeImage;
use Snowdog\Menu\Model\TemplateResolver;

class CustomUrl extends AbstractNode
{
    /**
     * @var TemplateResolver
     */
    private $templateResolver;

    public function __construct(
        Profiler $profiler,
        NodeImage $nodeImage,
        TemplateResolver $templateResolver
    ) {
        $this->templateResolver = $templateResolver;
        parent::__construct($profiler, $nodeImage);
    }

    /**
     * @inheritDoc
     */
    public function fetchConfigData()
    {
        $this->profiler->start(__METHOD__);

        $data = [
            'snowMenuNodeCustomTemplates' => [
                'defaultTemplate' => 'custom_url',
                'options' => $this->templateResolver->getCustomTemplateOptions('custom_url'),
                'message' => __('Template not found'),
            ],
            'snowMenuSubmenuCustomTemplates' => [
                'defaultTemplate' => 'sub_menu',
                'options' => $this->templateResolver->getCustomTemplateOptions('sub_menu'),
                'message' => __('Template not found'),
            ],
        ];

        $this->profiler->stop(__METHOD__);

        return $data;
    }
}
